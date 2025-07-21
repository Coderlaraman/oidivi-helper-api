<?php

return [
    // General
    'bad_request' => 'Invalid request.',
    'dashboard_data_retrieved' => 'Dashboard data retrieved successfully.',
    'error' => 'An error has occurred.',
    'file_too_large' => 'The file is too large.',
    'forbidden' => 'You do not have permission to access this resource.',
    'general_error' => 'An error has occurred.',
    'internal_error' => 'Internal server error.',
    'invalid_credentials' => 'The provided credentials are incorrect.',
    'invalid_current_password' => 'The current password is incorrect.',
    'invalid_file_provided' => 'Invalid file provided.',
    'invalid_file_type' => 'The file is not valid or its content does not match the expected type.',
    'login_successful' => 'Login successful.',
    'logout_successful' => 'Logout successful.',
    'must_accept_terms' => 'You must accept the terms and conditions.',
    'not_found' => 'Resource not found.',
    'password_changed_successfully' => 'Password changed successfully.',
    'password_reset_failed' => 'Error resetting the password.',
    'password_reset_successful' => 'Password reset successfully.',
    'profile_photo_deleted' => 'Profile photo deleted successfully.',
    'profile_photo_updated' => 'Profile photo updated successfully.',
    'profile_updated' => 'Profile updated successfully.',
    'profile_video_updated' => 'Profile video updated successfully.',
    'profile_video_deleted' => 'Profile video deleted successfully.',
    'reset_link_failed' => 'Error sending the password reset link.',
    'reset_link_sent' => 'Password reset link sent successfully.',
    'success' => 'Operation completed successfully.',
    'unauthorized' => 'Not authorized to perform this action.',
    'user_data_retrieved' => 'User data retrieved successfully.',
    'user_inactive' => 'The user is inactive.',
    'user_registered_successfully' => 'User registered successfully.',
    'validation_error' => 'Validation error.',

    // Auth
    'auth' => [
        'account_locked' => 'Account locked due to multiple failed attempts. Try again later.',
        'email_verified' => 'Email successfully verified.',
        'login_failed' => 'Invalid credentials.',
        'login_success' => 'Login successful.',
        'logout_success' => 'Logout successful.',
        'register_success' => 'Registration successful. Please verify your email.',
        'session_expired' => 'Session expired. Please log in again.',
        'verification_failed' => 'Error sending verification email.',
        'verification_sent' => 'Verification email sent.',
    ],

    // Categories
    'categories' => [
        'create_success' => 'Category created successfully.',
        'delete_success' => 'Category deleted successfully.',
        'list_success' => 'Categories retrieved successfully.',
        'not_found' => 'Category not found.',
        'show_success' => 'Category retrieved successfully.',
        'update_success' => 'Category updated successfully.',
    ],

    // Contracts
    'contracts' => [
        // Se eliminan las claves redundantes de nivel superior 
        // como 'created' y 'updated'. Se mantienen 'canceled' y 'completed'
        // si se usan como mensajes de estado simples y no como respuesta de una acción.
        'canceled' => 'Contract canceled.',
        'completed' => 'Contract marked as completed.',

        'success' => [
            'retrieved' => 'Contracts retrieved successfully.',
            'show' => 'Contract retrieved successfully.',
            'created' => 'Contract created successfully.', // Unificado
            'updated' => 'Contract updated successfully.', // Unificado
            'deleted' => 'Contract deleted successfully.',
        ],

        'errors' => [
            'not_found' => 'Contract not found.', // ¡Corregido!
            'service_offer_not_found' => 'Service offer not found for this contract.', // Clave más específica
            'retrieval_failed' => 'Error retrieving contracts.',
            'show_failed' => 'Error retrieving contract.',
            'create_failed' => 'Error creating contract.',
            'update_failed' => 'Error updating contract.',
            'delete_failed' => 'Error deleting contract.',
            'validation_failed' => 'Validation failed.',
            'invalid_status' => 'Invalid contract status.',
            'already_exists' => 'A contract already exists for this service offer.',
            'must_be_accepted' => 'The service offer must be accepted before creating a contract.',
            'unauthorized' => 'Unauthorized to perform this action.',
            'create_unauthorized' => 'Unauthorized to create a contract for this service offer.',
            'delete_unauthorized' => 'Unauthorized to delete this contract.',
            'delete_time_expired' => 'Contract cannot be deleted after :hours hours from creation.',
            'delete_status_invalid' => 'Only pending or canceled contracts can be deleted.',
        ],
    ],

    // Dashboard
    'dashboard' => [
        'data_retrieved' => 'Dashboard data retrieved successfully.',
    ],

    // Notifications
    'notifications' => [
        'deleted' => 'Notification deleted.',
        'read' => 'Notification marked as read.',
        'sent' => 'Notification sent successfully.',
    ],

    // Offers
    'offers' => [
        'accepted' => 'Offer accepted.',
        'created' => 'Offer submitted successfully.',
        'deleted' => 'Offer deleted.',
        'rejected' => 'Offer rejected.',
        'updated' => 'Offer updated.',
    ],

    // Payments
    'payments' => [
        'confirmed' => 'Payment confirmed successfully.',
        'confirmation_failed' => 'Error confirming payment.',
        'failed' => 'Error processing payment.',
        'insufficient_funds' => 'Insufficient funds to complete the payment.',
        'intent_created' => 'Payment intent created successfully.',
        'processed' => 'Payment processed successfully.',
        'refunded' => 'Refund processed successfully.',
        'transaction_not_found' => 'Transaction not found.',
    ],

    // Profile
    'profile' => [
        'bio_updated' => 'Biography updated.',
        'data_retrieved' => 'Profile data retrieved successfully.',
        'photo_deleted' => 'Profile photo deleted.',
        'photo_updated' => 'Profile photo updated.',
        'settings_updated' => 'Settings updated successfully.',
        'skill_setup_required' => 'You must complete your skills to receive relevant notifications and apply for requests.',
        'skills_updated' => 'Skills updated successfully.',
        'updated' => 'Profile updated successfully.',
        'users_retrieved' => 'Users retrieved successfully.',
        'video_updated' => 'Profile video updated.',
    ],

    // Profile Photo
    'profile_photo' => [
        'image' => 'The file must be a valid image.',
        'invalid' => 'Invalid file provided.',
        'max' => 'The maximum allowed image size is 2 MB.',
        'mimes' => 'The image must be in JPEG, PNG, JPG, or GIF format.',
        'required' => 'A profile photo is required.',
    ],

    // Reviews
    'reviews' => [
        'created' => 'Review submitted successfully.',
        'deleted' => 'Review deleted.',
        'not_allowed' => 'You cannot rate this service.',
        'updated' => 'Review updated.',
    ],

    // Service Offers
    'service_offers' => [
        'errors' => [
            'skills_required' => 'Compatible skills required.',
            'creation_failed' => 'Service offer creation failed.',
            'unauthorized' => 'Not authorized to perform this action.',
            'update_failed' => 'Service offer update failed.',
        ],
        'notifications' => [
            'new_offer_title' => 'New service offer.',
            'new_offer_message' => 'You have received a new offer for :title.',
            'status_update_title' => 'Status update of the offer.',
            'status_update_message' => 'The status of your offer for :title has been updated to :status.',
        ],
        'success' => [
            'created' => 'Service offer created successfully.',
            'updated' => 'Service offer updated successfully.',
        ],
    ],

    // Service Requests
    'service_requests' => [
        'already_assigned' => 'The request has already been assigned to a provider.',
        'created' => 'Service request created successfully.',
        'deleted' => 'Service request deleted.',
        'not_found' => 'Service request not found.',
        'status_updated' => 'Service request status updated.',
        'updated' => 'Service request updated.',
    ],

    // Subscriptions
    'subscriptions' => [
        'activated' => 'Subscription activated successfully.',
        'canceled' => 'Subscription canceled successfully.',
        'expired' => 'Subscription expired.',
        'renewed' => 'Subscription renewed automatically.',
        'created' => 'Subscription created successfully.',
        'create_error' => 'Error creating subscription.',
        'list_success' => 'Subscriptions retrieved successfully.',
        'list_error' => 'Error retrieving subscriptions.',
        'show_success' => 'Subscription retrieved successfully.',
        'show_error' => 'Error retrieving subscription.',
        'cancel_error' => 'Error canceling subscription.',
    ],

    // Tickets
    'tickets' => [
        'closed' => 'Ticket closed.',
        'created' => 'Support ticket created successfully.',
        'create_error' => 'Error creating ticket.',
        'list_success' => 'Tickets retrieved successfully.',
        'list_error' => 'Error retrieving tickets.',
        'show_success' => 'Ticket retrieved successfully.',
        'show_error' => 'Error retrieving ticket.',
        'reply_sent' => 'Reply sent successfully.',
        'reply_error' => 'Error sending reply.',
        'updated' => 'Ticket updated.',
    ],

    // Referrals
    'referrals' => [
        'accepted' => 'Referral accepted successfully.',
        'accept_error' => 'Error accepting referral.',
        'created' => 'Referral created successfully.',
        'create_error' => 'Error creating referral.',
        'deleted' => 'Referral deleted successfully.',
        'delete_error' => 'Error deleting referral.',
        'list_error' => 'Error retrieving referrals.',
        'list_success' => 'Referrals retrieved successfully.',
        'show_error' => 'Error retrieving referral.',
        'show_success' => 'Referral retrieved successfully.',
    ],

    // Reports
    'reports' => [
        'created' => 'Report submitted successfully.',
        'create_error' => 'Error submitting report.',
        'list_error' => 'Error retrieving reports.',
        'list_success' => 'Reports retrieved successfully.',
        'show_error' => 'Error retrieving report details.',
        'show_success' => 'Report details retrieved successfully.',
        'unauthorized' => 'You are not authorized to view this report.',
    ],
];
