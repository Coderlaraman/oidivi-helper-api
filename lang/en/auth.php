<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. Feel free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Login
    'login' => [
        'success' => 'Login successful.',
        'failed' => 'Invalid credentials.',
        'locked' => 'Your account is locked due to multiple failed attempts. Try again later.',
        'inactive' => 'Your account is inactive. Please contact support.',
        'email_not_verified' => 'Your email is not verified. Please check your inbox.',
        'session_expired' => 'Your session has expired. Please log in again.',
    ],

    // Logout
    'logout' => [
        'success' => 'You have been logged out successfully.',
        'failed' => 'Logout failed. Please try again.',
    ],

    // Registration
    'register' => [
        'success' => 'Registration successful. Please check your email for verification.',
        'failed' => 'Registration failed. Please try again.',
        'email_taken' => 'This email is already registered.',
        'username_taken' => 'This username is already in use.',
    ],

    // Email Verification
    'verification' => [
        'sent' => 'Verification email sent successfully.',
        'success' => 'Your email has been verified successfully.',
        'failed' => 'Email verification failed.',
        'invalid_token' => 'Invalid verification token.',
        'already_verified' => 'Your email is already verified.',
    ],

    // Password Reset
    'password_reset' => [
        'link_sent' => 'Password reset link sent successfully.',
        'link_failed' => 'Failed to send password reset link.',
        'success' => 'Your password has been reset successfully.',
        'failed' => 'Password reset failed.',
        'invalid_token' => 'Invalid password reset token.',
        'expired_token' => 'Password reset token has expired.',
    ],

    // Two-Factor Authentication (2FA)
    '2fa' => [
        'enabled' => 'Two-factor authentication enabled successfully.',
        'disabled' => 'Two-factor authentication disabled successfully.',
        'failed' => 'Invalid two-factor authentication code.',
        'sent' => 'Two-factor authentication code sent successfully.',
        'invalid_code' => 'The provided authentication code is incorrect.',
    ],

    // Account Management
    'account' => [
        'updated' => 'Account information updated successfully.',
        'deleted' => 'Account deleted successfully.',
        'deletion_failed' => 'Account deletion failed.',
        'password_updated' => 'Password updated successfully.',
        'password_incorrect' => 'Current password is incorrect.',
    ],

    // Social Authentication
    'social' => [
        'login_success' => 'Logged in successfully using :provider.',
        'login_failed' => 'Failed to authenticate with :provider.',
        'account_not_linked' => 'No account is linked with this :provider account.',
        'link_success' => ':provider account linked successfully.',
        'unlink_success' => ':provider account unlinked successfully.',
    ],

    // General Authentication Errors
    'unauthorized' => 'You are not authorized to perform this action.',
    'forbidden' => 'Access denied.',
    'session_invalid' => 'Invalid session. Please log in again.',
    'server_error' => 'An authentication error occurred. Please try again later.',

];
