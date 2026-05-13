<?php
require_once __DIR__ . '/includes/bot-blocker.php';

// Proprietatile Mamaia Nord sunt disponibile doar in sezon: 20.06 - 10.09

// ============================================
// CONFIGURARE STRIPE + GAP PRICING + LOYALTY
// ============================================
// ============================================
// LOYALTY SYSTEM INTEGRATION
// ============================================
if (!defined('SUMMER_SMILE')) define('SUMMER_SMILE', true);
require_once __DIR__ . '/includes/track.php';

require_once 'includes/loyalty-integration.php';

// Initializare autentificare si loyalty
$loyaltyInfo = initLoyaltyForBooking();
$currentUser = $loyaltyInfo['user'];
$loyaltyDiscount = $loyaltyInfo['loyaltyDiscount'];
$loyaltyLevel = $loyaltyInfo['loyaltyLevel'];
$loyaltyLevelName = $loyaltyInfo['loyaltyLevelName'];
$userId = $loyaltyInfo['userId'];


require_once __DIR__ . '/includes/stripe-config.php';

require_once 'mamaia-nord-pricing.php';

// Proprietati Mamaia Nord
$properties = [
    'summer-smile' => ['name' => 'Summer Smile - Studio Mamaia Nord', 'image' => 'images/mamaia-nord/summer-smile/01d4c4ce-1e4c-4b8a-a842-8fc33470eea7.jpeg', 'type' => 'studio', 'summer_only' => true],
    'studio-onix' => ['name' => 'Studio Onix - Mamaia Nord', 'image' => 'images/mamaia-nord/studio-onix/1d88a577-a55e-4277-9050-657ee24e3777.jfif', 'type' => 'studio', 'summer_only' => true],
    'vitamin-sea' => ['name' => 'Vitamin Sea - Studio Mamaia Nord', 'image' => 'images/mamaia-nord/vitamin-sea/059b9f61-0fc4-49a9-a9e9-ebc10595987a.jpeg', 'type' => 'studio', 'summer_only' => true],
    'studio-129-n10' => ['name' => 'Studio 129 N10 - Mamaia Nord', 'image' => 'images/mamaia-nord/summer-smile/01d4c4ce-1e4c-4b8a-a842-8fc33470eea7.jpeg', 'type' => 'studio', 'summer_only' => true],
    'lakeview-studio' => ['name' => 'Lakeview Studio - Mamaia Nord', 'image' => 'images/mamaia-nord/summer-smile/01d4c4ce-1e4c-4b8a-a842-8fc33470eea7.jpeg', 'type' => 'studio', 'summer_only' => true],
    'summer-smile-2' => ['name' => 'Summer Smile II - Apartament 2 camere', 'image' => 'images/mamaia-nord/summer-smile-2/01d4c4ce-1e4c-4b8a-a842-8fc33470eea7.jpeg', 'type' => 'apartament', 'parking' => true, 'summer_only' => true],
    'la-dame-blanche' => ['name' => 'La Dame Blanche - Mamaia Nord', 'image' => 'images/mamaia-nord/summer-smile/01d4c4ce-1e4c-4b8a-a842-8fc33470eea7.jpeg', 'type' => 'studio', 'summer_only' => true],
    'dd-apartment-meraki' => ['name' => 'DD Apartment at Meraki Resort & Spa - Mamaia Nord', 'image' => 'images/mamaia-nord/dd-apartment-meraki/dd-meraki-01.jpeg', 'type' => 'apartament', 'summer_only' => true],
    'aer-by-meraky' => ['name' => 'Aer by Meraky - Mamaia Nord', 'image' => 'images/mamaia-nord/aer-by-meraky/aer-meraky-01.jpeg', 'type' => 'apartament', 'summer_only' => true]
];
$selected_property = isset($_GET['property']) && isset($properties[$_GET['property']]) ? $_GET['property'] : 'summer-smile';
$property = $properties[$selected_property];
$is_closed = !empty($property['closed']);
$is_summer_only = !empty($property['summer_only']);

