<?php

namespace App\Constants;

class Permissions
{
    // Permisos Generales
    public const VIEW_SERVICE_REQUESTS = 'view_service_requests';
    public const CREATE_SERVICE_REQUEST = 'create_service_request';
    public const UPDATE_OWN_PROFILE = 'update_own_profile';
    public const SEND_MESSAGES = 'send_messages';
    public const MAKE_PAYMENTS = 'make_payments';
    public const RATE_USERS = 'rate_users';
    public const VIEW_USER_PROFILES = 'view_user_profiles';

    // Permisos de Usuario
    public const ACCEPT_SERVICE_REQUEST = 'accept_service_request';
    public const CANCEL_SERVICE_REQUEST = 'cancel_service_request';
    public const UPDATE_OWN_SERVICE_REQUEST = 'update_own_service_request';
    public const DELETE_OWN_SERVICE_REQUEST = 'delete_own_service_request';
    public const TRACK_SERVICE_STATUS = 'track_service_status';

    // Permisos de Administrador
    public const MANAGE_USERS = 'manage_users';
    public const ASSIGN_ROLES = 'assign_roles';
    public const VIEW_REPORTS = 'view_reports';
    public const DELETE_ANY_SERVICE_REQUEST = 'delete_any_service_request';
    public const MANAGE_PAYMENTS = 'manage_payments';
    public const ACCESS_ADMIN_DASHBOARD = 'access_admin_dashboard';

    // Permisos de Moderador
    public const SUSPEND_USERS = 'suspend_users';
    public const EDIT_ANY_SERVICE_REQUEST = 'edit_any_service_request';
    public const RESOLVE_DISPUTES = 'resolve_disputes';

    // Permisos de Soporte
    public const ASSIST_USERS = 'assist_users';
    public const RESOLVE_TICKETS = 'resolve_tickets';
    public const MODERATE_CONTENT = 'moderate_content';
} 