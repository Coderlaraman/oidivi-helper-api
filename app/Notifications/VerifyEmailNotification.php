<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Genera la URL de verificación apuntando al frontend y con todos los parámetros en el query string.
     */
    protected function verificationUrl($notifiable)
    {
        // Generamos la URL firmada original que apunta al backend.
        $backendUrl = URL::temporarySignedRoute(
            'verification.verify',  // Asegúrate de que esta ruta esté definida en el backend
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Parseamos la URL generada para extraer el query y la ruta.
        $parsedUrl = parse_url($backendUrl);

        // Extraemos la query string original (que contiene expires y signature).
        $originalQuery = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

        // Extraemos los segmentos de la ruta para obtener id y hash.
        // Ejemplo: /api/v1/client/email/verify/17/2383d0e6...
        $pathSegments = explode('/', trim($parsedUrl['path'], '/'));
        $count = count($pathSegments);
        $id = $pathSegments[$count - 2] ?? null;
        $hash = $pathSegments[$count - 1] ?? null;

        // Si por alguna razón no se obtuvieron, retornamos la URL original (aunque no debería ocurrir).
        if (!$id || !$hash) {
            Log::debug("No se pudieron extraer 'id' o 'hash' de la URL firmada: " . $backendUrl);
            return $backendUrl;
        }

        // Construimos un nuevo query string que incluya id, hash y la query original.
        $newQuery = http_build_query([
            'id' => $id,
            'hash' => $hash,
        ]);
        if ($originalQuery) {
            $newQuery .= '&' . $originalQuery;
        }

        // Obtenemos la URL del frontend definida en la variable de entorno.
        $frontendUrl = config('app.frontend_url');  // Asegúrate de definir FRONTEND_URL en .env y en config/app.php

        // Construimos la URL final que apunta al frontend con la ruta /verify y todos los parámetros en el query.
        $verificationUrl = rtrim($frontendUrl, '/') . '/verify?' . $newQuery;

        // Registramos la URL generada en los logs para depuración.
        Log::debug('Verification URL generated: ' . $verificationUrl);

        return $verificationUrl;
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }
}
