<?php

declare(strict_types=1);

return [
    'required' => 'The :field field is required.',
    'string' => 'The :field must be a string.',
    'integer' => 'The :field must be an integer.',
    'numeric' => 'The :field must be a number.',
    'email' => 'Please provide a valid email address.',
    'phone' => 'Please provide a valid Bangladesh phone number (e.g. 017XXXXXXXX).',
    'boolean' => 'The :field must be true or false.',
    'min' => 'The :field must be at least :min characters.',
    'max' => 'The :field may not be greater than :max characters.',
    'in' => 'The selected :field is invalid.',
    'date' => 'The :field is not a valid date.',

    'fields' => [
        'phone' => 'Phone Number',
        'otp' => 'OTP Code',
        'category_id' => 'Complaint Category',
        'subcategory_id' => 'Specific Problem',
        'ward_id' => 'Ward Number',
        'description' => 'Problem Description',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
    ],
];
