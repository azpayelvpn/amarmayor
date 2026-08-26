<?php

declare(strict_types=1);

return [
    'command_center' => 'Mayor / Administrator Command Center',
    'attention_required' => 'Executive Attention Required Queue',
    'directives' => 'Executive Directives',
    'explanation_requests' => 'Formal Explanation Requests',

    'triggers' => [
        'critical_hazard' => 'Critical Public Safety Hazard (P1)',
        'first_deadline_failure' => 'First SLA Deadline Missed',
        'first_citizen_reopen' => 'First Citizen Reopen (Needs More Work)',
        'repeated_failure' => 'Repeated Field Failure',
        'high_supporters' => 'High Citizen Support Count',
        'recurring_hotspot' => 'Recurring Problem Hotspot',
        'complaint_spike' => 'Unusual Complaint Spike',
    ],

    'directives_types' => [
        'ask_for_action' => 'Direct Immediate Action Order',
        'provide_support' => 'Approve Additional Resources & Budget',
        'request_inspection' => 'Order Senior Field Inspection',
        'set_priority' => 'Escalate Priority Order',
    ],
];
