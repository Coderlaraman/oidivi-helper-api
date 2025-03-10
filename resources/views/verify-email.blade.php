<!DOCTYPE html>
<html>

<head>
    <title>Verify your email address</title>
</head>

<body>
    <p>Hi {{ $user->name }},</p>
    <p>Thank you for registering. Please click the link below to verify your email address:</p>
    <p><a href="{{ url('/api/v1/client/verify-email/' . $user->email_verification_token) }}">Verify Email</a></p>
    <p>Thank you!</p>
</body>

</html>
