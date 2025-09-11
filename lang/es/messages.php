<?php

return [
    // General
    'bad_request' => 'Solicitud inválida.',
    'dashboard_data_retrieved' => 'Datos del panel de control recuperados exitosamente.',
    'error' => 'Ha ocurrido un error.',
    'file_too_large' => 'El archivo es demasiado grande.',
    'forbidden' => 'No tienes permiso para acceder a este recurso.',
    'general_error' => 'Ha ocurrido un error.',
    'internal_error' => 'Error interno del servidor.',
    'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
    'invalid_current_password' => 'La contraseña actual es incorrecta.',
    'invalid_file_provided' => 'Archivo proporcionado inválido.',
    'invalid_file_type' => 'El archivo no es válido o su contenido no coincide con el tipo esperado.',
    'login_successful' => 'Inicio de sesión exitoso.',
    'logout_successful' => 'Cierre de sesión exitoso.',
    'must_accept_terms' => 'Debes aceptar los términos y condiciones.',
    'not_found' => 'Recurso no encontrado.',
    'password_changed_successfully' => 'Contraseña cambiada exitosamente.',
    'password_reset_failed' => 'Error al restablecer la contraseña.',
    'password_reset_successful' => 'Contraseña restablecida exitosamente.',
    'profile_photo_deleted' => 'Foto de perfil eliminada exitosamente.',
    'profile_photo_updated' => 'Foto de perfil actualizada exitosamente.',
    'profile_updated' => 'Perfil actualizado exitosamente.',
    'profile_video_updated' => 'Video de perfil actualizado exitosamente.',
    'profile_video_deleted' => 'Video de perfil eliminado exitosamente.',
    'reset_link_failed' => 'Error al enviar el enlace de restablecimiento de contraseña.',
    'reset_link_sent' => 'Enlace de restablecimiento de contraseña enviado exitosamente.',
    'success' => 'Operación completada exitosamente.',
    'unauthorized' => 'No autorizado para realizar esta acción.',
    'user_data_retrieved' => 'Datos del usuario recuperados exitosamente.',
    'user_inactive' => 'El usuario está inactivo.',
    'user_registered_successfully' => 'Usuario registrado exitosamente.',
    'validation_error' => 'Error de validación.',

    // Auth
    'auth' => [
        'account_locked' => 'Cuenta bloqueada debido a múltiples intentos fallidos. Intenta nuevamente más tarde.',
        'email_verified' => 'Correo electrónico verificado exitosamente.',
        'login_failed' => 'Credenciales inválidas.',
        'login_success' => 'Inicio de sesión exitoso.',
        'logout_success' => 'Cierre de sesión exitoso.',
        'register_success' => 'Registro exitoso. Por favor verifica tu correo electrónico.',
        'session_expired' => 'Sesión expirada. Por favor inicia sesión nuevamente.',
        'verification_failed' => 'Error al enviar el correo de verificación.',
        'verification_sent' => 'Correo de verificación enviado.',
    ],

    // Categories
    'categories' => [
        'create_success' => 'Categoría creada exitosamente.',
        'delete_success' => 'Categoría eliminada exitosamente.',
        'list_success' => 'Categorías recuperadas exitosamente.',
        'not_found' => 'Categoría no encontrada.',
        'show_success' => 'Categoría recuperada exitosamente.',
        'update_success' => 'Categoría actualizada exitosamente.',
    ],

    // Contracts
    'contracts' => [
        'canceled' => 'Contrato cancelado.',
        'completed' => 'Contrato marcado como completado.',
        'created' => 'Contrato generado exitosamente.',
        'updated' => 'Contrato actualizado.',
        
        'sent_success' => 'Contrato enviado exitosamente.',
        'send_error' => 'Error al enviar el contrato.',
        'unauthorized_send' => 'No estás autorizado para enviar este contrato.',
        'cannot_send' => 'No es posible enviar este contrato en su estado actual.',

        'accepted_success' => 'Contrato aceptado exitosamente.',
        'accept_error' => 'Error al aceptar el contrato.',
        'unauthorized_accept' => 'No estás autorizado para aceptar este contrato.',
        'cannot_accept' => 'No es posible aceptar este contrato en su estado actual.',

        'rejected_success' => 'Contrato rechazado exitosamente.',
        'reject_error' => 'Error al rechazar el contrato.',
        'unauthorized_reject' => 'No estás autorizado para rechazar este contrato.',
        'cannot_reject' => 'No es posible rechazar este contrato en su estado actual.',

        'updated_success' => 'Contrato actualizado exitosamente.',
        'update_error' => 'Error al actualizar el contrato.',
    ],

    // Stripe Connect
    'connect' => [
        'only_helpers' => 'Solo los helpers pueden realizar esta acción.',
        'onboarding_error' => 'No se pudo iniciar el onboarding de Stripe Connect.',
        'status_error' => 'No se pudo obtener el estado de la cuenta de Stripe.',
        'refresh_error' => 'No se pudo refrescar el enlace de onboarding.',
        'gated_accept' => 'Debes completar el onboarding de pagos antes de aceptar contratos.',
    ],

    // Dashboard
    'dashboard' => [
        'data_retrieved' => 'Datos del panel de control recuperados exitosamente.',
    ],

    // Notifications
    'notifications' => [
        'deleted' => 'Notificación eliminada.',
        'read' => 'Notificación marcada como leída.',
        'sent' => 'Notificación enviada exitosamente.',
    ],

    // Offers
    'offers' => [
        'accepted' => 'Oferta aceptada.',
        'created' => 'Oferta enviada exitosamente.',
        'deleted' => 'Oferta eliminada.',
        'rejected' => 'Oferta rechazada.',
        'updated' => 'Oferta actualizada.',
    ],

    // Payments
    'payments' => [
        'confirmed' => 'Pago confirmado exitosamente.',
        'confirmation_failed' => 'Error al confirmar el pago.',
        'failed' => 'Error al procesar el pago.',
        'insufficient_funds' => 'Fondos insuficientes para completar el pago.',
        'intent_created' => 'Intención de pago creada exitosamente.',
        'processed' => 'Pago procesado exitosamente.',
        'refunded' => 'Reembolso procesado exitosamente.',
        'transaction_not_found' => 'Transacción no encontrada.',
    ],

    // Profile
    'profile' => [
        'bio_updated' => 'Biografía actualizada.',
        'data_retrieved' => 'Datos del perfil recuperados exitosamente.',
        'photo_deleted' => 'Foto de perfil eliminada.',
        'photo_updated' => 'Foto de perfil actualizada.',
        'settings_updated' => 'Configuración actualizada exitosamente.',
        'skill_setup_required' => 'Debes completar tus habilidades para recibir notificaciones relevantes y aplicar a solicitudes.',
        'skills_updated' => 'Habilidades actualizadas exitosamente.',
        'updated' => 'Perfil actualizado exitosamente.',
        'users_retrieved' => 'Usuarios recuperados exitosamente.',
        'video_updated' => 'Video de perfil actualizado.',
    ],

    // Profile Photo
    'profile_photo' => [
        'image' => 'El archivo debe ser una imagen válida.',
        'invalid' => 'Archivo proporcionado inválido.',
        'max' => 'El tamaño máximo permitido para la imagen es 2 MB.',
        'mimes' => 'La imagen debe estar en formato JPEG, PNG, JPG o GIF.',
        'required' => 'Se requiere una foto de perfil.',
    ],

    // Reviews
    'reviews' => [
        'created' => 'Reseña enviada exitosamente.',
        'deleted' => 'Reseña eliminada.',
        'not_allowed' => 'No puedes calificar este servicio.',
        'updated' => 'Reseña actualizada.',
    ],

    // Service Requests
    'service_requests' => [
        'already_assigned' => 'La solicitud ya ha sido asignada a un proveedor.',
        'created' => 'Solicitud de servicio creada exitosamente.',
        'deleted' => 'Solicitud de servicio eliminada.',
        'not_found' => 'Solicitud de servicio no encontrada.',
        'status_updated' => 'Estado de la solicitud de servicio actualizado.',
        'updated' => 'Solicitud de servicio actualizada.',
    ],

    // Subscriptions
    'subscriptions' => [
        'activated' => 'Suscripción activada exitosamente.',
        'canceled' => 'Suscripción cancelada exitosamente.',
        'expired' => 'Suscripción expirada.',
        'renewed' => 'Suscripción renovada automáticamente.',
        'created' => 'Suscripción creada exitosamente.',
        'create_error' => 'Error al crear la suscripción.',
        'list_success' => 'Suscripciones recuperadas exitosamente.',
        'list_error' => 'Error al recuperar las suscripciones.',
        'show_success' => 'Suscripción recuperada exitosamente.',
        'show_error' => 'Error al recuperar la suscripción.',
        'cancel_error' => 'Error al cancelar la suscripción.',
    ],

    // Tickets (Support)
    'tickets' => [
        'closed' => 'Ticket cerrado.',
        'created' => 'Ticket de soporte creado exitosamente.',
        'reply_sent' => 'Respuesta enviada exitosamente.',
        'updated' => 'Ticket actualizado.',
    ],

    // Referrals
    'referrals' => [
        'accepted' => 'Referido aceptado exitosamente.',
        'accept_error' => 'Error al aceptar referido.',
        'created' => 'Referido creado exitosamente.',
        'create_error' => 'Error al crear referido.',
        'deleted' => 'Referido eliminado exitosamente.',
        'delete_error' => 'Error al eliminar referido.',
        'list_error' => 'Error al recuperar referidos.',
        'list_success' => 'Referidos recuperados exitosamente.',
        'show_error' => 'Error al recuperar referido.',
        'show_success' => 'Referido recuperado exitosamente.',
    ],

    // Reports
    'reports' => [
        'created' => 'Reporte enviado exitosamente.',
        'create_error' => 'Error al enviar reporte.',
        'list_error' => 'Error al recuperar reportes.',
        'list_success' => 'Reportes recuperados exitosamente.',
        'show_error' => 'Error al recuperar detalles del reporte.',
        'show_success' => 'Detalles del reporte recuperados exitosamente.',
        'unauthorized' => 'No estás autorizado para ver este reporte.',
    ],

    // Service Offers
    'service_offers' => [
        'errors' => [
            'skills_required' => 'Se requieren habilidades compatibles.',
            'creation_failed' => 'Error al crear la oferta de servicio.',
            'unauthorized' => 'No autorizado para realizar esta acción.',
            'update_failed' => 'Error al actualizar la oferta de servicio.',
        ],
        'notifications' => [
            'new_offer_title' => 'Nueva oferta de servicio.',
            'new_offer_message' => 'Has recibido una nueva oferta para: :title.',
            'status_update_title' => 'Actualización de estado de la oferta.',
            'status_update_message' => 'El estado de tu oferta para :title ha sido actualizado a :status.',
        ],
        'success' => [
            'created' => 'Oferta de servicio creada exitosamente.',
            'updated' => 'Oferta de servicio actualizada exitosamente.',
        ],
    ],
];