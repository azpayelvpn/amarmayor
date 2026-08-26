<?php

declare(strict_types=1);

return [
    'command_center' => 'মেয়র / প্রশাসক কমান্ড সেন্টার',
    'attention_required' => 'জরুরি দৃষ্টি আকর্ষণ কিউ',
    'directives' => 'নির্বাহী নির্দেশনাসমূহ',
    'explanation_requests' => 'ব্যাখ্যা তলব ও জবাবদিহিতা',

    'triggers' => [
        'critical_hazard' => 'জরুরি জননিরাপত্তা ঝুঁকি (P1)',
        'first_deadline_failure' => 'প্রথমবার সময়সীমা অতিক্রম (First SLA Overdue)',
        'first_citizen_reopen' => 'নাগরিক কর্তৃক প্রথমবার রি-ওপেন (Needs More Work)',
        'repeated_failure' => 'একাধিকবার কাজ ব্যর্থ হওয়া',
        'high_supporters' => 'উচ্চ সংখ্যক নাগরিক কর্তৃক সমর্থন',
        'recurring_hotspot' => 'একই স্থানে পুনরাবৃত্তিমূলক সমস্যা',
        'complaint_spike' => 'হঠাৎ অস্বাভাবিক অভিযোগের আধিক্য',
    ],

    'directives_types' => [
        'ask_for_action' => 'জরুরি পদক্ষেপের নির্দেশ',
        'provide_support' => 'অতিরিক্ত সম্পদ ও সহায়তা বরাদ্দ',
        'request_inspection' => 'উচ্চপর্যায়ের সরজমিনে পরিদর্শন',
        'set_priority' => 'অগ্রাধিকার পুনর্বিন্যাস',
    ],
];
