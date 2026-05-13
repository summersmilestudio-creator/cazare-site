<?php
require_once __DIR__ . '/includes/bot-blocker.php';
// Pagina de confirmare rezervare
// ACTUALIZAT: 12.01.2026 - Foloseste DOAR PHP pentru WhatsApp (fara n8n)
if (!defined('SUMMER_SMILE')) define('SUMMER_SMILE', true);
require_once __DIR__ . '/includes/stripe-config.php';

require_once 'whatsapp-notify.php';
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/includes/EmailHelper.php';
require_once __DIR__ . '/fb-pixel.php';
require_once __DIR__ . '/google-ads.php';
initStripe();

$session_id = $_GET['session_id'] ?? null;
$error = null;
$booking = null;
$checkinLink = '';

if ($session_id) {
    if (!checkPaymentRateLimit('confirm')) {
        $error = 'Prea multe incercari.';
    } else {
    try {
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        if ($session->payment_status === 'paid') {
            $payment_intent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
            $metadata = $session->metadata;
            $cancel_token = bin2hex(random_bytes(32));

            // Ia datele clientului din Stripe (nu din metadata - Stripe le colecteaza la checkout)
            $customerDetails = $session->customer_details ?? null;
            $customerName = $customerDetails->name ?? ($metadata->name ?? 'Guest');
            $customerEmail = $session->customer_email ?? ($customerDetails->email ?? '');
            $customerPhone = $customerDetails->phone ?? ($metadata->phone ?? '');

            $booking = [
                'id' => uniqid('BK'),
                'session_id' => $session_id,
                'customer_id' => $session->customer,
                'payment_method_id' => $payment_intent->payment_method,
                'name' => $customerName,
                'phone' => $customerPhone,
                'email' => $customerEmail,
                'checkin' => $metadata->checkin,
                'checkout' => $metadata->checkout,
                'nights' => $metadata->nights,
                'guests' => $metadata->guests,
                'property' => $metadata->property ?? 'Studio New York Oradea',
                'total' => $metadata->total,
                'avans_paid' => $metadata->avans,
                'rest_to_charge' => $metadata->rest_plata,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'confirmed',
                'cancel_token' => $cancel_token
            ];

            $bookings_file = __DIR__ . '/data/bookings.json';
            if (!file_exists(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
            $bookings = file_exists($bookings_file) ? json_decode(file_get_contents($bookings_file), true) ?? [] : [];

            $exists = false;
            foreach ($bookings as $b) {
                if ($b['session_id'] === $session_id) { $exists = true; $booking = $b; break; }
            }

            if (!$exists) {
                $bookings[] = $booking;
                file_put_contents($bookings_file, json_encode($bookings, JSON_PRETTY_PRINT));
                // Notifica admin via Brevo
                try {
                    EmailHelper::send('lucyanapetrey@gmail.com', 'Rezervare noua - ' . $booking['property'],
                        "<h2>Rezervare noua!</h2>
                        <p><strong>{$booking['name']}</strong></p>
                        <p>{$booking['property']}</p>
                        <p>Check-in: {$booking['checkin']} | Check-out: {$booking['checkout']}</p>
                        <p>Total: {$booking['total']} RON (avans: {$booking['avans_paid']} RON)</p>
                        <p>Tel: {$booking['phone']} | Email: {$booking['email']}</p>");
                } catch (Exception $e) {
                    // Email non-critical
                }

                // Create CM reservation + guest check-in link
                try {
                    $db = getDB();
                    $propertyKey = $metadata->property_key ?? 'new-york';
                    $slugMap = [
                        'new-york' => 'studio-new-york',
                        'casa-odessei' => 'casa-odessei',
                        'summer-smile' => 'summer-smile',
                        'studio-onix' => 'studio-onix',
                        'vitamin-sea' => 'vitamin-sea',
                        'encanto' => 'encanto',
                        'summer-smile-2' => 'summer-smile-2',
                        'sunrise' => 'sun-rise',
                        'dd-apartment-meraki' => 'dd-apartment-at-meraki-resort-spa',
                        'aer-by-meraky' => 'aer-by-meraki-summer-smile'
                    ];
                    $cmSlug = $slugMap[$propertyKey] ?? $propertyKey;

                    $stmt = $db->prepare("SELECT * FROM cm_properties WHERE slug = ? AND is_active = 1");
                    $stmt->execute([$cmSlug]);
                    $cmProp = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($cmProp) {
                        $icalUid = 'site-' . $booking['id'] . '@summer-smile.ro';

                        // Check if already exists in CM
                        $chk = $db->prepare("SELECT id FROM cm_reservations WHERE ical_uid = ?");
                        $chk->execute([$icalUid]);

                        if (!$chk->fetch()) {
                            $ins = $db->prepare("INSERT INTO cm_reservations (property_id, source, guest_name, guest_email, guest_phone, guests_count, check_in, check_out, total_price, status, ical_uid) VALUES (?, 'direct', ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)");
                            $ins->execute([
                                $cmProp['id'],
                                $booking['name'],
                                $booking['email'],
                                $booking['phone'],
                                intval($booking['guests']),
                                $booking['checkin'],
                                $booking['checkout'],
                                floatval($booking['total']),
                                $icalUid
                            ]);
                            $resId = $db->lastInsertId();

                            $guestToken = bin2hex(random_bytes(16));
                            $ins2 = $db->prepare("INSERT INTO cm_guest_tokens (reservation_id, token, property_id, guest_name, check_in, check_out, access_code, wifi_name, wifi_password, parking_info, house_rules, property_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $ins2->execute([
                                $resId,
                                $guestToken,
                                $cmProp['id'],
                                $booking['name'],
                                $booking['checkin'],
                                $booking['checkout'],
                                $cmProp['default_access_code'],
                                $cmProp['default_wifi_name'],
                                $cmProp['default_wifi_password'],
                                $cmProp['default_parking_info'],
                                $cmProp['default_house_rules'],
                                $cmProp['address']
                            ]);
                            $checkinLink = 'https://summer-smile.ro/manager/guest.php?token=' . $guestToken;
                        }
                    }
                } catch (Exception $e) {
                    // Log CM error for debugging
                    $logDir = __DIR__ . '/logs';
                    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
                    $logMsg = date('Y-m-d H:i:s') . " CM INSERT FAIL: " . $e->getMessage() . " | Booking: " . json_encode($booking) . "\n";
                    @file_put_contents($logDir . '/cm-errors.log', $logMsg, FILE_APPEND | LOCK_EX);

                    // Notifica pe ntfy despre eroarea CM
                    @file_get_contents("https://ntfy.sh/ss-cm-9f3k7x2m", false, stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => "Title: EROARE CM - Rezervare neprocesata!\nTags: warning\nPriority: urgent",
                            'content' => "Rezervare {$booking['name']} ({$booking['checkin']} - {$booking['checkout']}) NU a intrat in CM!\nEroare: " . $e->getMessage(),
                            'timeout' => 5
                        ]
                    ]));
                }

                // Add check-in link to booking for WhatsApp
                if ($checkinLink) {
                    $booking['checkin_link'] = $checkinLink;
                }

                // Notificare ntfy - REZERVARE NOUA
                $ntfyMsg = "Rezervare NOUA pe site!\n\n";
                $ntfyMsg .= "Oaspete: {$booking['name']}\n";
                $ntfyMsg .= "Proprietate: {$booking['property']}\n";
                $ntfyMsg .= "Check-in: {$booking['checkin']}\n";
                $ntfyMsg .= "Check-out: {$booking['checkout']}\n";
                $ntfyMsg .= "Nopti: {$booking['nights']}\n";
                $ntfyMsg .= "Oaspeti: {$booking['guests']}\n";
                $ntfyMsg .= "Total: {$booking['total']} RON\n";
                $ntfyMsg .= "Avans platit: {$booking['avans_paid']} RON\n";
                $ntfyMsg .= "Rest: {$booking['rest_to_charge']} RON\n";
                $ntfyMsg .= "Tel: {$booking['phone']}\n";
                $ntfyMsg .= "Email: {$booking['email']}\n";
                if ($checkinLink) $ntfyMsg .= "\nCheck-in link: {$checkinLink}";

                @file_get_contents("https://ntfy.sh/ss-cm-9f3k7x2m", false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Title: Rezervare noua: {$booking['property']}\nTags: house,moneybag\nPriority: high",
                        'content' => $ntfyMsg,
                        'timeout' => 5
                    ]
                ]));

                // Trimite WhatsApp
                sendWhatsAppNotification($booking, $whatsapp_config);
                markSessionProcessed($session_id);

                // Meta Conversions API — Purchase event server-side (dedup cu Pixel browser-side prin event_id)
                $cookies = fb_get_cookies();
                $first_name = explode(' ', trim($booking['name']))[0] ?? '';
                $last_name  = trim(substr(strstr(trim($booking['name']), ' ') ?: '', 1));
                $purchase_value = floatval($booking['avans_paid'] ?? $booking['total']);

                fb_capi_send('Purchase',
                    [
                        'value' => $purchase_value,
                        'currency' => 'RON',
                        'content_type' => 'product',
                        'content_category' => 'cazare',
                        'content_name' => $booking['property'] ?? 'mamaia-nord',
                        'content_ids' => [$metadata->property_key ?? 'mamaia-nord'],
                        'num_items' => 1,
                        'predicted_ltv' => floatval($booking['total']),
                    ],
                    [
                        'email' => $booking['email'],
                        'phone' => $booking['phone'],
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'country' => 'ro',
                        'ip' => fb_get_client_ip(),
                        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'fbp' => $cookies['fbp'],
                        'fbc' => $cookies['fbc'],
                        'url' => 'https://summer-smile.ro/rezervare-confirmare.php',
                    ],
                    $booking['id']  // event_id pentru deduplicare cu pixel browser
                );

                // Google Ads Enhanced Conversions — server-side (dedup cu gtag browser-side prin transaction_id = booking id)
                google_ads_enhanced_conversion('purchase',
                    [
                        'value' => $purchase_value,
                        'transaction_id' => $booking['id'],
                    ],
                    [
                        'email' => $booking['email'],
                        'phone' => $booking['phone'],
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'country' => 'RO',
                    ],
                    $booking['id']  // event_id (= transaction_id) — același cu browser pentru dedup
                );
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    } // end rate limit else
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rezervare Confirmata! | Summer Smile</title>

