<?php

declare(strict_types=1);

return [
    'required' => ':field তথ্যটি পূরণ করা আবশ্যক।',
    'string' => ':field টেক্সট ফরম্যাটে হতে হবে।',
    'integer' => ':field একটি পূর্ণসংখ্যা হতে হবে।',
    'numeric' => ':field একটি সংখ্যা হতে হবে।',
    'email' => 'একটি সঠিক ইমেইল ঠিকানা দিন।',
    'phone' => 'একটি সঠিক বাংলাদেশী মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)।',
    'boolean' => ':field সঠিক সত্য/মিথ্যা মান হতে হবে।',
    'min' => ':field কমপক্ষে :min অক্ষরের হতে হবে।',
    'max' => ':field সর্বোচ্চ :max অক্ষরের হতে পারবে।',
    'in' => 'নির্বাচিত :field সঠিক নয়।',
    'date' => ':field একটি সঠিক তারিখ হতে হবে।',

    'fields' => [
        'phone' => 'মোবাইল নম্বর',
        'otp' => 'ওটিপি কোড',
        'category_id' => 'অভিযোগের ধরণ',
        'subcategory_id' => 'নির্দিষ্ট সমস্যা',
        'ward_id' => 'ওয়ার্ড নম্বর',
        'description' => 'সমস্যার বিবরণ',
        'latitude' => 'অক্ষাংশ (Latitude)',
        'longitude' => 'দ্রাঘিমাংশ (Longitude)',
    ],
];
