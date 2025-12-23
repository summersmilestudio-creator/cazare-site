// Sistem de traduceri - RO/EN/HU
const translations = {
    ro: {
        // Navigare
        nav_home: "Acasă",
        nav_oradea: "Oradea",
        nav_mamaia: "Mamaia Nord",
        nav_cleaning: "Curățenie",
        nav_property: "Property Manager",
        nav_contact: "Contact",

        // Banner principal
        banner_oradea: "Oradea",
        banner_oradea_desc: "Arhitectură & Cultură",
        banner_mamaia: "Mamaia Nord",
        banner_mamaia_desc: "Lux & Relaxare",
        banner_explore: "← Explorează fiecare destinație pentru detalii exclusive →",

        // Despre
        about_title: "Despre Noi",
        about_text: "Vă oferim cazare de cea mai înaltă calitate atât în Oradea cât și în Mamaia Nord. Cu apartamente moderne, dotări complete și locații excelente, vă garantăm o experiență de neuitat.",

        // Oradea Section
        oradea_title: "Cazare în Oradea",
        oradea_subtitle: "Confort urban în inima Oradei",
        oradea_banner_desc: "Confort urban în inima Oradei - Arhitectură & Cultură",
        oradea_view_btn: "Vezi Cazările",

        // Mamaia Section
        mamaia_title: "Cazare în Mamaia Nord",
        mamaia_subtitle: "Relaxare la malul mării",
        mamaia_banner_desc: "Relaxare la malul mării - Lux & Relaxare",
        mamaia_view_btn: "Vezi Cazările",

        // Studio New York
        studio_ny_name: "Studio New York",
        studio_ny_location: "Oradea, România",
        studio_ny_desc: "Studio modern și elegant în Oradea, perfect pentru sejururi de afaceri sau turistice. Complet echipat cu toate facilitățile necesare pentru un sejur confortabil.",

        // Facilități comune
        facility_studio: "Studio complet echipat",
        facility_kitchen: "Bucătărie modernă",
        facility_bathroom: "Baie privată",
        facility_ac: "Aer condiționat",
        facility_wifi: "WiFi ultra-rapid",
        facility_parking: "Parcare GRATUITĂ",
        facility_tv: "Smart TV",
        facility_linen: "Lenjerie premium",
        facility_2rooms: "2 camere spațioase",
        facility_sea_view: "Vedere panoramică la mare",
        facility_full_kitchen: "Bucătărie completă",
        facility_wifi_free: "WiFi gratuit",
        facility_balcony: "Balcon mare",
        facility_beach_access: "Acces direct la plajă",
        facility_kitchenette: "Kitchenette funcțională",
        facility_modern_bath: "Baie modernă",
        facility_free_parking: "Parcare gratuită",
        facility_near_shops: "Aproape de magazine",
        facility_2big_rooms: "2 camere mari",
        facility_large_living: "Living generos",
        facility_quiet_area: "Zonă liniștită",
        facility_equipped_kitchen: "Bucătărie complet echipată",
        facility_spacious_living: "Living spațios",
        facility_parking_available: "Parcare disponibilă",
        facility_near_beach_shops: "Aproape de plajă și magazine",

        // Apartamente Mamaia
        apt_sea_name: "Apartament 2 Camere - La Mare",
        apt_sea_location: "Mamaia Nord - Prima Linie la Mare",
        apt_sea_desc: "Apartament modern cu 2 camere, vedere spectaculoasă la mare, în prima linie. Perfect pentru familii sau grupuri mici.",
        apt_sea_placeholder: "Prima Linie La Mare",

        apt_lidl_studio_name: "Studio - Lângă Lidl",
        apt_lidl_studio_location: "Mamaia Nord - Lângă Lidl",
        apt_lidl_studio_desc: "Studio modern și compact, ideal pentru cupluri. Locație excelentă lângă Lidl, cu acces facil la toate facilitățile.",
        apt_lidl_studio_placeholder: "Studio Modern",

        apt_lidl_2room_name: "Apartament 2 Camere - Lângă Lidl",
        apt_lidl_2room_location: "Mamaia Nord - Lângă Lidl",
        apt_lidl_2room_desc: "Apartament spațios cu 2 camere, în apropierea Lidl. Excelent pentru familii, cu toate facilitățile necesare.",
        apt_lidl_2room_placeholder: "Apartament Spațios",

        // Prețuri și butoane
        price_night: "Lei / noapte",
        check_availability: "Verifică disponibilitatea",
        book_now: "Rezervă Acum",

        // Servicii
        services_title: "Servicii de Curățenie & Property Manager",
        services_subtitle: "Gestionăm proprietatea ta cu profesionalism",

        cleaning_title: "Servicii de Curățenie Profesionale",
        cleaning_desc: "Oferim servicii complete de curățenie pentru proprietățile tale, asigurând standarde înalte de igienă și confort pentru oaspeți.",
        cleaning_checkout: "Curățenie după fiecare check-out",
        cleaning_deep: "Curățenie deep cleaning periodic",
        cleaning_linen: "Schimbare lenjerie și prosoape",
        cleaning_supplies: "Verificare și completare consumabile",
        cleaning_sanitize: "Igienizare și dezinfectare",
        cleaning_windows: "Curățare ferestre și terase",
        cleaning_price: "De la 150 Lei / curățenie",

        pm_title: "Property Management Complet",
        pm_desc: "Gestionăm proprietatea ta de la A la Z, astfel încât tu să te bucuri doar de venituri fără griji.",
        pm_reservations: "Gestionare rezervări pe multiple platforme",
        pm_checkin: "Check-in / Check-out oaspeți",
        pm_communication: "Comunicare cu oaspeții 24/7",
        pm_cleaning: "Curățenie și mentenanță regulată",
        pm_financial: "Gestionare financiară și raportare",
        pm_pricing: "Optimizare prețuri și ocupare",
        pm_marketing: "Marketing și promovare proprietate",
        pm_maintenance: "Întreținere și reparații minore",
        pm_price: "15-20% din încasări / lună",
        pm_request: "Solicită Ofertă",

        extra_title: "Servicii Suplimentare",
        extra_desc: "Servicii opționale pentru a îmbunătăți experiența oaspeților și a maximiza veniturile tale.",
        extra_transfer: "Transfer aeroport / gară",
        extra_fridge: "Aprovizionare frigider la cerere",
        extra_concierge: "Concierge services",
        extra_reservations: "Rezervări restaurante / activități",
        extra_photo: "Fotografie profesională proprietate",
        extra_tech: "Asistență tehnică și IT",
        extra_price: "Prețuri variabile",

        services_cta_title: "Interested în serviciile noastre?",
        services_cta_text: "Contactează-ne pentru o ofertă personalizată și să discutăm despre nevoile tale specifice.",
        services_cta_btn: "Contactează-ne Acum",

        // Formular rezervare
        form_title: "Rezervă Cazarea Ta",
        form_name: "Nume Complet *",
        form_email: "Email *",
        form_phone: "Telefon *",
        form_accommodation: "Alege Cazarea *",
        form_select: "Selectează cazarea",
        form_checkin: "Check-in *",
        form_checkout: "Check-out *",
        form_guests: "Număr Persoane *",
        form_total: "Total Estimat",
        form_notes: "Observații / Cerințe Speciale",
        form_notes_placeholder: "Menționați orice cerințe speciale...",
        form_submit: "Trimite Rezervarea",
        form_success_title: "Rezervare Trimisă cu Succes!",
        form_success_text: "Vă mulțumim pentru rezervare. Veți fi contactat în curând pentru confirmare.",
        form_close: "Închide",

        // Contact
        contact_title: "Contact",
        contact_email_title: "Email General",
        contact_response: "Răspundem în 24h",

        // Footer
        footer_text: "Cazare Premium - Oradea & Mamaia Nord. Toate drepturile rezervate.",

        // Pagini separate
        page_oradea_title: "Cazare în Oradea - Cazare Premium",
        page_mamaia_title: "Cazare în Mamaia Nord - Cazare Premium",
        page_cleaning_title: "Servicii de Curățenie - Cazare Premium",
        page_pm_title: "Property Manager - Cazare Premium",

        // CTA alternate
        cta_sea_title: "Cauți cazare la mare?",
        cta_sea_text: "Descoperă apartamentele noastre premium în Mamaia Nord - direct la malul mării",
        cta_sea_btn: "Vezi Cazări în Mamaia Nord",
        cta_city_title: "Explorezi și orașul?",
        cta_city_text: "Descoperă studio-ul nostru premium în Oradea - confort urban în inima Transilvaniei",
        cta_city_btn: "Vezi Cazări în Oradea",

        // Curățenie page
        cleaning_hero_title: "Servicii Profesionale de Curățenie",
        cleaning_hero_subtitle: "Menținem spațiile tale impecabile pentru oaspeți mulțumiți",
        cleaning_page_title: "Servicii de Curățenie pentru Cazare",

        cleaning_checkout_title: "Curățenie După Check-Out",
        cleaning_checkout_desc: "Curățenie completă și profesională după plecarea oaspeților, pregătim spațiul pentru următorii vizitatori.",
        cleaning_vacuum: "Aspirare și spălare pardoseli",
        cleaning_bathroom: "Curățenie baie complet",
        cleaning_bedding: "Schimbare lenjerie de pat",
        cleaning_disinfect: "Dezinfectare suprafețe",

        cleaning_deep_title: "Curățenie Profundă",
        cleaning_deep_desc: "Servicii complete de curățenie profundă pentru menținerea standardelor înalte.",
        cleaning_windows_deep: "Curățare ferestre",
        cleaning_furniture: "Curățenie mobilier",
        cleaning_kitchen_deep: "Dezinfectare bucătărie",
        cleaning_carpets: "Curățare covoare și textile",

        cleaning_daily_title: "Mentenanță Zilnică",
        cleaning_daily_desc: "Servicii regulate de menținere a curățeniei pentru sejururi lungi.",
        cleaning_daily_room: "Curățenie zilnică camera",
        cleaning_refill: "Reumplere produse igienă",
        cleaning_towels: "Înlocuire prosoape",
        cleaning_check: "Verificare stare generală",

        cleaning_cta_title: "Solicită Servicii de Curățenie",
        cleaning_cta_text: "Contactează-ne pentru o ofertă personalizată",
        cleaning_cta_btn: "Contactează-ne",

        // Property Manager page
        pm_hero_title: "Servicii Property Management",
        pm_hero_subtitle: "Gestionăm proprietatea ta cu profesionalism și dedicare",
        pm_page_title: "Servicii Complete de Property Management",

        pm_full_title: "Gestionare Completă",
        pm_full_desc: "Ne ocupăm de toate aspectele proprietății tale, de la A la Z.",
        pm_calendar: "Gestionare rezervări și calendar",
        pm_guest_checkin: "Check-in / Check-out oaspeți",
        pm_guest_comm: "Comunicare cu oaspeții",
        pm_price_opt: "Optimizare prețuri și ocupare",

        pm_maint_title: "Mentenanță și Reparații",
        pm_maint_desc: "Menținem proprietatea în stare impecabilă.",
        pm_inspections: "Inspecții regulate",
        pm_repairs_coord: "Coordonare reparații",
        pm_preventive: "Mentenanță preventivă",
        pm_urgent: "Intervenții urgente 24/7",

        pm_reports_title: "Rapoarte și Financiar",
        pm_reports_desc: "Transparență totală asupra performanței proprietății.",
        pm_monthly: "Rapoarte lunare detaliate",
        pm_income: "Urmărire venituri/cheltuieli",
        pm_tax: "Optimizare fiscală",
        pm_dashboard: "Dashboard online 24/7",

        pm_marketing_title: "Marketing și Promovare",
        pm_marketing_desc: "Maximizăm vizibilitatea și rezervările proprietății tale.",
        pm_photos: "Fotografii profesionale",
        pm_platforms: "Listare pe platforme majore",
        pm_seo: "Optimizare SEO și descrieri",
        pm_reviews: "Gestionare recenzii",

        pm_security_title: "Asigurări și Securitate",
        pm_security_desc: "Protejăm investiția ta împotriva riscurilor.",
        pm_guest_check: "Verificare oaspeți",
        pm_insurance: "Asigurare pentru daune",
        pm_legal: "Contract și termeni legali",
        pm_alarms: "Securitate și alarme",

        pm_vip_title: "Servicii VIP",
        pm_vip_desc: "Servicii premium pentru proprietari exigenți.",
        pm_concierge: "Concierge pentru oaspeți",
        pm_design: "Amenajare și design interior",
        pm_extra_services: "Servicii suplimentare (transfer, etc)",
        pm_dedicated: "Manager dedicat",

        pm_cta_title: "Lasă-ne pe Noi Grija Proprietății Tale",
        pm_cta_text: "Contactează-ne pentru o consultație gratuită",
        pm_cta_btn: "Solicită Consultație"
    },

    en: {
        // Navigation
        nav_home: "Home",
        nav_oradea: "Oradea",
        nav_mamaia: "Mamaia Nord",
        nav_cleaning: "Cleaning",
        nav_property: "Property Manager",
        nav_contact: "Contact",

        // Main banner
        banner_oradea: "Oradea",
        banner_oradea_desc: "Architecture & Culture",
        banner_mamaia: "Mamaia Nord",
        banner_mamaia_desc: "Luxury & Relaxation",
        banner_explore: "← Explore each destination for exclusive details →",

        // About
        about_title: "About Us",
        about_text: "We offer the highest quality accommodation in both Oradea and Mamaia Nord. With modern apartments, complete amenities and excellent locations, we guarantee an unforgettable experience.",

        // Oradea Section
        oradea_title: "Accommodation in Oradea",
        oradea_subtitle: "Urban comfort in the heart of Oradea",
        oradea_banner_desc: "Urban comfort in the heart of Oradea - Architecture & Culture",
        oradea_view_btn: "View Accommodations",

        // Mamaia Section
        mamaia_title: "Accommodation in Mamaia Nord",
        mamaia_subtitle: "Relaxation by the sea",
        mamaia_banner_desc: "Relaxation by the sea - Luxury & Relaxation",
        mamaia_view_btn: "View Accommodations",

        // Studio New York
        studio_ny_name: "Studio New York",
        studio_ny_location: "Oradea, Romania",
        studio_ny_desc: "Modern and elegant studio in Oradea, perfect for business or tourist stays. Fully equipped with all the necessary facilities for a comfortable stay.",

        // Common facilities
        facility_studio: "Fully equipped studio",
        facility_kitchen: "Modern kitchen",
        facility_bathroom: "Private bathroom",
        facility_ac: "Air conditioning",
        facility_wifi: "Ultra-fast WiFi",
        facility_parking: "FREE Parking",
        facility_tv: "Smart TV",
        facility_linen: "Premium linens",
        facility_2rooms: "2 spacious rooms",
        facility_sea_view: "Panoramic sea view",
        facility_full_kitchen: "Full kitchen",
        facility_wifi_free: "Free WiFi",
        facility_balcony: "Large balcony",
        facility_beach_access: "Direct beach access",
        facility_kitchenette: "Functional kitchenette",
        facility_modern_bath: "Modern bathroom",
        facility_free_parking: "Free parking",
        facility_near_shops: "Near shops",
        facility_2big_rooms: "2 large rooms",
        facility_large_living: "Generous living room",
        facility_quiet_area: "Quiet area",
        facility_equipped_kitchen: "Fully equipped kitchen",
        facility_spacious_living: "Spacious living room",
        facility_parking_available: "Parking available",
        facility_near_beach_shops: "Near beach and shops",

        // Mamaia apartments
        apt_sea_name: "2 Bedroom Apartment - Beachfront",
        apt_sea_location: "Mamaia Nord - First Line to the Sea",
        apt_sea_desc: "Modern 2-bedroom apartment with spectacular sea views, on the first line. Perfect for families or small groups.",
        apt_sea_placeholder: "First Line to Sea",

        apt_lidl_studio_name: "Studio - Near Lidl",
        apt_lidl_studio_location: "Mamaia Nord - Near Lidl",
        apt_lidl_studio_desc: "Modern and compact studio, ideal for couples. Excellent location near Lidl, with easy access to all amenities.",
        apt_lidl_studio_placeholder: "Modern Studio",

        apt_lidl_2room_name: "2 Bedroom Apartment - Near Lidl",
        apt_lidl_2room_location: "Mamaia Nord - Near Lidl",
        apt_lidl_2room_desc: "Spacious 2-bedroom apartment near Lidl. Excellent for families, with all necessary amenities.",
        apt_lidl_2room_placeholder: "Spacious Apartment",

        // Prices and buttons
        price_night: "Lei / night",
        check_availability: "Check availability",
        book_now: "Book Now",

        // Services
        services_title: "Cleaning Services & Property Manager",
        services_subtitle: "We manage your property with professionalism",

        cleaning_title: "Professional Cleaning Services",
        cleaning_desc: "We offer complete cleaning services for your properties, ensuring high standards of hygiene and comfort for guests.",
        cleaning_checkout: "Cleaning after each check-out",
        cleaning_deep: "Periodic deep cleaning",
        cleaning_linen: "Linen and towel change",
        cleaning_supplies: "Check and replenish supplies",
        cleaning_sanitize: "Sanitization and disinfection",
        cleaning_windows: "Window and terrace cleaning",
        cleaning_price: "From 150 Lei / cleaning",

        pm_title: "Complete Property Management",
        pm_desc: "We manage your property from A to Z, so you can just enjoy the income without worries.",
        pm_reservations: "Reservation management on multiple platforms",
        pm_checkin: "Guest check-in / check-out",
        pm_communication: "Guest communication 24/7",
        pm_cleaning: "Regular cleaning and maintenance",
        pm_financial: "Financial management and reporting",
        pm_pricing: "Price and occupancy optimization",
        pm_marketing: "Property marketing and promotion",
        pm_maintenance: "Maintenance and minor repairs",
        pm_price: "15-20% of revenue / month",
        pm_request: "Request Quote",

        extra_title: "Additional Services",
        extra_desc: "Optional services to enhance guest experience and maximize your revenue.",
        extra_transfer: "Airport / train station transfer",
        extra_fridge: "Fridge stocking on request",
        extra_concierge: "Concierge services",
        extra_reservations: "Restaurant / activity reservations",
        extra_photo: "Professional property photography",
        extra_tech: "Technical and IT assistance",
        extra_price: "Variable prices",

        services_cta_title: "Interested in our services?",
        services_cta_text: "Contact us for a personalized offer and let's discuss your specific needs.",
        services_cta_btn: "Contact Us Now",

        // Booking form
        form_title: "Book Your Accommodation",
        form_name: "Full Name *",
        form_email: "Email *",
        form_phone: "Phone *",
        form_accommodation: "Choose Accommodation *",
        form_select: "Select accommodation",
        form_checkin: "Check-in *",
        form_checkout: "Check-out *",
        form_guests: "Number of Guests *",
        form_total: "Estimated Total",
        form_notes: "Notes / Special Requests",
        form_notes_placeholder: "Mention any special requests...",
        form_submit: "Submit Booking",
        form_success_title: "Booking Submitted Successfully!",
        form_success_text: "Thank you for your booking. You will be contacted soon for confirmation.",
        form_close: "Close",

        // Contact
        contact_title: "Contact",
        contact_email_title: "General Email",
        contact_response: "We respond within 24h",

        // Footer
        footer_text: "Cazare Premium - Oradea & Mamaia Nord. All rights reserved.",

        // Separate pages
        page_oradea_title: "Accommodation in Oradea - Cazare Premium",
        page_mamaia_title: "Accommodation in Mamaia Nord - Cazare Premium",
        page_cleaning_title: "Cleaning Services - Cazare Premium",
        page_pm_title: "Property Manager - Cazare Premium",

        // Alternate CTA
        cta_sea_title: "Looking for seaside accommodation?",
        cta_sea_text: "Discover our premium apartments in Mamaia Nord - right by the sea",
        cta_sea_btn: "View Mamaia Nord Accommodations",
        cta_city_title: "Also exploring the city?",
        cta_city_text: "Discover our premium studio in Oradea - urban comfort in the heart of Transylvania",
        cta_city_btn: "View Oradea Accommodations",

        // Cleaning page
        cleaning_hero_title: "Professional Cleaning Services",
        cleaning_hero_subtitle: "We keep your spaces spotless for satisfied guests",
        cleaning_page_title: "Cleaning Services for Accommodation",

        cleaning_checkout_title: "Post Check-Out Cleaning",
        cleaning_checkout_desc: "Complete and professional cleaning after guests leave, preparing the space for the next visitors.",
        cleaning_vacuum: "Vacuuming and floor washing",
        cleaning_bathroom: "Complete bathroom cleaning",
        cleaning_bedding: "Bed linen change",
        cleaning_disinfect: "Surface disinfection",

        cleaning_deep_title: "Deep Cleaning",
        cleaning_deep_desc: "Complete deep cleaning services to maintain high standards.",
        cleaning_windows_deep: "Window cleaning",
        cleaning_furniture: "Furniture cleaning",
        cleaning_kitchen_deep: "Kitchen disinfection",
        cleaning_carpets: "Carpet and textile cleaning",

        cleaning_daily_title: "Daily Maintenance",
        cleaning_daily_desc: "Regular cleaning maintenance services for long stays.",
        cleaning_daily_room: "Daily room cleaning",
        cleaning_refill: "Hygiene product refill",
        cleaning_towels: "Towel replacement",
        cleaning_check: "General condition check",

        cleaning_cta_title: "Request Cleaning Services",
        cleaning_cta_text: "Contact us for a personalized quote",
        cleaning_cta_btn: "Contact Us",

        // Property Manager page
        pm_hero_title: "Property Management Services",
        pm_hero_subtitle: "We manage your property with professionalism and dedication",
        pm_page_title: "Complete Property Management Services",

        pm_full_title: "Complete Management",
        pm_full_desc: "We take care of all aspects of your property, from A to Z.",
        pm_calendar: "Reservation and calendar management",
        pm_guest_checkin: "Guest check-in / check-out",
        pm_guest_comm: "Guest communication",
        pm_price_opt: "Price and occupancy optimization",

        pm_maint_title: "Maintenance and Repairs",
        pm_maint_desc: "We keep the property in impeccable condition.",
        pm_inspections: "Regular inspections",
        pm_repairs_coord: "Repair coordination",
        pm_preventive: "Preventive maintenance",
        pm_urgent: "24/7 emergency interventions",

        pm_reports_title: "Reports and Financial",
        pm_reports_desc: "Total transparency on property performance.",
        pm_monthly: "Detailed monthly reports",
        pm_income: "Income/expense tracking",
        pm_tax: "Tax optimization",
        pm_dashboard: "Online dashboard 24/7",

        pm_marketing_title: "Marketing and Promotion",
        pm_marketing_desc: "We maximize the visibility and bookings of your property.",
        pm_photos: "Professional photos",
        pm_platforms: "Listing on major platforms",
        pm_seo: "SEO and description optimization",
        pm_reviews: "Review management",

        pm_security_title: "Insurance and Security",
        pm_security_desc: "We protect your investment against risks.",
        pm_guest_check: "Guest verification",
        pm_insurance: "Damage insurance",
        pm_legal: "Contract and legal terms",
        pm_alarms: "Security and alarms",

        pm_vip_title: "VIP Services",
        pm_vip_desc: "Premium services for demanding owners.",
        pm_concierge: "Guest concierge",
        pm_design: "Interior design and decoration",
        pm_extra_services: "Additional services (transfer, etc)",
        pm_dedicated: "Dedicated manager",

        pm_cta_title: "Let Us Take Care of Your Property",
        pm_cta_text: "Contact us for a free consultation",
        pm_cta_btn: "Request Consultation"
    },

    hu: {
        // Navigáció
        nav_home: "Főoldal",
        nav_oradea: "Nagyvárad",
        nav_mamaia: "Mamaia Nord",
        nav_cleaning: "Takarítás",
        nav_property: "Ingatlankezelő",
        nav_contact: "Kapcsolat",

        // Fő banner
        banner_oradea: "Nagyvárad",
        banner_oradea_desc: "Építészet & Kultúra",
        banner_mamaia: "Mamaia Nord",
        banner_mamaia_desc: "Luxus & Pihenés",
        banner_explore: "← Fedezze fel minden úti célt az exkluzív részletekért →",

        // Rólunk
        about_title: "Rólunk",
        about_text: "A legmagasabb minőségű szállást kínáljuk Nagyváradon és Mamaia Nordban egyaránt. Modern apartmanokkal, teljes felszereléssel és kiváló helyszínekkel felejthetetlen élményt garantálunk.",

        // Nagyvárad szekció
        oradea_title: "Szállás Nagyváradon",
        oradea_subtitle: "Városi kényelem Nagyvárad szívében",
        oradea_banner_desc: "Városi kényelem Nagyvárad szívében - Építészet & Kultúra",
        oradea_view_btn: "Szállások megtekintése",

        // Mamaia szekció
        mamaia_title: "Szállás Mamaia Nordban",
        mamaia_subtitle: "Pihenés a tenger mellett",
        mamaia_banner_desc: "Pihenés a tenger mellett - Luxus & Pihenés",
        mamaia_view_btn: "Szállások megtekintése",

        // Studio New York
        studio_ny_name: "Studio New York",
        studio_ny_location: "Nagyvárad, Románia",
        studio_ny_desc: "Modern és elegáns stúdió Nagyváradon, üzleti vagy turisztikai tartózkodásra tökéletes. Teljesen felszerelt minden szükséges létesítménnyel a kényelmes tartózkodáshoz.",

        // Közös szolgáltatások
        facility_studio: "Teljesen felszerelt stúdió",
        facility_kitchen: "Modern konyha",
        facility_bathroom: "Saját fürdőszoba",
        facility_ac: "Légkondicionálás",
        facility_wifi: "Ultragyors WiFi",
        facility_parking: "INGYENES Parkolás",
        facility_tv: "Smart TV",
        facility_linen: "Prémium ágynemű",
        facility_2rooms: "2 tágas szoba",
        facility_sea_view: "Panorámás tengeri kilátás",
        facility_full_kitchen: "Teljes konyha",
        facility_wifi_free: "Ingyenes WiFi",
        facility_balcony: "Nagy erkély",
        facility_beach_access: "Közvetlen strand hozzáférés",
        facility_kitchenette: "Funkcionális teakonyha",
        facility_modern_bath: "Modern fürdőszoba",
        facility_free_parking: "Ingyenes parkolás",
        facility_near_shops: "Üzletek közelében",
        facility_2big_rooms: "2 nagy szoba",
        facility_large_living: "Tágas nappali",
        facility_quiet_area: "Csendes környék",
        facility_equipped_kitchen: "Teljesen felszerelt konyha",
        facility_spacious_living: "Tágas nappali",
        facility_parking_available: "Parkolás elérhető",
        facility_near_beach_shops: "Strand és üzletek közelében",

        // Mamaia apartmanok
        apt_sea_name: "2 Szobás Apartman - Tengerparton",
        apt_sea_location: "Mamaia Nord - Első sor a tengerhez",
        apt_sea_desc: "Modern 2 szobás apartman látványos tengeri kilátással, első sorban. Tökéletes családoknak vagy kis csoportoknak.",
        apt_sea_placeholder: "Első sor a tengerhez",

        apt_lidl_studio_name: "Stúdió - Lidl közelében",
        apt_lidl_studio_location: "Mamaia Nord - Lidl közelében",
        apt_lidl_studio_desc: "Modern és kompakt stúdió, párok számára ideális. Kiváló elhelyezkedés a Lidl közelében, könnyű hozzáférés minden szolgáltatáshoz.",
        apt_lidl_studio_placeholder: "Modern Stúdió",

        apt_lidl_2room_name: "2 Szobás Apartman - Lidl közelében",
        apt_lidl_2room_location: "Mamaia Nord - Lidl közelében",
        apt_lidl_2room_desc: "Tágas 2 szobás apartman a Lidl közelében. Kiváló családoknak, minden szükséges felszereléssel.",
        apt_lidl_2room_placeholder: "Tágas Apartman",

        // Árak és gombok
        price_night: "Lej / éjszaka",
        check_availability: "Elérhetőség ellenőrzése",
        book_now: "Foglalás Most",

        // Szolgáltatások
        services_title: "Takarítási Szolgáltatások & Ingatlankezelő",
        services_subtitle: "Professzionálisan kezeljük az Ön ingatlanát",

        cleaning_title: "Professzionális Takarítási Szolgáltatások",
        cleaning_desc: "Teljes körű takarítási szolgáltatásokat kínálunk ingatlanaihoz, magas higiéniai és kényelmi szabványokat biztosítva a vendégek számára.",
        cleaning_checkout: "Takarítás minden kijelentkezés után",
        cleaning_deep: "Rendszeres mélytisztítás",
        cleaning_linen: "Ágynemű és törölköző csere",
        cleaning_supplies: "Kellékek ellenőrzése és feltöltése",
        cleaning_sanitize: "Fertőtlenítés és higiénizálás",
        cleaning_windows: "Ablak és terasz tisztítás",
        cleaning_price: "150 Lej-től / takarítás",

        pm_title: "Teljes Ingatlankezelés",
        pm_desc: "A-tól Z-ig kezeljük az ingatlanát, így Ön csak a bevételeket élvezheti gond nélkül.",
        pm_reservations: "Foglaláskezelés több platformon",
        pm_checkin: "Vendég bejelentkezés / kijelentkezés",
        pm_communication: "Vendégkommunikáció 24/7",
        pm_cleaning: "Rendszeres takarítás és karbantartás",
        pm_financial: "Pénzügyi kezelés és jelentések",
        pm_pricing: "Ár és foglaltság optimalizálás",
        pm_marketing: "Ingatlan marketing és promóció",
        pm_maintenance: "Karbantartás és kisebb javítások",
        pm_price: "A bevétel 15-20%-a / hónap",
        pm_request: "Ajánlatkérés",

        extra_title: "Kiegészítő Szolgáltatások",
        extra_desc: "Opcionális szolgáltatások a vendégélmény javítására és bevételeinek maximalizálására.",
        extra_transfer: "Repülőtéri / vasútállomási transzfer",
        extra_fridge: "Hűtő feltöltés kérésre",
        extra_concierge: "Concierge szolgáltatások",
        extra_reservations: "Étterem / tevékenység foglalások",
        extra_photo: "Professzionális ingatlan fotózás",
        extra_tech: "Műszaki és IT támogatás",
        extra_price: "Változó árak",

        services_cta_title: "Érdekli szolgáltatásunk?",
        services_cta_text: "Vegye fel velünk a kapcsolatot személyre szabott ajánlatért, és beszéljük meg egyedi igényeit.",
        services_cta_btn: "Kapcsolat Most",

        // Foglalási űrlap
        form_title: "Szállás Foglalása",
        form_name: "Teljes Név *",
        form_email: "Email *",
        form_phone: "Telefon *",
        form_accommodation: "Válassza a Szállást *",
        form_select: "Szállás kiválasztása",
        form_checkin: "Bejelentkezés *",
        form_checkout: "Kijelentkezés *",
        form_guests: "Vendégek Száma *",
        form_total: "Becsült Összeg",
        form_notes: "Megjegyzések / Különleges Kérések",
        form_notes_placeholder: "Írjon le bármilyen különleges kérést...",
        form_submit: "Foglalás Elküldése",
        form_success_title: "Foglalás Sikeresen Elküldve!",
        form_success_text: "Köszönjük a foglalását. Hamarosan felvesszük Önnel a kapcsolatot a megerősítéshez.",
        form_close: "Bezárás",

        // Kapcsolat
        contact_title: "Kapcsolat",
        contact_email_title: "Általános Email",
        contact_response: "24 órán belül válaszolunk",

        // Lábléc
        footer_text: "Cazare Premium - Nagyvárad & Mamaia Nord. Minden jog fenntartva.",

        // Külön oldalak
        page_oradea_title: "Szállás Nagyváradon - Cazare Premium",
        page_mamaia_title: "Szállás Mamaia Nordban - Cazare Premium",
        page_cleaning_title: "Takarítási Szolgáltatások - Cazare Premium",
        page_pm_title: "Ingatlankezelő - Cazare Premium",

        // Alternatív CTA
        cta_sea_title: "Tengerparti szállást keres?",
        cta_sea_text: "Fedezze fel prémium apartmanjainkat Mamaia Nordban - közvetlenül a tenger mellett",
        cta_sea_btn: "Mamaia Nord Szállások",
        cta_city_title: "A várost is felfedezi?",
        cta_city_text: "Fedezze fel prémium stúdiónkat Nagyváradon - városi kényelem Erdély szívében",
        cta_city_btn: "Nagyvárad Szállások",

        // Takarítás oldal
        cleaning_hero_title: "Professzionális Takarítási Szolgáltatások",
        cleaning_hero_subtitle: "Tisztán tartjuk tereit az elégedett vendégekért",
        cleaning_page_title: "Takarítási Szolgáltatások Szállásokhoz",

        cleaning_checkout_title: "Kijelentkezés Utáni Takarítás",
        cleaning_checkout_desc: "Teljes és professzionális takarítás a vendégek távozása után, előkészítve a teret a következő látogatók számára.",
        cleaning_vacuum: "Porszívózás és padlómosás",
        cleaning_bathroom: "Teljes fürdőszoba takarítás",
        cleaning_bedding: "Ágynemű csere",
        cleaning_disinfect: "Felület fertőtlenítés",

        cleaning_deep_title: "Mélytisztítás",
        cleaning_deep_desc: "Teljes mélytisztítási szolgáltatások a magas szabványok fenntartásához.",
        cleaning_windows_deep: "Ablaktisztítás",
        cleaning_furniture: "Bútor tisztítás",
        cleaning_kitchen_deep: "Konyha fertőtlenítés",
        cleaning_carpets: "Szőnyeg és textil tisztítás",

        cleaning_daily_title: "Napi Karbantartás",
        cleaning_daily_desc: "Rendszeres takarítási karbantartási szolgáltatások hosszú tartózkodáshoz.",
        cleaning_daily_room: "Napi szobatakarítás",
        cleaning_refill: "Higiéniai termékek feltöltése",
        cleaning_towels: "Törölköző csere",
        cleaning_check: "Általános állapot ellenőrzés",

        cleaning_cta_title: "Takarítási Szolgáltatás Kérése",
        cleaning_cta_text: "Vegye fel velünk a kapcsolatot személyre szabott árajánlatért",
        cleaning_cta_btn: "Kapcsolat",

        // Ingatlankezelő oldal
        pm_hero_title: "Ingatlankezelési Szolgáltatások",
        pm_hero_subtitle: "Professzionálisan és elkötelezetten kezeljük ingatlanát",
        pm_page_title: "Teljes Körű Ingatlankezelési Szolgáltatások",

        pm_full_title: "Teljes Körű Kezelés",
        pm_full_desc: "Minden szempontból gondoskodunk ingatlanáról, A-tól Z-ig.",
        pm_calendar: "Foglalás és naptár kezelés",
        pm_guest_checkin: "Vendég bejelentkezés / kijelentkezés",
        pm_guest_comm: "Vendégkommunikáció",
        pm_price_opt: "Ár és foglaltság optimalizálás",

        pm_maint_title: "Karbantartás és Javítások",
        pm_maint_desc: "Kifogástalan állapotban tartjuk az ingatlant.",
        pm_inspections: "Rendszeres ellenőrzések",
        pm_repairs_coord: "Javítások koordinálása",
        pm_preventive: "Megelőző karbantartás",
        pm_urgent: "Sürgősségi beavatkozás 24/7",

        pm_reports_title: "Jelentések és Pénzügyek",
        pm_reports_desc: "Teljes átláthatóság az ingatlan teljesítményéről.",
        pm_monthly: "Részletes havi jelentések",
        pm_income: "Bevétel/kiadás követés",
        pm_tax: "Adó optimalizálás",
        pm_dashboard: "Online dashboard 24/7",

        pm_marketing_title: "Marketing és Promóció",
        pm_marketing_desc: "Maximalizáljuk ingatlanának láthatóságát és foglalásait.",
        pm_photos: "Professzionális fotók",
        pm_platforms: "Listázás fő platformokon",
        pm_seo: "SEO és leírás optimalizálás",
        pm_reviews: "Értékelés kezelés",

        pm_security_title: "Biztosítás és Biztonság",
        pm_security_desc: "Megvédjük befektetését a kockázatoktól.",
        pm_guest_check: "Vendég ellenőrzés",
        pm_insurance: "Kár biztosítás",
        pm_legal: "Szerződés és jogi feltételek",
        pm_alarms: "Biztonság és riasztók",

        pm_vip_title: "VIP Szolgáltatások",
        pm_vip_desc: "Prémium szolgáltatások igényes tulajdonosoknak.",
        pm_concierge: "Vendég concierge",
        pm_design: "Belsőépítészet és dekoráció",
        pm_extra_services: "Kiegészítő szolgáltatások (transzfer, stb.)",
        pm_dedicated: "Dedikált menedzser",

        pm_cta_title: "Hagyja Ránk Ingatlana Gondját",
        pm_cta_text: "Vegye fel velünk a kapcsolatot ingyenes konzultációért",
        pm_cta_btn: "Konzultáció Kérése"
    }
};

