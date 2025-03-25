<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de Lenguaje de Autenticación
    |--------------------------------------------------------------------------
    |
    | Estas líneas de lenguaje se utilizan durante la autenticación para
    | mostrar diversos mensajes al usuario. Siéntase libre de modificarlas
    | según los requisitos de su aplicación.
    |
    */

    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Inténtelo de nuevo en :seconds segundos.',

    // Inicio de sesión
    'login' => [
        'success' => 'Inicio de sesión exitoso.',
        'failed' => 'Credenciales inválidas.',
        'locked' => 'Tu cuenta ha sido bloqueada debido a múltiples intentos fallidos. Inténtalo más tarde.',
        'inactive' => 'Tu cuenta está inactiva. Por favor, contacta con soporte.',
        'email_not_verified' => 'Tu correo electrónico no está verificado. Revisa tu bandeja de entrada.',
        'session_expired' => 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.',
    ],

    // Cierre de sesión
    'logout' => [
        'success' => 'Has cerrado sesión exitosamente.',
        'failed' => 'Error al cerrar sesión. Inténtalo de nuevo.',
    ],

    // Registro
    'register' => [
        'success' => 'Registro exitoso. Por favor, revisa tu correo para verificar tu cuenta.',
        'failed' => 'Error en el registro. Inténtalo de nuevo.',
        'email_taken' => 'Este correo ya está registrado.',
        'username_taken' => 'Este nombre de usuario ya está en uso.',
    ],

    // Verificación de correo
    'verification' => [
        'sent' => 'Correo de verificación enviado con éxito.',
        'success' => 'Tu correo ha sido verificado con éxito.',
        'failed' => 'Error en la verificación del correo.',
        'invalid_token' => 'Token de verificación inválido.',
        'already_verified' => 'Tu correo ya está verificado.',
    ],

    // Restablecimiento de contraseña
    'password_reset' => [
        'link_sent' => 'Enlace de restablecimiento de contraseña enviado con éxito.',
        'link_failed' => 'Error al enviar el enlace de restablecimiento de contraseña.',
        'success' => 'Tu contraseña ha sido restablecida exitosamente.',
        'failed' => 'Error al restablecer la contraseña.',
        'invalid_token' => 'Token de restablecimiento de contraseña inválido.',
        'expired_token' => 'El token de restablecimiento ha expirado.',
    ],

    // Autenticación en dos pasos (2FA)
    '2fa' => [
        'enabled' => 'Autenticación en dos pasos activada con éxito.',
        'disabled' => 'Autenticación en dos pasos desactivada con éxito.',
        'failed' => 'Código de autenticación inválido.',
        'sent' => 'Código de autenticación enviado con éxito.',
        'invalid_code' => 'El código de autenticación proporcionado es incorrecto.',
    ],

    // Manejo de cuenta
    'account' => [
        'updated' => 'Información de la cuenta actualizada exitosamente.',
        'deleted' => 'Cuenta eliminada con éxito.',
        'deletion_failed' => 'Error al eliminar la cuenta.',
        'password_updated' => 'Contraseña actualizada exitosamente.',
        'password_incorrect' => 'La contraseña actual es incorrecta.',
    ],

    // Autenticación social
    'social' => [
        'login_success' => 'Inicio de sesión exitoso con :provider.',
        'login_failed' => 'Error al autenticar con :provider.',
        'account_not_linked' => 'No hay una cuenta vinculada a esta cuenta de :provider.',
        'link_success' => 'Cuenta de :provider vinculada exitosamente.',
        'unlink_success' => 'Cuenta de :provider desvinculada exitosamente.',
    ],

    // Errores generales de autenticación
    'unauthorized' => 'No tienes autorización para realizar esta acción.',
    'forbidden' => 'Acceso denegado.',
    'session_invalid' => 'Sesión inválida. Por favor, inicia sesión nuevamente.',
    'server_error' => 'Ha ocurrido un error de autenticación. Por favor, intenta más tarde.',

];