<!-- Meta Pixel — Summer Smile -->
<script>window.SS_FB_CONFIG = { pixelId: '2240541116756836' };</script>
<script src="fb-pixel-loader.js?v=1" async></script>

<!-- Google Ads + GA4 — Summer Smile -->
<script>window.SS_GADS_CONFIG = {
    conversionId: 'AW-XXXXXXXXXX',
    ga4Id: 'G-XXXXXXXXXX',
    conversionLabel: '2240541116756836XX'
};</script>
<script src="google-ads-loader.js?v=1" async></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:25px;padding:50px;text-align:center;max-width:550px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.icon{width:100px;height:100px;background:linear-gradient(135deg,#4CAF50,#8BC34A);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 30px}
.icon svg{width:50px;height:50px;fill:#fff}
h1{color:#1a2334;font-size:1.8rem;margin-bottom:15px}
.sub{color:#666;font-size:1.1rem;margin-bottom:30px}
.box{background:#f8f9fa;border-radius:15px;padding:25px;margin-bottom:20px;text-align:left}
.box h3{color:#1a2334;font-size:.9rem;text-transform:uppercase;margin-bottom:15px}
.row{display:flex;justify-content:space-between;margin-bottom:10px}
.row span:first-child{color:#666}
.row span:last-child{font-weight:600;color:#1a2334}
.pay{background:#e3f2fd;border-radius:15px;padding:20px;margin-bottom:20px;text-align:left}
.pay h4{color:#1565c0;margin-bottom:15px}
.btn-group{display:flex;gap:15px;margin-top:25px}
.btn{flex:1;padding:14px 20px;border-radius:12px;font-weight:600;text-decoration:none;text-align:center}
.btn-primary{background:linear-gradient(135deg,#25D366,#128C7E);color:#fff}
.btn-secondary{background:#f0f0f0;color:#1a2334}
.contact{margin-top:25px;padding-top:25px;border-top:1px solid #eee;color:#888}
.contact a{color:#667eea}
.error{background:#ffebee;border-radius:15px;padding:20px;color:#c62828;margin-bottom:20px}
</style>
</head>
<body>
<div class="card">
<?php if ($error): ?>
<div class="error"><h3>Eroare</h3><p><?php echo htmlspecialchars($error); ?></p></div>
<?php elseif ($booking): ?>
<div class="icon"><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg></div>
<h1>Rezervare Confirmata!</h1>
<p class="sub">Multumim, <?php echo htmlspecialchars($booking['name']); ?>!</p>
<div class="box">
<h3>Detalii</h3>
<div class="row"><span>Cod</span><span><?php echo $booking['id']; ?></span></div>
<div class="row"><span>Proprietate</span><span><?php echo htmlspecialchars($booking['property']); ?></span></div>
<div class="row"><span>Check-in</span><span><?php echo date('d.m.Y', strtotime($booking['checkin'])); ?></span></div>
<div class="row"><span>Check-out</span><span><?php echo date('d.m.Y', strtotime($booking['checkout'])); ?></span></div>
</div>
<div class="pay">
<h4>Plati</h4>
<p>Avans platit: <strong><?php echo $booking['avans_paid']; ?> RON</strong></p>
<p>Rest la check-in: <strong><?php echo $booking['rest_to_charge']; ?> RON</strong></p>
</div>
<?php if ($checkinLink): ?>
<div class="box" style="background:#f0fdf4;border:2px solid #22c55e;">
<h3 style="color:#166534;">Link Check-in</h3>
<p style="color:#475569;margin-bottom:10px;">Aici gasiti toate detaliile pentru sejur (WiFi, cod acces, reguli casa):</p>
<a href="<?php echo htmlspecialchars($checkinLink); ?>" target="_blank" style="color:#0369a1;word-break:break-all;font-weight:600;"><?php echo htmlspecialchars($checkinLink); ?></a>
</div>
<?php endif; ?>
<div class="btn-group">
<a href="https://wa.me/40746954812" class="btn btn-primary" target="_blank">WhatsApp</a>
<a href="index.html" class="btn btn-secondary">Acasa</a>
</div>
<div class="contact">Intrebari? <a href="tel:+40746954812">0746 954 812</a></div>
<script>
// Pixel browser-side Purchase — same eventID ca CAPI server-side (dedup automat la Meta)
(function () {
    var fbDone = false, gadsDone = false;
    function fireFB() {
        if (fbDone) return;
        if (!window.SS || !window.SS.fbTrack) return setTimeout(fireFB, 200);
        SS.fbTrack('Purchase', {
            value: <?php echo floatval($booking['avans_paid'] ?? $booking['total']); ?>,
            currency: 'RON',
            content_type: 'product',
            content_category: 'cazare',
            content_name: <?php echo json_encode($booking['property'] ?? 'mamaia-nord'); ?>,
            content_ids: [<?php echo json_encode($metadata->property_key ?? 'mamaia-nord'); ?>],
            num_items: 1
        }, { eventID: <?php echo json_encode($booking['id']); ?> });
        fbDone = true;
    }
    function fireGAds() {
        if (gadsDone) return;
        if (!window.SS || !window.SS.gadsConversion) return setTimeout(fireGAds, 200);
        SS.gadsConversion(
            <?php echo floatval($booking['avans_paid'] ?? $booking['total']); ?>,
            <?php echo json_encode($booking['id']); ?>,
            {
                email: <?php echo json_encode($booking['email']); ?>,
                phone: <?php echo json_encode($booking['phone']); ?>,
                first_name: <?php echo json_encode(explode(' ', trim($booking['name']))[0] ?? ''); ?>,
                last_name: <?php echo json_encode(trim(substr(strstr(trim($booking['name']), ' ') ?: '', 1))); ?>
            },
            { property: <?php echo json_encode($metadata->property_key ?? 'mamaia-nord'); ?> }
        );
        gadsDone = true;
    }
    fireFB();
    fireGAds();
})();
</script>
<?php else: ?>
<h1>Sesiune invalida</h1>
<p>Nu am gasit rezervarea.</p>
<a href="index.html" class="btn btn-primary" style="margin-top:20px;display:inline-block">Acasa</a>
<?php endif; ?>
</div>
</body>
</html>
