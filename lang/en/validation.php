<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each these messages here.
    |
    */

    'accepted' => 'The :attribute field must be accepted.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'The :attribute field must be a date after :date.',
    'after_or_equal' => 'The :attribute field must be a date after or equal to :date.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'array' => 'The :attribute field must be an array.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute field must be a date before :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute field must have between :min and :max items.',
        'file' => 'The :attribute field must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute field must be between :min and :max.',
        'string' => 'The :attribute field must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'contains' => 'The :attribute field is missing a required value.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute field must be a valid date.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field must exist in :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'register' => [
            'name.required' => 'Please provide your name.',
            'email.required' => 'Your email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Your phone number is required for verification.',
            'phone.min' => 'Your phone number must have at least 10 digits.',
            'phone.max' => 'Your phone number cannot exceed 15 digits.',
            'password.required' => 'You need to provide a password.',
            'password.min' => 'Your password must have at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'accepted_terms.required' => 'You must accept the terms and conditions.',
            'address.required' => 'Your address is required.',
            'zip_code.required' => 'The ZIP code is missing.',
            'latitude.required' => 'The latitude is required for geolocation.',
            'longitude.required' => 'The longitude is required for geolocation.',
        ],
        'login' => [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'The email provided is not valid.',
            'password.required' => 'You need to provide your password.',
            'invalid_credentials' => 'The provided credentials are incorrect. Please try again.',
        ],
        'profile' => [
            'name.required' => 'Your profile name is required.',
            'name.max' => 'Your name cannot exceed :max characters.',
            'biography.max' => 'Your biography cannot exceed :max characters.',
            'profile_photo.image' => 'The profile photo must be an image.',
            'profile_photo.max' => 'The profile photo cannot exceed :max kilobytes.',
            'profile_video.mimes' => 'The profile video must be in a valid format (mp4, mov, avi).',
            'profile_video.max' => 'The profile video cannot exceed :max kilobytes.',
            'verification_documents.*.file' => 'Each verification document must be a valid file.',
            'verification_documents.*.mimes' => 'Verification documents must be in PDF or image format.',
        ],
        'service_requests' => [
            'title.required' => 'Please provide a title for your service request.',
            'title.max' => 'The title cannot exceed :max characters.',
            'description.required' => 'A detailed description of the service is required.',
            'description.min' => 'The description must be at least :min characters long.',
            'budget.required' => 'Please specify your budget for this service.',
            'budget.numeric' => 'The budget must be a valid number.',
            'budget.min' => 'The minimum budget must be at least :min.',
            'category_id.required' => 'Please select a service category.',
            'category_id.exists' => 'The selected category is invalid.',
            'service_type.required' => 'Please specify the type of service.',
            'service_type.in' => 'Invalid service type selected.',
        ],
        'reviews' => [
            'rating.required' => 'Please provide a rating.',
            'rating.between' => 'The rating must be between 1 and 5 stars.',
            'comment.required' => 'Please provide a review comment.',
            'comment.min' => 'The review comment must be at least :min characters.',
            'service_request_id.required' => 'The service request reference is required.',
            'service_request_id.exists' => 'Invalid service request reference.',
        ],
        'payments' => [
            'amount.required' => 'The payment amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The minimum payment amount is :min.',
            'payment_method_id.required' => 'Please select a payment method.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
            'transaction_id.required' => 'Transaction reference is required.',
            'currency.required' => 'Please specify the currency.',
            'currency.in' => 'Invalid currency selected.',
        ],
        'tickets' => [
            'category.required' => 'Please select a ticket category.',
            'category.in' => 'Invalid ticket category selected.',
            'message.required' => 'Please describe your issue.',
            'message.min' => 'The description must be at least :min characters.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Invalid priority level selected.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Attachments must be in PDF or image format.',
        ],
        'location' => [
            'latitude.required' => 'Latitude is required for location tracking.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.required' => 'Longitude is required for location tracking.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'accuracy.min' => 'Location accuracy cannot be negative.',
            'speed.min' => 'Speed cannot be negative.',
            'heading.between' => 'Heading must be between 0 and 360 degrees.',
        ],
        'skills' => [
            'name.required' => 'Skill name is required.',
            'name.max' => 'Skill name cannot exceed :max characters.',
            'description.required' => 'Please provide a skill description.',
            'category_ids.required' => 'Please select at least one category.',
            'category_ids.*.exists' => 'One or more selected categories are invalid.',
            'experience_level.required' => 'Please specify your experience level.',
            'experience_level.between' => 'Experience level must be between 1 and 5.',
        ],
        'subscriptions' => [
            'plan_name.required' => 'Please select a subscription plan.',
            'plan_name.in' => 'Invalid subscription plan selected.',
            'payment_method_id.required' => 'Please select a payment method for subscription.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'billing_cycle.in' => 'Invalid billing cycle selected.',
            'auto_renew.required' => 'Please specify auto-renewal preference.',
        ],
        'reports' => [
            'type.required' => 'Please specify the report type.',
            'type.in' => 'Invalid report type selected.',
            'description.required' => 'Please describe the issue you are reporting.',
            'description.min' => 'The description must be at least :min characters.',
            'evidence.*.file' => 'Each piece of evidence must be a valid file.',
            'evidence.*.mimes' => 'Evidence must be in PDF or image format.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'name',
        'email' => 'email address',
        'phone' => 'phone number',
        'password' => 'password',
        'address' => 'address',
        'zip_code' => 'ZIP code',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
        'accepted_terms' => 'terms and conditions',
        'biography' => 'biography',
        'profile_photo' => 'profile photo',
        'profile_video' => 'profile video',
        'verification_documents' => 'verification documents',
        'title' => 'title',
        'description' => 'description',
        'budget' => 'budget',
        'category_id' => 'category',
        'service_type' => 'service type',
        'rating' => 'rating',
        'comment' => 'comment',
        'amount' => 'amount',
        'payment_method_id' => 'payment method',
        'transaction_id' => 'transaction reference',
        'currency' => 'currency',
        'experience_level' => 'experience level',
        'certifications' => 'certifications',
        'plan_name' => 'subscription plan',
        'billing_cycle' => 'billing cycle',
        'auto_renew' => 'auto-renewal',
    ],

];
