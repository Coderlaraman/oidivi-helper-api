<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linhas de Idioma para Autenticação
    |--------------------------------------------------------------------------
    |
    | As seguintes linhas de idioma são usadas durante a autenticação
    | para exibir várias mensagens ao usuário. Sinta-se à vontade para modificar
    | estas linhas de acordo com os requisitos da sua aplicação.
    |
    */

    'failed' => 'Estas credenciais не correspondem aos nossos registros.',
    'password' => 'A senha fornecida está incorreta.',
    'throttle' => 'Muitas tentativas de login. Por favor, tente novamente em :seconds segundos.',

    // Login
    'login' => [
        'success' => 'Login realizado com sucesso.',
        'failed' => 'Credenciais inválidas.',
        'locked' => 'Sua conta foi bloqueada após várias tentativas falhas. Por favor, tente novamente mais tarde.',
        'inactive' => 'Sua conta está inativa. Por favor, entre em contato com o suporte.',
        'email_not_verified' => 'Seu endereço de e-mail não foi verificado. Por favor, verifique sua caixa de entrada.',
        'session_expired' => 'Sua sessão expirou. Por favor, faça login novamente.',
    ],

    // Logout
    'logout' => [
        'success' => 'Logout realizado com sucesso.',
        'failed' => 'Falha ao fazer logout. Por favor, tente novamente.',
    ],

    // Registro
    'register' => [
        'success' => 'Registro realizado com sucesso. Por favor, verifique seu e-mail para confirmação.',
        'failed' => 'Falha no registro. Por favor, tente novamente.',
        'email_taken' => 'Este endereço de e-mail já está registrado.',
        'username_taken' => 'Este nome de usuário já está em uso.',
    ],

    // Verificação de e-mail
    'verification' => [
        'sent' => 'E-mail de verificação enviado com sucesso.',
        'success' => 'Seu e-mail foi verificado com sucesso.',
        'failed' => 'Falha na verificação do e-mail.',
        'invalid_token' => 'Token de verificação inválido.',
        'already_verified' => 'Seu e-mail já foi verificado.',
    ],

    // Redefinição de senha
    'password_reset' => [
        'link_sent' => 'Link de redefinição de senha enviado com sucesso.',
        'link_failed' => 'Falha ao enviar o link de redefinição de senha.',
        'success' => 'Sua senha foi redefinida com sucesso.',
        'failed' => 'Falha ao redefinir a senha.',
        'invalid_token' => 'Token de redefinição de senha inválido.',
        'expired_token' => 'O token de redefinição de senha expirou.',
    ],

    // Autenticação de dois fatores (2FA)
    '2fa' => [
        'enabled' => 'Autenticação de dois fatores ativada com sucesso.',
        'disabled' => 'Autenticação de dois fatores desativada com sucesso.',
        'failed' => 'Código de autenticação inválido.',
        'sent' => 'Código de autenticação enviado com sucesso.',
        'invalid_code' => 'O código de autenticação fornecido está incorreto.',
    ],

    // Gerenciamento da conta
    'account' => [
        'updated' => 'Informações da conta atualizadas com sucesso.',
        'deleted' => 'Conta excluída com sucesso.',
        'deletion_failed' => 'Falha ao excluir a conta.',
        'password_updated' => 'Senha atualizada com sucesso.',
        'password_incorrect' => 'A senha atual está incorreta.',
    ],

    // Autenticação social
    'social' => [
        'login_success' => 'Login com :provider realizado com sucesso.',
        'login_failed' => 'Falha na autenticação com :provider.',
        'account_not_linked' => 'Nenhuma conta vinculada a esta conta :provider.',
        'link_success' => 'Conta :provider vinculada com sucesso.',
        'unlink_success' => 'Conta :provider desvinculada com sucesso.',
    ],

    // Erros gerais de autenticação
    'unauthorized' => 'Você não tem autorização para realizar esta ação.',
    'forbidden' => 'Acesso negado.',
    'session_invalid' => 'Sessão inválida. Por favor, faça login novamente.',
    'server_error' => 'Ocorreu um erro de autenticação. Por favor, tente novamente mais tarde.',

];
