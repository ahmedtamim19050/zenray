<?php

/*
|--------------------------------------------------------------------------
| ZenRay Brand & Site Content
|--------------------------------------------------------------------------
|
| Central place for brand details and the display content used on the
| public site. Contact details are read from the environment so that no
| unconfirmed information is hard-coded; anything left empty is shown as
| a clearly marked placeholder.
|
| Equipment categories, products, services, industries, hero slides,
| gallery and stats below are only the starting content: the migration
| 2026_11_01_000001_create_content_tables copies them into the database,
| and from then on they are managed in Admin → Website content
| (loaded back into these keys by App\Support\SiteContent).
|
*/

return [

    'name' => 'ZenRay Medical Systems',

    'short_name' => 'ZenRay',

    'tagline' => 'Precision Imaging. Better Diagnostics.',

    'description' => 'ZenRay Medical Systems helps healthcare facilities find medical imaging equipment, with installation, maintenance and technical support services.',

    'contact' => [
        'email' => env('ZENRAY_CONTACT_EMAIL'),
        'phone' => env('ZENRAY_CONTACT_PHONE'),
        'address' => env('ZENRAY_CONTACT_ADDRESS'),
        'hours' => env('ZENRAY_BUSINESS_HOURS'),
        'whatsapp' => null,
        'map_url' => null,
    ],

    // Social profile links; set from Admin → Settings. Empty values are hidden.
    'social' => [
        'facebook' => null,
        'instagram' => null,
        'linkedin' => null,
        'youtube' => null,
    ],

    'navigation' => [
        ['label' => 'Home', 'route' => 'home'],
        // Optional "fragment" appends #section to the link.
        // "menu" renders a dropdown / mega menu for that item on desktop.
        ['label' => 'Equipment', 'route' => 'products.index', 'menu' => 'equipment'],
        ['label' => 'Services', 'route' => 'services.index', 'menu' => 'services'],
        ['label' => 'About Us', 'route' => 'about'],
        ['label' => 'Projects', 'route' => 'portfolio.index'],
        ['label' => 'Blog', 'route' => 'blog.index'],
        ['label' => 'Contact', 'route' => 'contact'],
    ],

    /*
    | Equipment categories shown on the homepage. These describe the kinds
    | of equipment ZenRay can help with; they are not a stock list.
    */
    'product_categories' => [
        [
            'name' => 'X-Ray Systems',
            'slug' => 'x-ray-systems',
            'photo' => 'angio-suite',
            'alt' => 'Large C-shaped X-ray system mounted from the ceiling above a procedure table',
            'summary' => 'Machines that take X-ray images, from fixed room systems to mobile units that can be wheeled to the patient.',
        ],
        [
            'name' => 'Digital Radiography',
            'slug' => 'digital-radiography',
            'photo' => 'detector-panel',
            'alt' => 'Flat digital X-ray detector panel photographed against a white background',
            'summary' => 'Digital detectors that capture X-ray images electronically, so they can be viewed on a screen instead of film.',
        ],
        [
            'name' => 'Imaging Accessories',
            'slug' => 'imaging-accessories',
            'photo' => 'apron',
            'alt' => 'Protective lead apron with a thyroid collar hanging on a wall hook',
            'summary' => 'Everyday essentials around the exam, such as protective aprons, positioning aids and detector holders.',
        ],
        [
            'name' => 'Other Diagnostic Equipment',
            'slug' => 'diagnostic-equipment',
            'photo' => 'ultrasound',
            'alt' => 'Control panel and keyboard of an ultrasound scanner',
            'summary' => 'Additional diagnostic equipment, such as ultrasound systems, to support your clinical services.',
        ],
    ],

    /*
    | Services. Confirm this list matches the services ZenRay offers before
    | going live. Copy intentionally avoids response times or coverage claims.
    */
    'services' => [
        [
            'name' => 'Equipment Installation',
            'slug' => 'installation',
            'icon' => 'install',
            'summary' => 'Get help setting up new equipment and preparing it for everyday use.',
            'next_step' => 'Share your room details and the equipment you are considering.',
            'action' => 'Ask about installation',
        ],
        [
            'name' => 'Maintenance & Technical Support',
            'slug' => 'maintenance',
            'icon' => 'support',
            'summary' => 'Help keeping your equipment in good working order, and support when something is not working as expected.',
            'next_step' => 'Tell us which equipment you have and the support you need.',
            'action' => 'Ask about support',
        ],
        [
            'name' => 'Equipment Inspection',
            'slug' => 'inspection',
            'icon' => 'inspect',
            'summary' => 'A check of your equipment’s condition, so you can plan servicing, repairs or replacements.',
            'next_step' => 'Let us know the equipment and where it is located.',
            'action' => 'Ask about inspection',
        ],
        [
            'name' => 'Technical Consultation',
            'slug' => 'consultation',
            'icon' => 'consult',
            'summary' => 'Advice on choosing equipment that suits your facility, your space and your budget.',
            'next_step' => 'Book a conversation to talk through your options.',
            'action' => 'Book a consultation',
        ],
    ],

    /*
    | Example product cards. Each one is labelled as an example so it is never
    | presented as real inventory. Replace with Product models later.
    */
    'demo_products' => [
        [
            'name' => 'Mobile X-ray unit',
            'category' => 'x-ray-systems',
            'photo' => 'xray-mobile',
            'alt' => 'Mobile X-ray unit on wheels, with its tube arm folded for moving',
            'summary' => 'A wheeled X-ray system that can be taken to the patient, for example on wards or in emergency areas.',
        ],
        [
            'name' => 'Mobile C-arm',
            'category' => 'x-ray-systems',
            'photo' => 'c-arm',
            'alt' => 'C-shaped mobile X-ray system positioned beside an operating table',
            'summary' => 'A C-shaped X-ray system that shows live images during procedures, often used in operating theatres.',
        ],
        [
            'name' => 'Digital X-ray room',
            'category' => 'x-ray-systems',
            'photo' => 'room-overview',
            'alt' => 'Digital X-ray room with a patient table, tube stand and wall stand',
            'summary' => 'A fixed room set-up with a patient table, tube stand and wall stand for general X-ray exams.',
        ],
        [
            'name' => 'Wall-stand detector',
            'category' => 'digital-radiography',
            'photo' => 'dr-wallstand',
            'alt' => 'Wall-mounted digital X-ray detector on a vertical stand',
            'summary' => 'Holds a digital detector upright for exams taken while standing, such as chest X-rays.',
        ],
        [
            'name' => 'Protective lead apron',
            'category' => 'imaging-accessories',
            'photo' => 'apron',
            'alt' => 'Protective lead apron with a thyroid collar hanging on a wall hook',
            'summary' => 'Worn by staff to help reduce radiation exposure during X-ray procedures.',
        ],
        [
            'name' => 'Ultrasound system',
            'category' => 'diagnostic-equipment',
            'photo' => 'ultrasound',
            'alt' => 'Control panel and keyboard of an ultrasound scanner',
            'summary' => 'Uses sound waves to create images of organs and tissue, without using X-rays.',
        ],
    ],

    /*
    | Gallery of representative equipment photography. These are NOT ZenRay
    | client installations; replace with approved project photos when available.
    */
    'gallery' => [
        // Sizes are arranged to fill a 4-column grid: large (2x2), tall (1x2), normal, normal, wide, wide.
        ['photo' => 'angio-suite', 'caption' => 'Ceiling-mounted X-ray system in a procedure room', 'size' => 'large'],
        ['photo' => 'c-arm', 'caption' => 'Mobile C-arm in an operating theatre', 'size' => 'tall'],
        ['photo' => 'detector-panel', 'caption' => 'Flat-panel digital detector', 'size' => 'normal'],
        ['photo' => 'tube-head', 'caption' => 'X-ray tube head and controls', 'size' => 'normal'],
        ['photo' => 'room-overview', 'caption' => 'General digital X-ray room', 'size' => 'wide'],
        ['photo' => 'technician', 'caption' => 'Technical work on medical equipment', 'size' => 'wide'],
    ],

    /*
    | Homepage hero carousel. Each slide showcases an equipment type (not a
    | specific model). "category" links the slide to the equipment filter
    | and pre-selects it on the quote form.
    */
    'hero_slides' => [
        [
            'eyebrow' => 'Digital Radiography',
            'title' => 'Flat-panel detectors',
            'text' => 'Capture X-ray images digitally, ready to view on a screen instead of film.',
            'photo' => 'detector-panel',
            'alt' => 'Flat digital X-ray detector panel',
            'category' => 'digital-radiography',
        ],
        [
            'eyebrow' => 'X-Ray Systems',
            'title' => 'Mobile C-arms',
            'text' => 'Live X-ray imaging that can be positioned around the patient in theatres and procedure rooms.',
            'photo' => 'c-arm',
            'alt' => 'Mobile C-arm X-ray system beside an operating table',
            'category' => 'x-ray-systems',
        ],
        [
            'eyebrow' => 'X-Ray Systems',
            'title' => 'Digital X-ray rooms',
            'text' => 'Complete room set-ups with table, tube stand and wall stand for everyday general X-ray.',
            'photo' => 'hero-room',
            'alt' => 'Digital X-ray room with patient table, tube stand and wall stand',
            'category' => 'x-ray-systems',
        ],
        [
            'eyebrow' => 'Diagnostic Equipment',
            'title' => 'Ultrasound systems',
            'text' => 'Imaging with sound waves for a wide range of everyday diagnostic exams.',
            'photo' => 'ultrasound',
            'alt' => 'Control panel of an ultrasound system',
            'category' => 'diagnostic-equipment',
        ],
    ],

    /*
    | Facility types ZenRay supports ("Who we serve"). These describe target
    | customers, not named clients.
    */
    'industries' => [
        ['name' => 'Hospitals', 'icon' => 'layers', 'text' => 'Equipment and technical support for busy radiology departments and theatres.'],
        ['name' => 'Diagnostic & imaging centres', 'icon' => 'monitor', 'text' => 'Digital X-ray and diagnostic systems for dedicated imaging services.'],
        ['name' => 'Clinics & medical practices', 'icon' => 'users', 'text' => 'Practical imaging options sized for smaller rooms and teams.'],
        ['name' => 'Orthopaedic practices', 'icon' => 'xray', 'text' => 'X-ray equipment suited to bone and joint imaging.'],
        ['name' => 'Urgent care', 'icon' => 'clock', 'text' => 'Reliable imaging for walk-in and emergency settings.'],
        ['name' => 'Mobile & field imaging', 'icon' => 'detector', 'text' => 'Portable X-ray units and detectors for imaging on the move.'],
    ],

    /*
    | Company figures for the stats band. The band is hidden while this is
    | empty. Only add verified numbers, e.g.:
    | ['value' => '10+', 'label' => 'Years of experience'],
    */
    'stats' => [],

    /*
    | Where website enquiries are emailed. Falls back to the contact email,
    | then to the application's MAIL_FROM_ADDRESS.
    */
    'enquiries_to' => env('ZENRAY_ENQUIRY_EMAIL', env('ZENRAY_CONTACT_EMAIL')),

    /*
    | Quotations, invoices and inventory. Editable in Admin → Settings → Billing.
    */
    'billing' => [
        'currency' => 'BDT',
        'currency_symbol' => '৳',
        'tax_label' => 'Tax',
        'tax_rate' => '0',
        'tax_number' => null,
        'quote_prefix' => 'QT',
        'invoice_prefix' => 'INV',
        'quote_valid_days' => '30',
        'payment_terms_days' => '30',
        'payment_details' => null,
        'quote_terms' => 'Prices are valid until the date shown. Delivery and installation times are confirmed on order.',
        'invoice_notes' => 'Thank you for your business.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Photography
    |--------------------------------------------------------------------------
    |
    | Responsive WebP files live in public/images/photos as {key}-{width}.webp.
    | "width"/"height" are the largest file's dimensions (used for aspect ratio).
    | "credit" links each image to its licence entry below.
    |
    */
    'photos' => [
        'hero-room' => ['widths' => [640, 960, 1440, 1920], 'width' => 1920, 'height' => 1846, 'credit' => 'xray-room'],
        'room-overview' => ['widths' => [800, 1280, 1760], 'width' => 1760, 'height' => 1320, 'credit' => 'xray-room'],
        'tube-head' => ['widths' => [480, 800], 'width' => 800, 'height' => 845, 'credit' => 'xray-room'],
        'xray-mobile' => ['widths' => [480, 800, 1120], 'width' => 1120, 'height' => 1602, 'credit' => 'xray-mobile'],
        'dr-wallstand' => ['widths' => [480, 800, 1120], 'width' => 1120, 'height' => 1456, 'credit' => 'dr-wallstand'],
        'apron' => ['widths' => [480, 800, 1120], 'width' => 1120, 'height' => 1291, 'credit' => 'apron'],
        'ultrasound' => ['widths' => [480, 800, 1120], 'width' => 1120, 'height' => 622, 'credit' => 'ultrasound'],
        'technician' => ['widths' => [640, 960, 1440], 'width' => 1440, 'height' => 791, 'credit' => 'technician'],
        'angio-suite' => ['widths' => [640, 960, 1440], 'width' => 1440, 'height' => 1339, 'credit' => 'angio-suite'],
        'c-arm' => ['widths' => [640, 960, 1440], 'width' => 1440, 'height' => 1488, 'credit' => 'c-arm'],
        'detector-panel' => ['widths' => [480, 800, 1120], 'width' => 1120, 'height' => 853, 'credit' => 'detector-panel'],
    ],

    /*
    | Licence information for third-party photography (Wikimedia Commons).
    | Shown on the Image Credits page. Replace when switching to owned or
    | purchased photography.
    */
    'photo_credits' => [
        'angio-suite' => [
            'title' => 'KH St Elisabeth RV 2013 Radiologie Angiographie',
            'author' => 'Andreas Praefcke',
            'license' => 'Public domain',
            'license_url' => null,
            'source_url' => 'https://commons.wikimedia.org/wiki/File:KH_St_Elisabeth_RV_2013_Radiologie_Angiographie.jpg',
            'changes' => 'Cropped, resized, contrast adjusted, manufacturer and product labels removed.',
        ],
        'c-arm' => [
            'title' => 'New Alma Hospital Mannarkkad orthopaedic OT laminar flow C-arm',
            'author' => 'Drnabeelmohdkk1',
            'license' => 'CC0 1.0',
            'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:New_Alma_Hospital_Mannarkkad_orthopaedic_OT_laminar_flow_C-arm.jpg',
            'changes' => 'Cropped, resized, contrast adjusted.',
        ],
        'detector-panel' => [
            'title' => 'Digital X-Ray Detector Panel',
            'author' => 'The Central Intelligence Agency (Flickr)',
            'license' => 'Public domain',
            'license_url' => null,
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Digital_X-Ray_Detector_Panel_-_Flickr_-_The_Central_Intelligence_Agency.jpg',
            'changes' => 'Cropped, resized, contrast adjusted. Use does not imply endorsement by the CIA.',
        ],
        'xray-room' => [
            'title' => 'Digital radiography Machine 08',
            'author' => 'أمين',
            'license' => 'CC BY-SA 4.0',
            'license_url' => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Digital_radiography_Machine_08.jpg',
            'changes' => 'Cropped, resized, contrast adjusted, manufacturer labels removed.',
        ],
        'xray-mobile' => [
            'title' => 'Mobile X-Ray Unit Sirius Star Mobile',
            'author' => 'Syced',
            'license' => 'CC0 1.0',
            'license_url' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Mobile_X-Ray_Unit_Sirius_Star_Mobile.jpg',
            'changes' => 'Cropped, resized, product name blurred.',
        ],
        'dr-wallstand' => [
            'title' => 'Digital radiography Machine 09',
            'author' => 'أمين',
            'license' => 'CC BY-SA 4.0',
            'license_url' => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Digital_radiography_Machine_09.jpg',
            'changes' => 'Cropped, resized, contrast adjusted.',
        ],
        'apron' => [
            'title' => 'Darby X-Ray apron 20230316 105852',
            'author' => 'Kiran891',
            'license' => 'CC BY-SA 4.0',
            'license_url' => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Darby_X-Ray_apron_20230316_105852.jpg',
            'changes' => 'Cropped, resized, contrast adjusted.',
        ],
        'ultrasound' => [
            'title' => 'A modern medical ultrasound scanner',
            'author' => 'Harrison Keely',
            'license' => 'CC BY 4.0',
            'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
            'source_url' => 'https://commons.wikimedia.org/wiki/File:A_modern_medical_ultrasound_scanner.jpg',
            'changes' => 'Cropped to the control panel, resized, contrast adjusted.',
        ],
        'technician' => [
            'title' => 'Biomedical Equipment Technicians Keep Medical Equipment Running in St Lucia (8903443)',
            'author' => 'U.S. Air Force photo by Andrea Jenkins',
            'license' => 'Public domain',
            'license_url' => null,
            'source_url' => 'https://commons.wikimedia.org/wiki/File:Biomedical_Equipment_Technicians_Keep_Medical_Equipment_Running_in_St_Lucia_(8903443).jpg',
            'changes' => 'Cropped, resized, contrast adjusted. Use does not imply endorsement by the U.S. Department of Defense.',
        ],
    ],

];
