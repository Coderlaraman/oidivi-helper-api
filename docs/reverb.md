# Integración de Laravel Reverb con Next.js

## Introducción

En este documento, exploraremos cómo integrar Laravel Reverb en una aplicación Next.js. A diferencia de un enfoque monolítico donde el frontend y backend están en el mismo repositorio, aquí demostraremos cómo implementar esta funcionalidad en una arquitectura desacoplada.

## Requisitos Previos

- Next.js 14
- Laravel 11
- Laravel Echo
- Pusher.js
- Laravel Breeze (opcional, para autenticación)

## Configuración Inicial

### 1. Instalación de Dependencias

En el directorio raíz de la aplicación Next.js, ejecuta:

```bash
npm install laravel-echo pusher-js
```

### 2. Configuración del Backend (Laravel)

1. Instala el paquete de broadcasting de Laravel:

```bash
php artisan install:broadcasting
```

2. Configura el archivo `.env`:

```env
BROADCAST_DRIVER=reverb
```

3. Configura CORS en `config/cors.php`:

```php
'allowed_origins' => ['http://localhost:3000'],
'supports_credentials' => true,
```

### 3. Configuración de Autorización de Canales Privados

1. En el archivo `bootstrap/app.php`, configura el middleware para canales de broadcasting:

```php
$app->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    channels: __DIR__.'/../routes/channels.php',
);

$app->withBroadcasting();
```

2. En el archivo `routes/channels.php`, define los canales privados:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

3. En la configuración de Echo en Next.js, agrega la función autorizadora:

```javascript
const echo = new Echo({
  broadcaster: "pusher",
  key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
  wsPort: process.env.NEXT_PUBLIC_REVERB_PORT,
  forceTLS: false,
  enabledTransports: ["ws", "wss"],
  authorizer: (channel, options) => {
    return {
      authorize: (socketId, callback) => {
        axios
          .post("/api/broadcasting/auth", {
            socket_id: socketId,
            channel_name: channel.name,
          })
          .then((response) => {
            callback(false, response.data);
          })
          .catch((error) => {
            callback(true, error);
          });
      },
    };
  },
});
```

### 4. Configuración del Frontend (Next.js)

1. Crea un archivo `.env.local` con las siguientes variables:

```env
NEXT_PUBLIC_BROADCAST_DRIVER=reverb
NEXT_PUBLIC_REVERB_APP_ID=your_app_id
NEXT_PUBLIC_REVERB_APP_KEY=your_app_key
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
```

2. Crea un archivo `echo.js` en el directorio `app`:

```javascript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

const echo = new Echo({
  broadcaster: "pusher",
  key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
  wsPort: process.env.NEXT_PUBLIC_REVERB_PORT,
  forceTLS: false,
  enabledTransports: ["ws", "wss"],
});
```

## Implementación

### 1. Creación del Hook Personalizado

Crea un archivo `hooks/useEcho.js`:

```javascript
import { useState, useEffect } from "react";
import { echo } from "../app/echo";

export function useEcho() {
  const [echoInstance, setEchoInstance] = useState(null);

  useEffect(() => {
    setEchoInstance(echo);
  }, []);

  return echoInstance;
}
```

### 2. Configuración de Autenticación

1. En el modelo `User` de Laravel:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

2. En el controlador de autenticación:

```php
public function store(Request $request)
{
    $request->authenticate();
    $request->session()->regenerate();

    $token = $request->user()->createToken('API token')->plainTextToken;

    return response()->json(['token' => $token])
        ->withCookie('token', $token, 60);
}
```

### 3. Implementación del Evento

1. Crea el evento en Laravel:

```bash
php artisan make:event MessageSent
```

2. Implementa el evento:

```php
class MessageSent implements ShouldBroadcast
{
    public $receiver;
    public $sender;
    public $message;

    public function __construct($receiver, $sender, $message)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->receiver->id);
    }
}
```

### 4. Uso en el Frontend

```javascript
import { useEcho } from '../hooks/useEcho';

function Navigation() {
    const echo = useEcho();
    const [unreadMessages, setUnreadMessages] = useState(0);

    useEffect(() => {
        if (echo) {
            echo.private(`chat.${user.id}`)
                .listen('MessageSent', (event) => {
                    setUnreadMessages(prev => prev + 1);
                    // Reproducir sonido y animación
                });
        }
    }, [echo]);

    return (
        // Componente de navegación
    );
}
```

## Ejecución

1. Inicia el servidor Reverb:

```bash
php artisan reverb:start --debug
```

2. Inicia el worker de colas:

```bash
php artisan queue:listen
```

3. Inicia el servidor Next.js:

```bash
npm run dev
```

## Consideraciones Finales

- Este enfoque funciona con cualquier framework frontend que pueda instalar paquetes npm
- La arquitectura desacoplada requiere más configuración que el enfoque monolítico
- Asegúrate de manejar correctamente los tokens de autenticación
- Considera implementar reconexión automática en caso de desconexión

## Recursos Adicionales

- [Documentación de Laravel Reverb](https://laravel.com/docs/reverb)
- [Documentación de Laravel Echo](https://laravel.com/docs/echo)
- [Documentación de Next.js](https://nextjs.org/docs)