// Pret de baza in functie de tip proprietate
$property_type_for_price = $property['type'] ?? 'studio';
$has_explicit_pricing = mamaiaHasPropertyOverride($selected_property);
$extra_apartment = (!$has_explicit_pricing && $property_type_for_price === 'apartament') ? 50 : 0;
if ($has_explicit_pricing) {
    // Pret de baza = primul pret de sezon definit pentru proprietate
    $pret_noapte = getMamaiaPriceForDate(date('Y-m-d'), $selected_property);
} else {
    $pret_noapte = 420 + $extra_apartment;
}
$pret_crescut = false;
$pret_scazut = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_checkout') {
    if ($is_closed) {
        $error_message = 'Aceasta proprietate este momentan indisponibila.';
    } elseif ($is_summer_only && !$has_explicit_pricing) {
        $checkin_date = $_POST['checkin'] ?? '';
        $summer_start = date('Y') . '-06-20';
        $summer_end = date('Y') . '-09-10';
        if ($checkin_date < $summer_start || $checkin_date > $summer_end) {
            $error_message = 'Aceasta proprietate este disponibila doar in sezonul de vara (20 iunie - 10 septembrie).';
        }
    }
    if (!isset($error_message) && !validatePaymentCsrf($_POST['csrf_token'] ?? '')) {
        $error_message = 'Sesiune expirata. Va rugam reincarcati pagina.';
    } elseif (!isset($error_message) && !checkPaymentRateLimit('checkout')) {
        $error_message = 'Prea multe incercari. Asteptati cateva minute.';
    } elseif (!isset($error_message)) {
    initStripe();
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $guests = $_POST['guests'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $date1 = new DateTime($checkin);
    $date2 = new DateTime($checkout);
    $nights = $date2->diff($date1)->days;
    $property_type = $property["type"] ?? "studio";
    $validation = validateMamaiaBooking($checkin, $checkout, $property_type, $selected_property);
    if (!$validation['allowed']) {
        $error_message = $validation['message'];
    } else {
        $total = $validation['total_price'];

        // Aplica discount loyalty daca utilizatorul este logat
        $loyaltyDiscountAmount = 0;
        $originalTotal = $total;
        if ($loyaltyDiscount > 0) {
            $loyaltyDiscountAmount = round($total * ($loyaltyDiscount / 100), 2);
            $total = $total - $loyaltyDiscountAmount;
        }
        $pret_final = $validation['average_per_night'];
        $subtotal = $total;
        $avans = ceil($total * 0.30);
        $rest_plata = $total - $avans;
        $is_gap = $validation['is_gap'] ?? false;
        $gap_percent = $validation['increase_percent'] ?? 0;
        try {
            $customers = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
            if (count($customers->data) > 0) { $customer = $customers->data[0]; }
            else { $customer = \Stripe\Customer::create(['email' => $email, 'name' => $name, 'phone' => $phone, 'metadata' => ['source' => 'summer-smile.ro']]); }
            $product_name = $property["name"] . " - Avans 30%";
            $description = "Check-in: $checkin | Check-out: $checkout | $nights nopti";
            if ($is_gap) { $description .= " | Oferta gap +{$gap_percent}%"; }
            $checkout_session = \Stripe\Checkout\Session::create([
                'customer' => $customer->id, 'payment_method_types' => ['card'],
                'line_items' => [['price_data' => ['currency' => 'ron', 'product_data' => ['name' => $product_name, 'description' => $description], 'unit_amount' => $avans * 100], 'quantity' => 1]],
                'mode' => 'payment', 'payment_intent_data' => ['setup_future_usage' => 'off_session'],
                'success_url' => 'https://summer-smile.ro/rezervare-confirmare.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'https://summer-smile.ro/rezervare-mamaia-nord.php?cancelled=1',
                'metadata' => ['checkin' => $checkin, 'checkout' => $checkout, 'nights' => $nights, 'guests' => $guests, 'name' => $name, 'phone' => $phone, 'total' => $total, 'avans' => $avans, 'rest_plata' => $rest_plata, 'property' => $property['name'], 'is_gap' => $is_gap ? 'yes' : 'no', 'gap_percent' => $gap_percent, 'price_per_night' => $pret_final, 'user_id' => $userId, 'loyalty_discount' => $loyaltyDiscount, 'loyalty_discount_amount' => $loyaltyDiscountAmount, 'original_total' => $originalTotal]
            ]);
            header('Location: ' . $checkout_session->url);
            exit;
        } catch (Exception $e) { $error_message = $e->getMessage(); }
    }
    } // end else (csrf/rate-limit passed)
}
$cancelled = isset($_GET['cancelled']) ? true : false;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'ro';
if (!in_array($lang, ['ro', 'en', 'hu', 'de'])) $lang = 'ro';
// Traduceri pentru pagina de rezervare
$translations = [
    'ro' => [
        'page_title' => 'Rezervare',
        'online_booking' => 'Rezervare Online - Mamaia Nord',
        'select_stay' => 'Selecteaza Sejurul',
        'booking_cancelled' => 'Plata a fost anulata. Puteti incerca din nou.',
        'your_data' => 'Datele Dumneavoastra',
        'full_name' => 'Nume complet',
        'name_placeholder' => 'Nume si prenume',
        'phone' => 'Telefon',
        'phone_placeholder' => '07xx xxx xxx',
        'email' => 'Email',
        'email_placeholder' => 'email@exemplu.ro',
        'your_booking' => 'Rezervarea Ta',
        'checkin' => 'Check-in',
        'checkout' => 'Check-out',
        'select' => 'Selecteaza',
        'guests' => 'Numar oaspeti',
        'nights' => 'nopti',
        'per_night' => '/ noapte',
        'total_stay' => 'Total sejur',
        'pay_now' => 'Platesti acum (30%)',
        'pay_checkin' => 'Rest la check-in (70%)',
        'reserve_now' => 'Rezerva Acum',
        'how_payment' => 'Cum functioneaza plata',
        'payment_info' => 'Platesti acum 30% din total. Restul de 70% se incaseaza automat de pe card in ziua check-in-ului.',
        'cancel_policy' => 'Politica de anulare',
        'cancel_info' => 'Anulare gratuita cu min. 5 zile inainte de check-in (rambursare completa). Sub 5 zile - avansul de 30% nu se ramburseaza.',
        'secure_payment' => 'Plata securizata prin Stripe',
        'available' => 'Disponibil',
        'blocked' => 'Ocupat',
        'apartment_2rooms' => 'Apartament 2 camere',
        'free_parking' => 'Parcare gratuita',
        'nav_home' => 'Acasa',
        'nav_cleaning' => 'Curatenie',
        'nav_property' => 'Property Manager',
        'nav_contact' => 'Contact',
        'months' => 'Ianuarie,Februarie,Martie,Aprilie,Mai,Iunie,Iulie,August,Septembrie,Octombrie,Noiembrie,Decembrie'
    ],
    'en' => [
        'page_title' => 'Booking',
        'online_booking' => 'Online Booking - Mamaia Nord',
        'select_stay' => 'Select Your Stay',
        'booking_cancelled' => 'Payment was cancelled. You can try again.',
        'your_data' => 'Your Details',
        'full_name' => 'Full name',
        'name_placeholder' => 'First and last name',
        'phone' => 'Phone',
        'phone_placeholder' => '+40 7xx xxx xxx',
        'email' => 'Email',
        'email_placeholder' => 'email@example.com',
        'your_booking' => 'Your Booking',
        'checkin' => 'Check-in',
        'checkout' => 'Check-out',
        'select' => 'Select',
        'guests' => 'Number of guests',
        'nights' => 'nights',
        'per_night' => '/ night',
        'total_stay' => 'Total stay',
        'pay_now' => 'Pay now (30%)',
        'pay_checkin' => 'Rest at check-in (70%)',
        'reserve_now' => 'Book Now',
        'how_payment' => 'How payment works',
        'payment_info' => 'Pay 30% now. The remaining 70% will be automatically charged on check-in day.',
        'cancel_policy' => 'Cancellation policy',
        'cancel_info' => 'Free cancellation up to 5 days before check-in (full refund). Less than 5 days - 30% deposit is non-refundable.',
        'secure_payment' => 'Secure payment via Stripe',
        'available' => 'Available',
        'blocked' => 'Blocked',
        'apartment_2rooms' => '2-Room Apartment',
        'free_parking' => 'Free parking',
        'nav_home' => 'Home',
        'nav_cleaning' => 'Cleaning',
        'nav_property' => 'Property Manager',
        'nav_contact' => 'Contact',
        'months' => 'January,February,March,April,May,June,July,August,September,October,November,December'
    ],
    'hu' => [
        'page_title' => 'Foglalas',
        'online_booking' => 'Online Foglalas - Mamaia Nord',
        'select_stay' => 'Valassza ki a tartozkodasat',
        'booking_cancelled' => 'A fizetes megszakadt. Probalkozhat ujra.',
        'your_data' => 'Az On adatai',
        'full_name' => 'Teljes nev',
        'name_placeholder' => 'Vezeteknev es keresztnev',
        'phone' => 'Telefon',
        'phone_placeholder' => '+40 7xx xxx xxx',
        'email' => 'Email',
        'email_placeholder' => 'email@pelda.hu',
        'your_booking' => 'Az On foglalasa',
        'checkin' => 'Erkezes',
        'checkout' => 'Tavozas',
        'select' => 'Valasszon',
        'guests' => 'Vendegek szama',
        'nights' => 'ejszaka',
        'per_night' => '/ ejszaka',
        'total_stay' => 'Teljes osszeg',
        'pay_now' => 'Fizessen most (30%)',
        'pay_checkin' => 'Maradek erkezeskor (70%)',
        'reserve_now' => 'Foglalas most',
        'how_payment' => 'Hogyan mukodik a fizetes',
        'payment_info' => 'Most 30%-ot fizet. A maradek 70%-ot automatikusan levonjuk az erkezes napjan.',
        'cancel_policy' => 'Lemondasi feltetelek',
        'cancel_info' => 'Ingyenes lemondas az erkezest megelozo 5. napig (teljes visszaterites). 5 napon belul - a 30% eloleget nem terjtjuk vissza.',
        'secure_payment' => 'Biztonsagos fizetes Stripe-pal',
        'available' => 'Elerheto',
        'blocked' => 'Foglalt',
        'apartment_2rooms' => '2 szobas apartman',
        'free_parking' => 'Ingyenes parkolas',
        'nav_home' => 'Fooldal',
        'nav_cleaning' => 'Takaritas',
        'nav_property' => 'Ingatlankezelo',
        'nav_contact' => 'Kapcsolat',
        'months' => 'Januar,Februar,Marcius,Aprilis,Majus,Junius,Julius,Augusztus,Szeptember,Oktober,November,December'
    ],
    'de' => [
        'page_title' => 'Buchung',
        'online_booking' => 'Online Buchung - Mamaia Nord',
        'select_stay' => 'Wahlen Sie Ihren Aufenthalt',
        'booking_cancelled' => 'Zahlung wurde abgebrochen. Sie konnen es erneut versuchen.',
        'your_data' => 'Ihre Daten',
        'full_name' => 'Vollstandiger Name',
        'name_placeholder' => 'Vor- und Nachname',
        'phone' => 'Telefon',
        'phone_placeholder' => '+40 7xx xxx xxx',
        'email' => 'E-Mail',
        'email_placeholder' => 'email@beispiel.de',
        'your_booking' => 'Ihre Buchung',
        'checkin' => 'Check-in',
        'checkout' => 'Check-out',
        'select' => 'Auswahlen',
        'guests' => 'Anzahl der Gaste',
        'nights' => 'Nachte',
        'per_night' => '/ Nacht',
        'total_stay' => 'Gesamtaufenthalt',
        'pay_now' => 'Jetzt zahlen (30%)',
        'pay_checkin' => 'Rest beim Check-in (70%)',
        'reserve_now' => 'Jetzt buchen',
        'how_payment' => 'So funktioniert die Zahlung',
        'payment_info' => 'Zahlen Sie jetzt 30%. Die restlichen 70% werden automatisch am Check-in-Tag abgebucht.',
        'cancel_policy' => 'Stornierungsbedingungen',
        'cancel_info' => 'Kostenlose Stornierung bis 5 Tage vor Check-in (volle Ruckerstattung). Weniger als 5 Tage - 30% Anzahlung wird nicht erstattet.',
        'secure_payment' => 'Sichere Zahlung uber Stripe',
        'available' => 'Verfugbar',
        'blocked' => 'Belegt',
        'apartment_2rooms' => '2-Zimmer-Apartment',
        'free_parking' => 'Kostenloser Parkplatz',
        'nav_home' => 'Startseite',
        'nav_cleaning' => 'Reinigung',
        'nav_property' => 'Immobilienverwaltung',
        'nav_contact' => 'Kontakt',
        'months' => 'Januar,Februar,Marz,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember'
    ]
];
$t = $translations[$lang];

