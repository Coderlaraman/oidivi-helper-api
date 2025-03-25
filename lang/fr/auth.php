<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lignes de Langue pour l'Authentification
    |--------------------------------------------------------------------------
    |
    | Les lignes de langue suivantes sont utilisées lors de l'authentification
    | pour afficher divers messages à l'utilisateur. N'hésitez pas à modifier
    | ces lignes en fonction des exigences de votre application.
    |
    */

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',

    // Connexion
    'login' => [
        'success' => 'Connexion réussie.',
        'failed' => 'Identifiants invalides.',
        'locked' => 'Votre compte est verrouillé après plusieurs tentatives échouées. Veuillez réessayer plus tard.',
        'inactive' => 'Votre compte est inactif. Veuillez contacter le support.',
        'email_not_verified' => 'Votre adresse e-mail n\'est pas vérifiée. Veuillez vérifier votre boîte de réception.',
        'session_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
    ],

    // Déconnexion
    'logout' => [
        'success' => 'Déconnexion réussie.',
        'failed' => 'Échec de la déconnexion. Veuillez réessayer.',
    ],

    // Inscription
    'register' => [
        'success' => 'Inscription réussie. Veuillez vérifier votre e-mail pour confirmation.',
        'failed' => 'Échec de l\'inscription. Veuillez réessayer.',
        'email_taken' => 'Cette adresse e-mail est déjà enregistrée.',
        'username_taken' => 'Ce nom d\'utilisateur est déjà utilisé.',
    ],

    // Vérification d'e-mail
    'verification' => [
        'sent' => 'E-mail de vérification envoyé avec succès.',
        'success' => 'Votre e-mail a été vérifié avec succès.',
        'failed' => 'Échec de la vérification de l\'e-mail.',
        'invalid_token' => 'Jeton de vérification invalide.',
        'already_verified' => 'Votre e-mail est déjà vérifié.',
    ],

    // Réinitialisation du mot de passe
    'password_reset' => [
        'link_sent' => 'Lien de réinitialisation du mot de passe envoyé avec succès.',
        'link_failed' => 'Échec de l\'envoi du lien de réinitialisation du mot de passe.',
        'success' => 'Votre mot de passe a été réinitialisé avec succès.',
        'failed' => 'Échec de la réinitialisation du mot de passe.',
        'invalid_token' => 'Jeton de réinitialisation du mot de passe invalide.',
        'expired_token' => 'Le jeton de réinitialisation du mot de passe a expiré.',
    ],

    // Authentification à deux facteurs (2FA)
    '2fa' => [
        'enabled' => 'Authentification à deux facteurs activée avec succès.',
        'disabled' => 'Authentification à deux facteurs désactivée avec succès.',
        'failed' => 'Code d\'authentification invalide.',
        'sent' => 'Code d\'authentification envoyé avec succès.',
        'invalid_code' => 'Le code d\'authentification fourni est incorrect.',
    ],

    // Gestion du compte
    'account' => [
        'updated' => 'Informations du compte mises à jour avec succès.',
        'deleted' => 'Compte supprimé avec succès.',
        'deletion_failed' => 'Échec de la suppression du compte.',
        'password_updated' => 'Mot de passe mis à jour avec succès.',
        'password_incorrect' => 'Le mot de passe actuel est incorrect.',
    ],

    // Authentification sociale
    'social' => [
        'login_success' => 'Connexion réussie avec :provider.',
        'login_failed' => 'Échec de l\'authentification avec :provider.',
        'account_not_linked' => 'Aucun compte lié à ce compte :provider.',
        'link_success' => 'Compte :provider lié avec succès.',
        'unlink_success' => 'Compte :provider dissocié avec succès.',
    ],

    // Erreurs générales d'authentification
    'unauthorized' => 'Vous n\'êtes pas autorisé à effectuer cette action.',
    'forbidden' => 'Accès refusé.',
    'session_invalid' => 'Session invalide. Veuillez vous reconnecter.',
    'server_error' => 'Une erreur d\'authentification est survenue. Veuillez réessayer plus tard.',

];
