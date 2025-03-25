<?php

return [
    'must_accept_terms' => 'You must accept the terms and conditions.',
    'user_registered_successfully' => 'User registered successfully.',
    'invalid_credentials' => 'The provided credentials are incorrect.',
    'user_inactive' => 'The user is inactive.',
    'login_successful' => 'Login successful.',
    'logout_successful' => 'Logout successful.',
    'profile_updated_successfully' => 'Profile updated successfully.',
    'invalid_current_password' => 'The current password is incorrect.',
    'password_changed_successfully' => 'Password changed successfully.',
    'reset_link_sent' => 'Password reset link sent successfully.',
    'reset_link_failed' => 'Error sending the password reset link.',
    'password_reset_successful' => 'Password reset successfully.',
    'password_reset_failed' => 'Error resetting the password.',

    // Auth
    'auth' => [
        'login_success' => 'Login successful.',
        'login_failed' => 'Invalid credentials.',
        'logout_success' => 'Session closed successfully.',
        'register_success' => 'Registration successful. Please verify your email.',
        'email_verified' => 'Email successfully verified.',
        'verification_sent' => 'Verification email sent.',
        'verification_failed' => 'Error sending verification email.',
        'account_locked' => 'Account locked due to multiple failed attempts. Try again later.',
        'session_expired' => 'Session expired, please log in again.',
    ],

    // Profile
    'profile' => [
        'updated' => 'Profile updated successfully.',
        'photo_updated' => 'Profile photo updated.',
        'photo_deleted' => 'Profile photo deleted.',
        'bio_updated' => 'Biography updated.',
        'settings_updated' => 'Settings updated successfully.',
    ],

    // Profile Photo
    'profile_photo' => [
        'required' => 'A profile photo is required.',
        'image' => 'The file must be a valid image.',
        'mimes' => 'The image must be in JPEG, PNG, JPG, or GIF format.',
        'max' => 'The maximum allowed image size is 2 MB.',
    ],

    // Service Requests
    'service_requests' => [
        'created' => 'Service request created successfully.',
        'updated' => 'Service request updated.',
        'deleted' => 'Service request deleted.',
        'status_updated' => 'Service request status updated.',
        'not_found' => 'Service request not found.',
        'already_assigned' => 'The request has already been assigned to a provider.',
    ],

    // Offers
    'offers' => [
        'created' => 'Offer submitted successfully.',
        'updated' => 'Offer updated.',
        'deleted' => 'Offer deleted.',
        'accepted' => 'Offer accepted.',
        'rejected' => 'Offer rejected.',
    ],

    // Contracts
    'contracts' => [
        'created' => 'Contract generated successfully.',
        'updated' => 'Contract updated.',
        'canceled' => 'Contract canceled.',
        'completed' => 'Contract marked as completed.',
    ],

    // Reviews
    'reviews' => [
        'created' => 'Review submitted successfully.',
        'updated' => 'Review updated.',
        'deleted' => 'Review deleted.',
        'not_allowed' => 'You cannot rate this service.',
    ],

    // Payments
    'payments' => [
        'processed' => 'Payment processed successfully.',
        'failed' => 'Error processing payment.',
        'refunded' => 'Refund processed successfully.',
        'insufficient_funds' => 'Insufficient funds to complete the payment.',
    ],

    // Subscriptions
    'subscriptions' => [
        'activated' => 'Subscription activated successfully.',
        'canceled' => 'Subscription canceled.',
        'renewed' => 'Subscription renewed automatically.',
        'expired' => 'Subscription expired.',
    ],

    // Notifications
    'notifications' => [
        'sent' => 'Notification sent successfully.',
        'read' => 'Notification marked as read.',
        'deleted' => 'Notification deleted.',
    ],

    // Tickets (Support)
    'tickets' => [
        'created' => 'Support ticket created successfully.',
        'updated' => 'Ticket updated.',
        'closed' => 'Ticket closed.',
        'reply_sent' => 'Reply sent successfully.',
    ],

    // General
    'success' => 'Operation completed successfully.',
    'error' => 'An error has occurred.',
    'unauthorized' => 'Not authorized to perform this action.',
    'forbidden' => 'You do not have permission to access this resource.',
    'not_found' => 'Resource not found.',
    'bad_request' => 'Invalid request.',
    'internal_error' => 'Internal server error.',

    // Categories
    'categories' => [
        'list_success' => 'Categories retrieved successfully.',
        'show_success' => 'Category retrieved successfully.',
        'create_success' => 'Category created successfully.',
        'update_success' => 'Category updated successfully.',
        'delete_success' => 'Category deleted successfully.',
        'not_found' => 'Category not found.',
    ],
];
