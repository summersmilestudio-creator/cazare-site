<?php
/**
 * Meta (Facebook) Pixel + Conversions API config
 *
 * Include cu: <?php require_once __DIR__ . '/fb-pixel.php'; ?>
 * Apoi pune în <head>: <?php fb_pixel_head(); ?>
 * Și pe paginile speciale: <?php fb_pixel_event('ViewContent', [...]); ?>
 *
 * Pixel ID + Access Token se citesc din .env (NU hardcoded în repo).
 */

// Citește credentiale din .env (sau fallback la env vars sistem)
function fb_pixel_config() {
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $cfg = ['pixel_id' => '', 'access_token' => '', 'test_event_code' => ''];
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) continue;
            $key = trim($parts[0]);
            $val = trim($parts[1], " \t\n\r\0\x0B\"'");
            if ($key === 'FB_PIXEL_ID') $cfg['pixel_id'] = $val;
            if ($key === 'FB_CAPI_TOKEN') $cfg['access_token'] = $val;
            if ($key === 'FB_TEST_EVENT_CODE') $cfg['test_event_code'] = $val;
        }
    }
    return $cfg;
}

/**
 * Returnează snippet pentru <head> — Pixel base + PageView automat.
 */
function fb_pixel_head() {
    $cfg = fb_pixel_config();
    if (empty($cfg['pixel_id'])) {
        echo "<!-- FB Pixel: not configured (set FB_PIXEL_ID in .env) -->\n";
        return;
    }
    $pid = htmlspecialchars($cfg['pixel_id'], ENT_QUOTES);
    echo <<<HTML

<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pid}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$pid}&ev=PageView&noscript=1"/></noscript>
<!-- End Meta Pixel Code -->

HTML;
}

/**
 * Trimite eveniment Pixel browser-side.
 * @param string $event ViewContent | InitiateCheckout | Purchase | Lead | etc.
 * @param array  $params Parametri standard Meta (value, currency, content_ids, etc.)
 */
function fb_pixel_event($event, $params = []) {
    $cfg = fb_pixel_config();
    if (empty($cfg['pixel_id'])) return;
    $eventName = htmlspecialchars($event, ENT_QUOTES);
    $json = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) $json = '{}';
    echo "<script>fbq('track', '{$eventName}', {$json});</script>\n";
}

/**
 * Server-side Conversions API event (de apelat din webhook-uri Stripe sau cron).
 * Returnează true dacă a trimis cu succes, false altfel.
 *
 * @param string $event_name Numele eveniment (Purchase, etc.)
 * @param array  $custom_data Custom data (value, currency, content_ids, num_items)
 * @param array  $user_data User data (email, phone, ip, ua, fbp, fbc, country)
 * @param string $event_id ID idempotent — ACELAȘI ca cel din browser pentru dedup
 */
function fb_capi_send($event_name, $custom_data = [], $user_data = [], $event_id = null) {
    $cfg = fb_pixel_config();
    if (empty($cfg['pixel_id']) || empty($cfg['access_token'])) {
        error_log('[FB CAPI] missing pixel_id or access_token');
        return false;
    }

    // Hash PII (email, phone) — Meta cere sha256
    $hashed = [];
    if (!empty($user_data['email'])) {
        $hashed['em'] = hash('sha256', strtolower(trim($user_data['email'])));
    }
    if (!empty($user_data['phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $user_data['phone']);
        $hashed['ph'] = hash('sha256', $phone);
    }
    if (!empty($user_data['first_name'])) {
        $hashed['fn'] = hash('sha256', strtolower(trim($user_data['first_name'])));
    }
    if (!empty($user_data['last_name'])) {
        $hashed['ln'] = hash('sha256', strtolower(trim($user_data['last_name'])));
    }
    if (!empty($user_data['country'])) {
        $hashed['country'] = hash('sha256', strtolower(trim($user_data['country'])));
    }
    if (!empty($user_data['city'])) {
        $hashed['ct'] = hash('sha256', strtolower(preg_replace('/\s+/', '', $user_data['city'])));
    }

    // IP + UA + cookie pentru match quality
    if (!empty($user_data['ip']))   $hashed['client_ip_address']   = $user_data['ip'];
    if (!empty($user_data['ua']))   $hashed['client_user_agent']   = $user_data['ua'];
    if (!empty($user_data['fbp']))  $hashed['fbp'] = $user_data['fbp'];
    if (!empty($user_data['fbc']))  $hashed['fbc'] = $user_data['fbc'];

    $event = [
        'event_name'   => $event_name,
        'event_time'   => time(),
        'action_source'=> 'website',
        'event_source_url' => $user_data['url'] ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'summer-smile.ro') . ($_SERVER['REQUEST_URI'] ?? '/')),
        'user_data'    => $hashed,
        'custom_data'  => $custom_data,
    ];
    if ($event_id) $event['event_id'] = $event_id;

    $payload = ['data' => [$event]];
    if (!empty($cfg['test_event_code'])) {
        $payload['test_event_code'] = $cfg['test_event_code'];
    }

    $url = "https://graph.facebook.com/v19.0/{$cfg['pixel_id']}/events?access_token=" . urlencode($cfg['access_token']);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 8,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log pentru debugging
    @file_put_contents(
        __DIR__ . '/logs/fb-capi.log',
        date('c') . " [{$code}] {$event_name}: {$resp}\n",
        FILE_APPEND
    );

    return $code >= 200 && $code < 300;
}

/**
 * Helper — extrage IP real (în spatele Cloudflare/proxy).
 */
function fb_get_client_ip() {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '';
}

/**
 * Helper — extrage cookie _fbp și _fbc (pentru match quality).
 */
function fb_get_cookies() {
    return [
        'fbp' => $_COOKIE['_fbp'] ?? '',
        'fbc' => $_COOKIE['_fbc'] ?? '',
    ];
}
