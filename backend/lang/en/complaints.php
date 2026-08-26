<?php

declare(strict_types=1);

return [
    'title' => 'Citizen Complaints & Civic Services',
    'complaint_number' => 'Complaint Number',
    'tracking_number' => 'Tracking Number',
    'category' => 'Category / Service Type',
    'subcategory' => 'Subcategory / Specific Issue',
    'ward' => 'Ward',
    'zone' => 'Zone',
    'location' => 'Exact Problem Location',
    'landmark' => 'Nearby Landmark / Area',
    'description' => 'Problem Description',
    'evidence_photos' => 'Evidence Photos / Videos',
    'camera_required_notice' => 'Direct camera photo capture is required for this issue.',
    'submission_success' => 'Your complaint has been submitted successfully. Tracking Number: :number',
    'already_submitted_recently' => 'A similar complaint was recently submitted. Tracking Number: :number',

    // 7 Citizen Presentation Statuses
    'citizen_status' => [
        'received' => 'Complaint Received',
        'assigned' => 'Officer / Team Assigned',
        'in_progress' => 'Work In Progress',
        'work_completed' => 'Work Completed',
        'confirmation_needed' => 'Citizen Confirmation Needed',
        'resolved' => 'Resolved & Confirmed',
        'needs_more_work' => 'Needs More Work',
    ],

    // 17 Internal State Machine Statuses
    'internal_status' => [
        'submitted' => 'Newly Submitted',
        'review_required' => 'Review & Triage Required',
        'routed' => 'Automatically Routed',
        'assigned' => 'Assigned to Supervisor/Team',
        'accepted' => 'Accepted by Team',
        'in_progress' => 'Field Work In Progress',
        'work_completed' => 'Field Work Reported Complete',
        'verification_required' => 'Supervisor Verification Pending',
        'awaiting_citizen_confirmation' => 'Awaiting Citizen Confirmation',
        'closed' => 'Resolved & Closed',
        'needs_more_work' => 'Incomplete / Needs More Work',
        'transferred' => 'Transferred to Another Department',
        'project_required' => 'Development Project Required',
        'referred_external' => 'Referred to External Agency',
        'duplicate_linked' => 'Duplicate Linked Case',
        'rejected' => 'Rejected / Out of Scope',
        'cancelled' => 'Cancelled by Citizen',
    ],

    // Priorities
    'priority' => [
        'p1_critical' => 'Emergency / Critical (P1)',
        'p2_high' => 'High Priority (P2)',
        'p3_normal' => 'Normal Priority (P3)',
        'p4_low' => 'Low Priority (P4)',
    ],

    // Operational Classifications
    'classification' => [
        'quick_action' => 'Quick Action',
        'maintenance_required' => 'Maintenance Required',
        'technical_assessment' => 'Technical Assessment',
        'project_required' => 'Project Required',
        'external_agency' => 'External Agency',
        'administrative_service' => 'Administrative Service',
    ],

    // Evidence Stages
    'evidence_stage' => [
        'citizen_submission' => 'Citizen Initial Evidence',
        'before_work' => 'Before Work Photo',
        'in_progress' => 'In Progress Photo',
        'after_work' => 'After Work Completion Photo',
        'supervisor_verification' => 'Supervisor Field Verification Photo',
    ],

    // Feedback & Confirmation
    'feedback' => [
        'confirm_title' => 'Resolution Confirmation',
        'is_resolved_question' => 'Has your reported issue been resolved satisfactorily?',
        'yes_resolved' => 'Yes, Resolved',
        'partially_resolved' => 'Partially Resolved',
        'not_resolved' => 'No, Problem Still Exists',
        'rating_label' => 'Rate the Quality of Work',
        'comment_placeholder' => 'Share your feedback or experience...',
        'thank_you' => 'Thank you for your feedback.',
    ],
];