// Functie pentru a obtine limba curenta
function getCurrentLang() {
    return localStorage.getItem('selectedLang') || 'ro';
}

// Functie pentru a seta limba
function setLang(lang) {
    localStorage.setItem('selectedLang', lang);
    applyTranslations(lang);
    updateLanguageSelector(lang);
}

// Functie pentru a aplica traducerile
function applyTranslations(lang) {
    const t = translations[lang];
    if (!t) return;

    // Actualizeaza toate elementele cu atributul data-translate
    document.querySelectorAll('[data-translate]').forEach(el => {
        const key = el.getAttribute('data-translate');
        if (t[key]) {
            if (el.tagName === 'INPUT' && el.type === 'text') {
                el.placeholder = t[key];
            } else if (el.tagName === 'TEXTAREA') {
                el.placeholder = t[key];
            } else {
                el.textContent = t[key];
            }
        }
    });

    // Actualizeaza placeholder-urile
    document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
        const key = el.getAttribute('data-translate-placeholder');
        if (t[key]) {
            el.placeholder = t[key];
        }
    });

    // Actualizeaza titlul paginii
    const pageTitle = document.querySelector('[data-translate-title]');
    if (pageTitle) {
        const key = pageTitle.getAttribute('data-translate-title');
        if (t[key]) {
            document.title = t[key];
        }
    }

    // Actualizeaza atributul lang pe html
    document.documentElement.lang = lang === 'hu' ? 'hu' : (lang === 'en' ? 'en' : 'ro');
}

