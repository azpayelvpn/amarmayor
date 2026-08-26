<?php

declare(strict_types=1);

return [
    'title' => 'নাগরিক অভিযোগ ও সেবা',
    'complaint_number' => 'অভিযোগ নম্বর',
    'tracking_number' => 'ট্র্যাকিং নম্বর',
    'category' => 'ক্যাটাগরি / সেবার ধরণ',
    'subcategory' => 'সাবক্যাটাগরি / সুনির্দিষ্ট সমস্যা',
    'ward' => 'ওয়ার্ড',
    'zone' => 'অঞ্চল',
    'location' => 'সমস্যার সঠিক স্থান',
    'landmark' => 'নিকটবর্তী পরিচিত স্থান / ল্যান্ডমার্ক',
    'description' => 'সমস্যার বিবরণ',
    'evidence_photos' => 'প্রমাণমূলক ছবি / ভিডিও',
    'camera_required_notice' => 'এই সমস্যার ক্ষেত্রে সরাসরি ক্যামেরা দিয়ে তোলা ছবি সংযুক্ত করা আবশ্যক।',
    'submission_success' => 'আপনার অভিযোগটি সফলভাবে নিবন্ধিত হয়েছে। ট্র্যাকিং নম্বর: :number',
    'already_submitted_recently' => 'একই অভিযোগ সম্প্রতি দাখিল করা হয়েছে। ট্র্যাকিং নম্বর: :number',

    // 7 Citizen Presentation Statuses
    'citizen_status' => [
        'received' => 'অভিযোগ জমা হয়েছে',
        'assigned' => 'দায়িত্ব অর্পণ করা হয়েছে',
        'in_progress' => 'কাজ চলমান',
        'work_completed' => 'কাজ সম্পন্ন হয়েছে',
        'confirmation_needed' => 'নাগরিক যাচাই প্রয়োজন',
        'resolved' => 'সমাধান নিশ্চিত',
        'needs_more_work' => 'পুনরায় কাজ প্রয়োজন',
    ],

    // 17 Internal State Machine Statuses
    'internal_status' => [
        'submitted' => 'নতুন দাখিলকৃত',
        'review_required' => 'বাছাই ও পর্যালোচনা প্রয়োজন',
        'routed' => 'স্বয়ংক্রিয় রাউট সম্পন্ন',
        'assigned' => 'দায়িত্ব অর্পিত',
        'accepted' => 'দায়িত্ব গ্রহণকৃত',
        'in_progress' => 'মাঠপর্যায়ে কাজ চলমান',
        'work_completed' => 'মাঠের কাজ সমাপ্ত ঘোষিত',
        'verification_required' => 'সুপারভাইজার যাচাই অপেক্ষমাণ',
        'awaiting_citizen_confirmation' => 'নাগরিক নিশ্চিতকরণ অপেক্ষমাণ',
        'closed' => 'সম্পূর্ণ নিষ্পন্ন ও বন্ধ',
        'needs_more_work' => 'অপূর্ণাঙ্গ / পুনরায় কাজ প্রয়োজন',
        'transferred' => 'মালিকানা স্থানান্তরিত',
        'project_required' => 'উন্নয়ন প্রকল্প / বড় বাজেট প্রয়োজন',
        'referred_external' => 'বাহ্যিক সংস্থায় প্রেরিত',
        'duplicate_linked' => 'দ্বৈত / লিংককৃত',
        'rejected' => 'বাতিল / অযোগ্য',
        'cancelled' => 'নাগরিক কর্তৃক প্রত্যাহার',
    ],

    // Priorities
    'priority' => [
        'p1_critical' => 'জরুরি / বিপদজনক (P1)',
        'p2_high' => 'উচ্চ অগ্রাধিকার (P2)',
        'p3_normal' => 'সাধারণ (P3)',
        'p4_low' => 'নিম্ন অগ্রাধিকার (P4)',
    ],

    // Operational Classifications
    'classification' => [
        'quick_action' => 'দ্রুত পদক্ষেপ (Quick Action)',
        'maintenance_required' => 'নিয়মিত রক্ষণাবেক্ষণ (Maintenance Required)',
        'technical_assessment' => 'প্রকৌশল যাচাই প্রয়োজন (Technical Assessment)',
        'project_required' => 'উন্নয়ন প্রকল্প প্রয়োজন (Project Required)',
        'external_agency' => 'অন্যান্য সরকারি সংস্থা সংশ্লিষ্ট (External Agency)',
        'administrative_service' => 'প্রশাসনিক সেবা (Administrative)',
    ],

    // Evidence Stages
    'evidence_stage' => [
        'citizen_submission' => 'নাগরিক কর্তৃক প্রাথমিক প্রমাণ',
        'before_work' => 'কাজের শুরুর পূর্বাবস্থার ছবি',
        'in_progress' => 'কাজ চলাকালীন ছবি',
        'after_work' => 'কাজ সমাপ্তির পরের বাস্তব ছবি',
        'supervisor_verification' => 'সুপারভাইজার সরজমিনে যাচাইয়ের ছবি',
    ],

    // Feedback & Confirmation
    'feedback' => [
        'confirm_title' => 'সমাধান নিশ্চিতকরণ',
        'is_resolved_question' => 'আপনার উল্লেখিত সমস্যার কি সন্তোষজনক সমাধান হয়েছে?',
        'yes_resolved' => 'হ্যাঁ, সমাধান হয়েছে',
        'partially_resolved' => 'আংশিক সমাধান হয়েছে',
        'not_resolved' => 'না, এখনো সমাধান হয়নি',
        'rating_label' => 'কাজের মানের রেটিং দিন',
        'comment_placeholder' => 'আপনার কোনো মন্তব্য বা অভিজ্ঞতা জানান...',
        'thank_you' => 'আপনার মতামতের জন্য ধন্যবাদ।',
    ],
];