// Link-uri pagini in functie de limba
$mamaia_page = ($lang == 'ro') ? 'mamaia-nord.html' : 'mamaia-nord-' . $lang . '.html';
$oradea_page = 'oradea.html' . (($lang != 'ro') ? '?lang=' . $lang : '');
$index_page = 'index.html' . (($lang != 'ro') ? '?lang=' . $lang : '');
$curateanie_page = 'curateanie.html' . (($lang != 'ro') ? '?lang=' . $lang : '');
$property_page = 'property-manager.html' . (($lang != 'ro') ? '?lang=' . $lang : '');
$contact_page = 'contact.html' . (($lang != 'ro') ? '?lang=' . $lang : '');

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t["page_title"]; ?> - <?php echo $property["name"]; ?> | Summer Smile</title>

    <!-- Meta Pixel — Summer Smile (InitiateCheckout auto-fires pe pagina rezervare) -->
    <script>window.SS_FB_CONFIG = { pixelId: '2240541116756836' };</script>
    <script src="fb-pixel-loader.js?v=1" async></script>

    <!-- Google Ads + GA4 — Summer Smile -->
    <script>window.SS_GADS_CONFIG = { conversionId: 'AW-XXXXXXXXXX', ga4Id: 'G-XXXXXXXXXX', conversionLabel: '2240541116756836XX' };</script>
    <script src="google-ads-loader.js?v=1" async></script>

    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f0f23 100%);min-height:100vh}
        .top-contact-bar{background:linear-gradient(135deg,#1a1a2e,#16213e);padding:10px 0;font-size:0.85rem}
        .top-contact-bar .container{display:flex;justify-content:flex-end;align-items:center;max-width:1200px;margin:0 auto;padding:0 20px;gap:40px}
        .top-contact-bar a{color:#FFD700;text-decoration:none;display:flex;align-items:center;gap:8px}
        .contact-links{display:flex;gap:25px}
        .social-links{display:flex;gap:12px}
        .social-links a{width:30px;height:30px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all 0.3s}
        .social-links a.facebook{color:#1877F2}.social-links a.instagram{color:#E4405F}.social-links a.tiktok{color:#fff}
        .social-links a.facebook:hover{background:#1877F2;color:#fff}
        .social-links a.instagram:hover{background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff}
        .social-links a.tiktok:hover{background:#000;color:#00f2ea}
        .site-header{background:linear-gradient(135deg,rgba(26,35,52,0.95),rgba(44,62,80,0.95));backdrop-filter:blur(10px);color:#F5F5F5;padding:0.95rem 0;position:sticky;top:0;z-index:1000;box-shadow:0 4px 20px rgba(0,0,0,0.2);border-bottom:2px solid rgba(218,165,32,0.3)}
        .site-header nav{max-width:1400px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center}
        .logo a{display:flex;align-items:center;overflow:hidden;border-radius:10px}
        .logo img{height:72px;width:144px;object-fit:cover;object-position:center;box-shadow:0 4px 12px rgba(218,165,32,0.4);transition:transform 0.3s;border:none;display:block;transform:scale(1.15)}
        .nav-links{display:flex;list-style:none;gap:1.6rem;margin-left:auto;margin-right:0.5rem}
        .nav-links a{color:#E8E8E8;text-decoration:none;font-weight:500;font-size:1rem;letter-spacing:0.8px;transition:all 0.3s;position:relative}
        .nav-links a::after{content:'';position:absolute;bottom:-5px;left:0;width:0;height:2px;background:linear-gradient(90deg,#DAA520,#FFD700);transition:width 0.3s}
        .nav-links a:hover{color:#FFD700}.nav-links a:hover::after{width:100%}
        .hamburger{display:none;flex-direction:column;cursor:pointer;padding:0.5rem;z-index:1001}
        .hamburger span{width:25px;height:3px;background:#FFD700;margin:3px 0;transition:all 0.3s;border-radius:2px}
        .hamburger.active span:nth-child(1){transform:rotate(45deg) translate(7px,7px)}
        .hamburger.active span:nth-child(2){opacity:0}
        .hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(7px,-7px)}
        .main-content{padding:30px 20px}
        .page-header{text-align:center;margin-bottom:30px}
        .page-header h1{font-family:'Playfair Display',serif;font-size:2rem;color:#d4af37;margin-bottom:8px}
        .page-header p{color:#8892b0;font-size:0.95rem}
        .booking-wrapper{max-width:1000px;margin:0 auto;display:grid;grid-template-columns:1fr 380px;gap:30px}
        .calendar-section{background:linear-gradient(145deg,#2a2a40,#31314e);border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.5),inset 0 1px 0 rgba(255,255,255,0.05);padding:32px;border:1px solid rgba(212,175,55,0.1)}
        .alert{padding:15px;border-radius:10px;margin-bottom:20px}
        .alert-warning{background:rgba(255,152,0,0.1);color:#ffb74d;border:1px solid rgba(255,152,0,0.2)}
        .alert-error{background:rgba(244,67,54,0.1);color:#ef5350;border:1px solid rgba(244,67,54,0.2)}
        .gap-notice{background:linear-gradient(135deg,rgba(76,175,80,0.15),rgba(139,195,74,0.1));border:1px solid rgba(76,175,80,0.3);border-radius:10px;padding:12px 15px;margin-bottom:15px;font-size:0.85rem;color:#81c784}
        .gap-notice strong{color:#a5d6a7}
        .min-nights-notice{background:rgba(255,152,0,0.1);border:1px solid rgba(255,152,0,0.2);border-radius:10px;padding:12px 15px;margin-bottom:15px;font-size:0.85rem;color:#ffb74d}
        .calendar-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .calendar-header h2{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:500;color:#fff;letter-spacing:0.5px}
        .cal-nav-buttons{display:flex;gap:8px}
        .cal-nav-btn{width:42px;height:42px;border-radius:50%;border:1px solid rgba(212,175,55,0.3);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.3s;font-size:14px;color:#d4af37}
        .cal-nav-btn:hover{background:rgba(212,175,55,0.1);border-color:#d4af37}
        .month-display{text-align:center;font-family:'Playfair Display',serif;font-size:1.2rem;color:#d4af37;margin-bottom:24px;letter-spacing:2px;text-transform:uppercase}
        .calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
        .day-header{text-align:center;font-size:0.7rem;font-weight:500;color:#d4af37;padding:12px 0;text-transform:uppercase;letter-spacing:1px}
        .day-cell{aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:0.9rem;cursor:pointer;border-radius:8px;transition:all 0.3s;position:relative;color:#cacaca;font-weight:300}
        .day-cell:hover:not(.disabled):not(.blocked):not(.empty){background:rgba(212,175,55,0.15);color:#d4af37}
        .day-cell.disabled{color:#3a3a5a;cursor:default}
        .day-cell.blocked{color:#5a4a4a;cursor:not-allowed}
        .day-cell.blocked::before{content:'';position:absolute;width:60%;height:1px;background:#5a4a4a;transform:rotate(-45deg)}
        .day-cell.selected,.day-cell.range-start,.day-cell.range-end{background:linear-gradient(135deg,#d4af37,#b8962e);color:#1a1a2e;font-weight:600;box-shadow:0 4px 15px rgba(212,175,55,0.4)}
        .day-cell.in-range{background:rgba(212,175,55,0.1);border-radius:0}
        .day-cell.range-start{border-radius:8px 0 0 8px}
        .day-cell.range-end{border-radius:0 8px 8px 0}
        .day-cell.today{border:1px solid #d4af37}
        .calendar-legend{display:flex;gap:24px;margin-top:20px;justify-content:center}
        .legend-item{display:flex;align-items:center;gap:8px;font-size:0.75rem;color:#8892b0}
        .legend-dot{width:10px;height:10px;border-radius:3px}
        .legend-dot.available{background:rgba(212,175,55,0.2);border:1px solid rgba(212,175,55,0.5)}
        .legend-dot.blocked{background:#2a2a3a}
        .divider{height:1px;background:linear-gradient(90deg,transparent,rgba(212,175,55,0.3),transparent);margin:28px 0}
        .contact-form h3{font-family:'Playfair Display',serif;font-size:1.1rem;color:#fff;margin-bottom:20px}
        .form-row{display:flex;gap:15px;margin-bottom:15px}
        .form-group{flex:1}
        .form-group label{display:block;font-size:0.7rem;font-weight:500;color:#d4af37;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px}
        .form-group input{width:100%;padding:14px 16px;border:1px solid rgba(212,175,55,0.2);border-radius:10px;font-size:0.95rem;transition:all 0.3s;background:rgba(255,255,255,0.03);color:#fff}
        .form-group input::placeholder{color:#5a5a7a}
        .form-group input:focus{outline:none;border-color:#d4af37;background:rgba(212,175,55,0.05)}
        .sidebar{display:flex;flex-direction:column;gap:20px}
        .property-card{background:linear-gradient(145deg,#2a2a40,#31314e);border-radius:20px;overflow:hidden;box-shadow:0 15px 40px rgba(0,0,0,0.4);border:1px solid rgba(212,175,55,0.1)}
        .property-card img{width:100%;height:200px;object-fit:cover}
        .property-card-info{padding:20px}
        .property-card-info h3{font-family:'Playfair Display',serif;color:#fff;margin-bottom:5px;font-size:1.2rem}
        .property-card-info p{color:#8892b0;font-size:0.85rem}
        .property-card-info .price-tag{margin-top:12px;color:#d4af37;font-size:1.1rem;font-weight:600}
        .property-card-info .price-tag span{font-size:0.8rem;font-weight:400;color:#8892b0}
        .booking-summary{background:linear-gradient(145deg,#2a2a40,#31314e);border-radius:20px;padding:25px;box-shadow:0 15px 40px rgba(0,0,0,0.4);border:1px solid rgba(212,175,55,0.1)}
        .booking-summary h3{font-family:'Playfair Display',serif;color:#fff;margin-bottom:20px;font-size:1.1rem}
        .date-display{display:flex;align-items:center;gap:12px;margin-bottom:20px}
        .date-box{flex:1;text-align:center;padding:14px 12px;background:rgba(255,255,255,0.03);border-radius:12px;border:1px solid rgba(212,175,55,0.1)}
        .date-box label{display:block;font-size:0.65rem;text-transform:uppercase;letter-spacing:1.5px;color:#d4af37;margin-bottom:6px}
        .date-box span{font-family:'Playfair Display',serif;font-size:1rem;color:#fff}
        .date-arrow{color:#d4af37;font-size:1.2rem}
        .guest-selector{margin-bottom:20px}
        .guest-selector label{display:block;font-size:0.7rem;color:#d4af37;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px}
        .guest-counter{display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.03);border-radius:12px;padding:10px 15px;border:1px solid rgba(212,175,55,0.1)}
        .guest-counter button{width:36px;height:36px;border:1px solid rgba(212,175,55,0.3);background:transparent;border-radius:50%;cursor:pointer;font-size:1.2rem;color:#d4af37;transition:all 0.3s}
        .guest-counter button:hover{background:rgba(212,175,55,0.1);border-color:#d4af37}
        .guest-counter span{font-weight:600;font-size:1.1rem;color:#fff}
        .price-breakdown{border-top:1px solid rgba(212,175,55,0.1);padding-top:15px;margin-bottom:20px}
        .price-row{display:flex;justify-content:space-between;margin-bottom:10px;font-size:0.9rem;color:#8892b0}
        .price-row.total{font-weight:600;font-size:1rem;color:#fff;border-top:1px solid rgba(212,175,55,0.2);padding-top:12px;margin-top:12px}
        .price-row.avans{background:rgba(212,175,55,0.1);margin:15px -15px;padding:14px 15px;border-radius:10px;color:#d4af37;font-weight:600}
        .price-row.rest{background:rgba(255,255,255,0.03);margin:5px -15px;padding:12px 15px;border-radius:10px;color:#8892b0;font-size:0.8rem}
        .btn-pay{width:100%;padding:18px 32px;background:linear-gradient(135deg,#d4af37,#b8962e);color:#1a1a2e;border:none;border-radius:12px;font-size:0.9rem;font-weight:600;cursor:pointer;transition:all 0.3s;text-transform:uppercase;letter-spacing:1.5px;display:flex;align-items:center;justify-content:center;gap:10px}
        .btn-pay:hover{box-shadow:0 8px 30px rgba(212,175,55,0.4);transform:translateY(-2px)}
        .btn-pay:disabled{background:#3a3a4a;color:#6a6a7a;cursor:not-allowed;transform:none;box-shadow:none}
        .payment-info{background:rgba(212,175,55,0.05);padding:15px;border-radius:10px;margin-top:15px;font-size:0.8rem;color:#8892b0;border:1px solid rgba(212,175,55,0.1)}
        .payment-info strong{display:block;margin-bottom:6px;color:#d4af37;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px}
        .stripe-badge{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:15px;font-size:0.75rem;color:#5a5a7a}
        @media(max-width:900px){
            .booking-wrapper{grid-template-columns:1fr}.form-row{flex-direction:column}.page-header h1{font-size:1.6rem}
            .top-contact-bar .container{flex-direction:column;gap:10px}.contact-links{flex-direction:column;gap:8px;align-items:center}
            .hamburger{display:flex}
            .nav-links{position:fixed;top:0;left:-100%;width:80%;height:100vh;background:linear-gradient(135deg,#1a1a2e,#16213e);flex-direction:column;justify-content:center;align-items:center;gap:2rem;transition:left 0.3s}
            .nav-links.active{left:0}.nav-links a{font-size:1.2rem}
        }
    </style>
    <script>
        // Sincronizeaza limba din URL cu localStorage
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const langFromUrl = urlParams.get('lang');
            if (langFromUrl && ['ro', 'en', 'hu', 'de'].includes(langFromUrl)) {
                localStorage.setItem('selectedLang', langFromUrl);
            }
        })();
    </script>
    <script src="translations.js"></script>
</head>
<body>
    <div class="top-contact-bar">
        <div class="container">
            <div class="contact-links">
                <a href="tel:+40746954812">&#128222; +40 746 954 812</a>
                <a href="mailto:contact@summer-smile.ro">&#128231; contact@summer-smile.ro</a>
                <a href="https://wa.me/40746954812" class="whatsapp-link" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor" width="16" height="16"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg> WhatsApp
                </a>
            </div>
            <div class="social-links">
                <a href="https://www.facebook.com/studiosummersmile" title="Facebook" target="_blank" class="facebook"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                <a href="https://www.instagram.com/cazare_mamaianord/" title="Instagram" target="_blank" class="instagram"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                <a href="https://www.tiktok.com/@cazareoradea" title="TikTok" class="tiktok" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg></a>
            </div>
        </div>
    </div>
    <header class="site-header">
        <nav>
            <div class="logo"><a href="index.html"><img src="IMAGINI/3.jpeg" alt="Summer Smile"></a></div>
            <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo $index_page; ?>"><?php echo $t["nav_home"]; ?></a></li>
                <li><a href="<?php echo $mamaia_page; ?>">Mamaia Nord</a></li>
                <li><a href="<?php echo $oradea_page; ?>">Oradea</a></li>
                <li><a href="<?php echo $curateanie_page; ?>"><?php echo $t["nav_cleaning"]; ?></a></li>
                <li><a href="property-manager.html" ><?php echo $t["nav_property"]; ?></a></li>
                <li><a href="contact.html" ><?php echo $t["nav_contact"]; ?></a></li>
            </ul>
        </nav>
    </header>
    <div class="main-content">
        <div class="page-header"><h1><?php echo $property["name"]; ?></h1><p><?php echo $t["online_booking"]; ?></p></div>
        
        <!-- Property Selector -->
        <div style="display:flex;gap:15px;justify-content:center;flex-wrap:wrap;margin-bottom:30px;">
            <?php foreach ($properties as $key => $prop): ?>
            <?php if (!empty($prop['closed']) && $key !== $selected_property) continue; ?>
            <a href="?property=<?php echo $key; ?>" style="background:<?php echo $key === $selected_property ? 'rgba(212,175,55,0.2)' : 'rgba(255,255,255,0.05)'; ?>;border:2px solid <?php echo $key === $selected_property ? '#d4af37' : 'rgba(212,175,55,0.2)'; ?>;border-radius:15px;padding:12px;text-align:center;width:180px;text-decoration:none;transition:all 0.3s;<?php echo !empty($prop['closed']) ? 'opacity:0.5;pointer-events:none;' : ''; ?>">
                <img src="<?php echo $prop['image']; ?>" alt="" style="width:100%;height:80px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
                <div style="font-size:0.85rem;color:#fff;"><?php echo explode(' - ', $prop['name'])[0]; ?></div>
                <?php if (!empty($prop['closed'])): ?><div style="font-size:0.75rem;color:#ff6b6b;margin-top:4px;">Indisponibil</div><?php endif; ?>
                <?php
                if (!empty($prop['summer_only'])):
                    $propLabel = (mamaiaHasPropertyOverride($key) && isset($MAMAIA_PROPERTY_OPEN_PERIODS[$key]))
                        ? 'Disponibil 20 iunie - 15 septembrie'
                        : 'Disponibil 20 iunie - 10 septembrie';
                ?><div style="font-size:0.7rem;color:#64b5f6;margin-top:4px;"><?php echo $propLabel; ?></div><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <form method="POST" action="" id="bookingForm">
            <input type="hidden" name="action" value="create_checkout">
            <input type="hidden" name="csrf_token" value="<?php echo generatePaymentCsrf(); ?>">
            <input type="hidden" name="checkin" id="checkinInput">
            <input type="hidden" name="checkout" id="checkoutInput">
            <input type="hidden" name="guests" id="guestsInput" value="2">
            <div class="booking-wrapper">
                <div class="calendar-section">
                    <?php if ($is_closed): ?>
                    <div class="alert alert-warning" style="text-align:center;padding:40px 20px;font-size:1.1rem;">
                        <strong>Aceasta proprietate este momentan indisponibila.</strong><br><br>
                        Va recomandam <a href="?property=summer-smile" style="color:#d4af37;text-decoration:underline;">Summer Smile - Studio Mamaia Nord</a>
                    </div>
                    <?php else: ?>
                    <?php if ($is_summer_only):
                        $seasonText = ($has_explicit_pricing && isset($MAMAIA_PROPERTY_OPEN_PERIODS[$selected_property]))
                            ? 'Aceasta proprietate este disponibila doar in sezon (20 iunie - 15 septembrie)'
                            : 'Aceasta proprietate este disponibila doar in sezon (20 iunie - 10 septembrie)';
                    ?><div style="background:rgba(100,181,246,0.1);border:1px solid rgba(100,181,246,0.3);border-radius:12px;padding:12px 20px;margin-bottom:15px;text-align:center;color:#64b5f6;font-size:0.9rem;"><?php echo $seasonText; ?></div><?php endif; ?>
                    <?php if ($cancelled): ?><div class="alert alert-warning" ><?php echo $t["booking_cancelled"]; ?></div><?php endif; ?>
                    <?php if (isset($error_message)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>
                    <div class="calendar-header"><h2 ><?php echo $t["select_stay"]; ?></h2><div class="cal-nav-buttons"><button type="button" class="cal-nav-btn" onclick="changeMonth(-1)">&#10094;</button><button type="button" class="cal-nav-btn" onclick="changeMonth(1)">&#10095;</button></div></div>
                    <div style="display:flex;justify-content:center;gap:10px;margin:15px 0;">
                        <select id="monthSelect" onchange="jumpToMonth()" style="padding:10px 15px;border-radius:10px;border:1px solid rgba(212,175,55,0.3);background:rgba(42,42,64,0.8);color:#fff;font-size:0.95rem;cursor:pointer;">
                            <option value="0">Ianuarie</option>
                            <option value="1">Februarie</option>
                            <option value="2">Martie</option>
                            <option value="3">Aprilie</option>
                            <option value="4">Mai</option>
                            <option value="5">Iunie</option>
                            <option value="6">Iulie</option>
                            <option value="7">August</option>
                            <option value="8">Septembrie</option>
                            <option value="9">Octombrie</option>
                            <option value="10">Noiembrie</option>
                            <option value="11">Decembrie</option>
                        </select>
                        <select id="yearSelect" onchange="jumpToMonth()" style="padding:10px 15px;border-radius:10px;border:1px solid rgba(212,175,55,0.3);background:rgba(42,42,64,0.8);color:#fff;font-size:0.95rem;cursor:pointer;">
                            <option value="2026">2026</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                            <option value="2031">2031</option>
                            <option value="2032">2032</option>
                            <option value="2033">2033</option>
                            <option value="2034">2034</option>
                            <option value="2035">2035</option>
                        </select>
                    </div>
                    <div class="month-display" id="monthDisplay"></div>
                    <div class="calendar-grid" id="calendarGrid"></div>
                    <div class="calendar-legend"><div class="legend-item"><div class="legend-dot available"></div> <span ><?php echo $t["available"]; ?></span></div><div class="legend-item"><div class="legend-dot blocked"></div> <span ><?php echo $t["blocked"]; ?></span></div></div>
                    <div class="divider"></div>
                    <div class="contact-form">
                        <h3 ><?php echo $t["your_data"]; ?></h3>
                        <div class="form-row"><div class="form-group"><label ><?php echo $t["full_name"]; ?></label><input type="text" name="name" id="nameInput" required placeholder="<?php echo $t["name_placeholder"]; ?>"></div></div>
                        <div class="form-row"><div class="form-group"><label ><?php echo $t["phone"]; ?></label><input type="tel" name="phone" id="phoneInput" required placeholder="<?php echo $t["phone_placeholder"]; ?>"></div><div class="form-group"><label ><?php echo $t["email"]; ?></label><input type="email" name="email" id="emailInput" required placeholder="<?php echo $t["email_placeholder"]; ?>"></div></div>
                    </div>
                </div>
                <div class="sidebar">
                    <div class="property-card"><img src="<?php echo $property["image"]; ?>" alt="<?php echo $property["name"]; ?>"><div class="property-card-info"><h3><?php echo $property["name"]; ?></h3><p>Bloc N10 by Alezzi, Str D10 nr 9, Mamaia Nord</p><?php if(isset($property['parking']) && $property['parking']): ?><span style="display:inline-block;background:#d4edda;color:#155724;padding:4px 10px;border-radius:12px;font-size:0.75rem;margin:8px 0;">&#127359; <?php echo $t['free_parking']; ?></span><?php endif; ?><div class="price-tag"><?php echo $pret_noapte; ?> RON <span ><?php echo $t["per_night"]; ?></span><?php if($property_type_for_price === 'apartament'): ?><br><small style="color:#90cdf4;font-size:0.7rem"><?php echo $t["apartment_2rooms"]; ?></small><?php endif; ?><?php if($pret_crescut): ?><br><small style="color:#ff6b6b;font-size:0.7rem">Cerere mare +10%</small><?php endif; ?><?php if($pret_scazut): ?><br><small style="color:#81c784;font-size:0.7rem">Oferta -10%</small><?php endif; ?></div></div></div>
                    <div class="booking-summary">
                        <h3 ><?php echo $t["your_booking"]; ?></h3>
                        <div class="date-display"><div class="date-box"><label ><?php echo $t["checkin"]; ?></label><span id="checkinDisplay" ><?php echo $t["select"]; ?></span></div><span class="date-arrow">&#8594;</span><div class="date-box"><label ><?php echo $t["checkout"]; ?></label><span id="checkoutDisplay" ><?php echo $t["select"]; ?></span></div></div>
                        <div class="guest-selector"><label ><?php echo $t["guests"]; ?></label><div class="guest-counter"><button type="button" onclick="changeGuests(-1)">-</button><span id="guestCount">2</span><button type="button" onclick="changeGuests(1)">+</button></div></div>
                        <div id="bookingNotice"></div>
                        <div class="price-breakdown" id="priceBreakdown" style="display:none">
                            <div class="price-row"><span><span id="pricePerNight"><?php echo $pret_noapte; ?></span> RON x <span id="nightsCount">0</span> <span ><?php echo $t["nights"]; ?></span></span><span id="subtotal">0 RON</span></div>
                            <div class="price-row total"><span ><?php echo $t["total_stay"]; ?></span><span id="totalPrice">0 RON</span></div>
                            <div class="price-row avans"><span ><?php echo $t["pay_now"]; ?></span><span id="avansPrice">0 RON</span></div>
                            <div class="price-row rest"><span ><?php echo $t["pay_checkin"]; ?></span><span id="restPrice">0 RON</span></div>
                        </div>
                        
                        <!-- Voucher Code Section -->
                        <div class="voucher-section" id="voucherSection" style="margin: 15px 0; padding: 15px; background: rgba(255,215,0,0.05); border-radius: 12px; border: 1px dashed rgba(255,215,0,0.3);">
                            <div style="display: flex; gap: 10px; align-items: flex-end;">
                                <div style="flex: 1;">
                                    <label style="display: block; font-size: 0.75rem; color: #d4af37; margin-bottom: 5px; text-transform: uppercase;">Cod Voucher / Cadou</label>
                                    <input type="text" id="voucherCode" name="voucher_code" placeholder="Ex: GIFT-XXXXXXXX" style="width: 100%; padding: 12px; border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; background: rgba(0,0,0,0.2); color: #fff; font-size: 0.95rem;">
                                </div>
                                <button type="button" id="applyVoucherBtn" onclick="applyVoucher()" style="padding: 12px 20px; background: linear-gradient(135deg, #d4af37, #b8962e); color: #1a1a2e; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Aplica</button>
                            </div>
                            <div id="voucherMessage" style="margin-top: 10px; font-size: 0.85rem; display: none;"></div>
                            <input type="hidden" id="voucherDiscount" name="voucher_discount" value="0">
                            <input type="hidden" id="voucherCodeApplied" name="voucher_code_applied" value="">
                        </div>
                        <button type="submit" class="btn-pay" id="payBtn" disabled><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg><span ><?php echo $t["reserve_now"]; ?></span></button>
                        <div class="payment-info"><strong ><?php echo $t["how_payment"]; ?></strong><span ><?php echo $t["payment_info"]; ?></span></div>
                        <div class="payment-info" style="background: rgba(156, 39, 176, 0.1); border-color: rgba(156, 39, 176, 0.2); margin-top: 10px;"><strong style="color: #9c27b0;" ><?php echo $t["cancel_policy"]; ?></strong><span style="color: #8892b0;" ><?php echo $t["cancel_info"]; ?></span></div>
                        <div class="stripe-badge"><svg width="14" height="14" fill="#5a5a7a" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg><span ><?php echo $t["secure_payment"]; ?></span></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        </form>
    </div>
    <script>
        document.getElementById('hamburger').addEventListener('click', function() { this.classList.toggle('active'); document.getElementById('navLinks').classList.toggle('active'); });
        const basePretNoapte = <?php echo $pret_noapte; ?>;
        const propertyType = '<?php echo $property_type_for_price ?? "studio"; ?>';
        const propertyKey = '<?php echo $selected_property; ?>';
        const propertyPrices = <?php echo getMamaiaPropertyPricesJSON($selected_property); ?>;
        const hasPropertyPrices = Object.keys(propertyPrices).length > 0;
        const apartmentExtra = (propertyType === 'apartament' && !hasPropertyPrices) ? 50 : 0;
        let currentPretNoapte = basePretNoapte;
        let currentDate=new Date(),currentMonth=currentDate.getMonth(),currentYear=currentDate.getFullYear(),checkinDate=null,checkoutDate=null,guestCount=2,blockedDates=[],bookingAllowed=false;
        // Get months and days from translations
        const currentLang = localStorage.getItem('selectedLang') || 'ro';
        const defaultMonths = '<?php echo $t["months"]; ?>'.split(',');
        const defaultDays = ['Lu','Ma','Mi','Jo','Vi','Sa','Du'];
        let months = defaultMonths;
        let days = defaultDays;
        if (typeof translations !== 'undefined' && translations[currentLang]) {
            const t = translations[currentLang];
            if (t.booking_months) months = t.booking_months.split(',');
            if (t.booking_days) days = t.booking_days.split(',');
        }
        // Mamaia Nord blocked dates - sincronizat cu Mobile Calendar si Booking
        let mamaiaBlockedDates = <?php echo getMamaiaBlockedDatesJSON($selected_property); ?>; // Default blocked
        // Daca proprietatea are open-period propriu (in mamaia-nord-pricing.php), se folosesc DOAR acelea — nu mai aplicam logica summer_only generica
        const hasPropertyOpenPeriod = <?php echo (isset($MAMAIA_PROPERTY_OPEN_PERIODS[$selected_property]) ? 'true' : 'false'); ?>;
        const isSummerOnly = <?php echo $is_summer_only ? 'true' : 'false'; ?> && !hasPropertyOpenPeriod;
        // Pentru proprietatile summer_only, blocam tot ce e inainte de 20 iunie si dupa 10 septembrie
        if (isSummerOnly) {
            const yr = currentDate.getFullYear();
            const summerStart = new Date(yr, 5, 20); // 20 iunie
            const summerEnd = new Date(yr, 8, 10); // 10 septembrie
            // Blocheaza inainte de 20 iunie
            if (currentDate < summerStart) {
                let d = new Date(currentDate);
                while (d < summerStart) {
                    mamaiaBlockedDates.push(d.toISOString().split('T')[0]);
                    d.setDate(d.getDate() + 1);
                }
            }
            // Blocheaza dupa 10 septembrie
            let d2 = new Date(summerEnd);
            d2.setDate(d2.getDate() + 1);
            const yearEnd = new Date(yr, 11, 31);
            while (d2 <= yearEnd) {
                mamaiaBlockedDates.push(d2.toISOString().split('T')[0]);
                d2.setDate(d2.getDate() + 1);
            }
        }
        blockedDates = mamaiaBlockedDates;
        
        function jumpToMonth(){currentMonth=parseInt(document.getElementById('monthSelect').value);currentYear=parseInt(document.getElementById('yearSelect').value);renderCalendar();}
        
        async function updateDisplay() {
            const checkinEl = document.getElementById('checkinDisplay');
            const checkoutEl = document.getElementById('checkoutDisplay');
            const priceBreakdown = document.getElementById('priceBreakdown');
            const payBtn = document.getElementById('payBtn');
            const checkinInput = document.getElementById('checkinInput');
            const checkoutInput = document.getElementById('checkoutInput');
            const bookingNotice = document.getElementById('bookingNotice');
            
            if (checkinDate) {
                checkinEl.textContent = formatDate(checkinDate);
                checkinInput.value = formatDateISO(checkinDate);
            } else {
                checkinEl.textContent = getTranslation('booking_select');
                checkinInput.value = '';
            }
            
            if (checkoutDate) {
                checkoutEl.textContent = formatDate(checkoutDate);
                checkoutInput.value = formatDateISO(checkoutDate);
            } else {
                checkoutEl.textContent = getTranslation('booking_select');
                checkoutInput.value = '';
            }
            
            if (checkinDate && checkoutDate) {
                const result = await checkBookingValidity(formatDateISO(checkinDate), formatDateISO(checkoutDate));
                
                if (result.allowed) {
                    document.getElementById('nightsCount').textContent = result.nights;
                    document.getElementById('pricePerNight').textContent = result.price_per_night;
                    document.getElementById('subtotal').textContent = result.subtotal + ' RON';
                    document.getElementById('totalPrice').textContent = result.total + ' RON';
                    document.getElementById('avansPrice').textContent = result.avans + ' RON';
                    document.getElementById('restPrice').textContent = result.rest + ' RON';
                    priceBreakdown.style.display = 'block';
                    payBtn.disabled = false;
                    bookingAllowed = true;
                    bookingNotice.innerHTML = '';
                } else {
                    priceBreakdown.style.display = 'none';
                    payBtn.disabled = true;
                    bookingAllowed = false;
                    bookingNotice.innerHTML = '<div class="min-nights-notice">' + result.message + '</div>';
                }
            } else {
                priceBreakdown.style.display = 'none';
                payBtn.disabled = true;
                bookingAllowed = false;
                bookingNotice.innerHTML = '';
            }
        }
        
        async function loadBlockedDates(){ 
            try {
                const response = await fetch('sync-mamaia-calendars.php?property=<?php echo $selected_property; ?>');
                const data = await response.json();
                if (data.success && data.data.blocked_dates) {
                    // Combina datele blocate statice cu cele din calendare externe
                    blockedDates = [...new Set([...mamaiaBlockedDates, ...data.data.blocked_dates])];
                }
            } catch(e) {
                console.log('Folosesc date blocate locale');
            }
            renderCalendar(); 
        }
        function isDateBlocked(date){return blockedDates.includes(date.toISOString().split('T')[0])}
        function hasBlockedDatesInRange(start,end){let c=new Date(start);while(c<end){if(isDateBlocked(c))return true;c.setDate(c.getDate()+1)}return false}
        function renderCalendar(){const md=document.getElementById('monthDisplay'),cg=document.getElementById('calendarGrid');md.textContent=months[currentMonth]+' '+currentYear;document.getElementById('monthSelect').value=currentMonth;document.getElementById('yearSelect').value=currentYear;let h=days.map(d=>'<div class="day-header">'+d+'</div>').join('');const fd=new Date(currentYear,currentMonth,1).getDay(),dim=new Date(currentYear,currentMonth+1,0).getDate(),today=new Date();today.setHours(0,0,0,0);const sd=fd===0?6:fd-1;for(let i=0;i<sd;i++)h+='<div class="day-cell empty"></div>';for(let day=1;day<=dim;day++){const date=new Date(currentYear,currentMonth,day),dis=date<today,tod=date.getTime()===today.getTime(),blk=isDateBlocked(date);let cls='day-cell';if(dis)cls+=' disabled';if(blk)cls+=' blocked';if(tod)cls+=' today';if(checkinDate&&checkoutDate){if(date.getTime()===checkinDate.getTime())cls+=' range-start';else if(date.getTime()===checkoutDate.getTime())cls+=' range-end';else if(date>checkinDate&&date<checkoutDate)cls+=' in-range'}else if(checkinDate&&date.getTime()===checkinDate.getTime())cls+=' selected';h+='<div class="'+cls+'" onclick="selectDate('+day+')">'+day+'</div>'}cg.innerHTML=h}
        function selectDate(day){const date=new Date(currentYear,currentMonth,day),today=new Date();today.setHours(0,0,0,0);if(date<today)return;if(isDateBlocked(date)){alert(getTranslation('booking_date_unavailable'));return}if(!checkinDate||(checkinDate&&checkoutDate)){checkinDate=date;checkoutDate=null}else if(date>checkinDate){if(hasBlockedDatesInRange(checkinDate,date)){alert(getTranslation('booking_period_blocked'));checkinDate=null;checkoutDate=null}else{checkoutDate=date}}else{checkinDate=date;checkoutDate=null}updateDisplay();renderCalendar()}
        async function checkBookingValidity(checkin,checkout){
            const start = new Date(checkin);
            const end = new Date(checkout);
            const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            
            if (nights < 2) {
                return {allowed: false, message: 'Perioada minima de rezervare este de 2 nopti.'};
            }
            
            // Seasonal prices + apartment extra (sau preturi proprii daca proprietatea le defineste)
            function getPriceForDate(date) {
                const dateStr = date.toISOString().split('T')[0];
                if (hasPropertyPrices) {
                    // Preturi explicite per zi din mamaia-nord-pricing.php (apartmentExtra = 0 in acest caz)
                    return propertyPrices[dateStr] || 0;
                }
                const y = date.getFullYear();
                const m = date.getMonth() + 1;
                const d = date.getDate();
                let price = 350;
                // Pre-sezon 2026
                if (y === 2026) {
                    if ((m === 3 && d >= 16) || (m === 4 && d <= 10)) price = 150;
                    else if (m === 4 && d >= 11 && d <= 15) price = 200;
                    else if (m === 4 && d >= 16 && d <= 28) price = 150;
                    else if ((m === 4 && d >= 29) || (m === 5 && d <= 4)) price = 275;
                    else if (m === 5 && d >= 5 && d <= 31) price = 250;
                    else if (m === 6 && d >= 1 && d <= 20) price = 300;
                    else if ((m === 6 && d >= 21) || (m === 7 && d <= 10)) price = 350;
                    else if (m === 7 && d >= 11 && d <= 20) price = 400;
                    else if ((m === 7 && d >= 21) || m === 8 || (m === 9 && d <= 10)) price = 450;
                } else {
                    if ((m === 6 && d >= 20) || (m === 7 && d <= 10)) price = 350;
                    else if (m === 7 && d >= 11 && d <= 20) price = 400;
                    else if ((m === 7 && d >= 21) || m === 8 || (m === 9 && d <= 10)) price = 450;
                }
                return price + apartmentExtra;
            }
            
            let total = 0;
            let current = new Date(start);
            while (current < end) {
                total += getPriceForDate(current);
                current.setDate(current.getDate() + 1);
            }
            
            const avgPrice = Math.round(total / nights);
            const avans = Math.ceil(total * 0.30);
            const rest = total - avans;
            
            return {
                allowed: true, 
                nights: nights,
                price_per_night: avgPrice,
                subtotal: total,
                total: total,
                avans: avans,
                rest: rest
            };
        }
        function formatDate(d){return d.getDate()+' '+months[d.getMonth()].substring(0,3)}
        function formatDateISO(d){return d.toISOString().split('T')[0]}
        function changeMonth(delta){currentMonth+=delta;if(currentMonth>11){currentMonth=0;currentYear++}else if(currentMonth<0){currentMonth=11;currentYear--}renderCalendar()}
        function changeGuests(delta){guestCount=Math.max(1,Math.min(4,guestCount+delta));document.getElementById('guestCount').textContent=guestCount;document.getElementById('guestsInput').value=guestCount}
        document.getElementById('bookingForm').addEventListener('submit',function(e){if(!bookingAllowed){e.preventDefault();alert(getTranslation('booking_select_valid'))}});
        // Translation helper
        function getTranslation(key) {
            const lang = localStorage.getItem('selectedLang') || 'ro';
            if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
                return translations[lang][key];
            }
            const defaults = {
                booking_date_unavailable: 'Aceasta data nu este disponibila.',
                booking_period_blocked: 'Perioada contine date indisponibile.',
                booking_select_valid: 'Selectati o perioada valida de rezervare.',
                booking_select: 'Selecteaza'
            };
            return defaults[key] || key;
        }
        // Apply translations to placeholders
        document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
            const key = el.getAttribute('data-translate-placeholder');
            const text = getTranslation(key);
            if (text && text !== key) el.placeholder = text;
        });
        loadBlockedDates();renderCalendar();
    </script>
<script src="cookie-consent.js"></script>
<script>
        // Voucher validation
        let appliedVoucherDiscount = 0;
        
        async function applyVoucher() {
            const code = document.getElementById('voucherCode').value.trim();
            const msgDiv = document.getElementById('voucherMessage');
            const btn = document.getElementById('applyVoucherBtn');
            
            if (!code) {
                msgDiv.innerHTML = '<span style="color: #ff6b6b;">Introdu un cod voucher</span>';
                msgDiv.style.display = 'block';
                return;
            }
            
            btn.disabled = true;
            btn.textContent = '...';
            
            try {
                const totalEl = document.getElementById('totalPrice');
                const total = parseFloat(totalEl?.textContent?.replace(/[^0-9.]/g, '') || 0);
                
                const response = await fetch('/api/validate-voucher.php?code=' + encodeURIComponent(code) + '&amount=' + total);
                const data = await response.json();
                
                if (data.success && data.valid) {
                    appliedVoucherDiscount = data.discount_amount;
                    document.getElementById('voucherDiscount').value = appliedVoucherDiscount;
                    document.getElementById('voucherCodeApplied').value = code;
                    
                    msgDiv.innerHTML = '<span style="color: #81c784;">✓ ' + data.message + '</span>';
                    msgDiv.style.display = 'block';
                    
                    // Update display
                    updatePriceWithVoucher();
                    
                    btn.textContent = '✓';
                    btn.style.background = '#4CAF50';
                    document.getElementById('voucherCode').disabled = true;
                } else {
                    msgDiv.innerHTML = '<span style="color: #ff6b6b;">✗ ' + data.message + '</span>';
                    msgDiv.style.display = 'block';
                    btn.textContent = 'Aplica';
                    btn.disabled = false;
                }
            } catch (err) {
                msgDiv.innerHTML = '<span style="color: #ff6b6b;">Eroare la validare</span>';
                msgDiv.style.display = 'block';
                btn.textContent = 'Aplica';
                btn.disabled = false;
            }
        }
        
        function updatePriceWithVoucher() {
            if (appliedVoucherDiscount > 0) {
                const totalEl = document.getElementById('totalPrice');
                const avansEl = document.getElementById('avansPrice');
                const restEl = document.getElementById('restPrice');
                
                let total = parseFloat(totalEl.textContent.replace(/[^0-9.]/g, '') || 0);
                total = Math.max(0, total - appliedVoucherDiscount);
                
                const avans = Math.round(total * 0.3);
                const rest = total - avans;
                
                // Add voucher discount row if not exists
                let voucherRow = document.getElementById('voucherDiscountRow');
                if (!voucherRow) {
                    const priceBreakdown = document.getElementById('priceBreakdown');
                    const totalRow = priceBreakdown.querySelector('.price-row.total');
                    voucherRow = document.createElement('div');
                    voucherRow.id = 'voucherDiscountRow';
                    voucherRow.className = 'price-row';
                    voucherRow.style.color = '#81c784';
                    totalRow.parentNode.insertBefore(voucherRow, totalRow);
                }
                voucherRow.innerHTML = '<span>Voucher cadou</span><span>-' + appliedVoucherDiscount + ' RON</span>';
                
                totalEl.textContent = total + ' RON';
                avansEl.textContent = avans + ' RON';
                restEl.textContent = rest + ' RON';
            }
        }

    </script>
</body>
</html>