// Functie pentru a actualiza selectorul de limba
function updateLanguageSelector(lang) {
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-lang') === lang) {
            btn.classList.add('active');
        }
    });
}

// Initializare la incarcarea paginii
document.addEventListener('DOMContentLoaded', function() {
    const currentLang = getCurrentLang();
    applyTranslations(currentLang);
    updateLanguageSelector(currentLang);
    initLanguageDropdown(currentLang);

    // Adauga event listeners pentru butoanele de limba (backward compatibility)
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang');
            setLang(lang);
        });
    });
});

// ========== LANGUAGE DROPDOWN FUNCTIONALITY ==========
function initLanguageDropdown(currentLang) {
    const dropdownBtn = document.getElementById('langDropdownBtn');
    const dropdownMenu = document.getElementById('langDropdownMenu');
    const dropdown = document.querySelector('.language-dropdown');

    if (!dropdownBtn || !dropdownMenu) return;

    // Update display based on current language
    updateDropdownDisplay(currentLang);

    // Toggle dropdown on button click
    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('open');
    });

    // Handle language option clicks
    document.querySelectorAll('.lang-option').forEach(option => {
        option.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang');

            // Update active state
            document.querySelectorAll('.lang-option').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');

            // Update dropdown display
            updateDropdownDisplay(lang);

            // Close dropdown
            dropdown.classList.remove('open');

            // Apply language change
            setLang(lang);
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    // Close dropdown on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('open');
        }
    });
}

function updateDropdownDisplay(lang) {
    const currentFlag = document.getElementById('currentFlag');
    const currentLangCode = document.getElementById('currentLangCode');

    if (!currentFlag || !currentLangCode) return;

    // Map language codes to display
    const langMap = {
        'ro': { code: 'RO', flagClass: 'flag-ro' },
        'en': { code: 'EN', flagClass: 'flag-en' },
        'hu': { code: 'HU', flagClass: 'flag-hu' },
        'de': { code: 'DE', flagClass: 'flag-de' }
    };

    const langInfo = langMap[lang] || langMap['ro'];

    // Update flag
    currentFlag.className = 'flag-icon ' + langInfo.flagClass;

    // Update code
    currentLangCode.textContent = langInfo.code;

    // Update active option in dropdown
    document.querySelectorAll('.lang-option').forEach(option => {
        if (option.getAttribute('data-lang') === lang) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}
