# Implementación de Notificaciones en Tiempo Real

## Descripción General
Este documento describe la implementación de notificaciones en tiempo real para el sistema de solicitudes de servicio. Las notificaciones se mostrarán a los usuarios que tengan habilidades coincidentes con las categorías de las nuevas solicitudes de servicio.

## Requisitos Previos

### Dependencias
```bash
npm install laravel-echo pusher-js react-hot-toast
```

### Variables de Entorno
Crear o actualizar el archivo `.env.local` con:

```env
NEXT_PUBLIC_APP_NAME="OiDiVi Helper Web"
NEXT_PUBLIC_API_BASE_URL=http://oidivi-helper-api.test

# Laravel Echo Configuration
NEXT_PUBLIC_REVERB_APP_ID=827310
NEXT_PUBLIC_REVERB_APP_KEY=u8cxcnjsj80d9mbgexuh
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=http
```

## Estructura de Archivos

### 1. Configuración de Echo (`@/config/echo.ts`)

```typescript
import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

if (typeof window !== "undefined") {
  window.Pusher = Pusher;
}

const getAuthToken = () => {
  if (typeof window !== "undefined") {
    return localStorage.getItem("user_auth_token");
  }
  return null;
};

const echo = new Echo({
  broadcaster: "reverb",
  key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST,
  wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 80,
  wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 443,
  forceTLS: false,
  enabledTransports: ["ws", "wss"],
  disableStats: true,
  authEndpoint: `${process.env.NEXT_PUBLIC_API_BASE_URL}/broadcasting/auth`,
  auth: {
    headers: {
      Accept: "application/json",
      Authorization: `Bearer ${getAuthToken()}`,
    },
  },
  enableLogging: true,
  reconnectionAttempts: 5,
  reconnectionDelay: 3000
});

// Manejar eventos de conexión globales
echo.connector.pusher.connection.bind('connected', () => {
  console.log('Successfully connected to Reverb.');
});

echo.connector.pusher.connection.bind('disconnected', () => {
  console.log('Disconnected from Reverb.');
});

echo.connector.pusher.connection.bind('error', (err: any) => {
  console.error('Reverb connection error:', err);
});

if (typeof window !== "undefined") {
  window.Echo = echo;
}

export const refreshEchoAuthToken = () => {
  if (echo.connector) {
    echo.connector.options.auth.headers.Authorization = `Bearer ${getAuthToken()}`;
  }
};

export default echo;
```

### 2. Hook de Conexión WebSocket (`@/hooks/useEcho.ts`)

```typescript
import { useEffect, useState } from "react";
import echo, { refreshEchoAuthToken } from "@/config/echo";
import { useUserAuthContext } from "@/contexts/UserAuthContext";
import { EchoError } from "@/lib/types/echo";

export const useEcho = () => {
  const { isAuthenticated, user } = useUserAuthContext();
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    if (isAuthenticated && user) {
      refreshEchoAuthToken();

      const userChannel = echo.private(`user.${user.id}`);
      const notificationChannel = echo.private(`user.notifications.${user.id}`);

      const handleError = (error: EchoError) => {
        console.error("WebSocket error:", error);
        setIsConnected(false);
      };

      const handleSubscribed = (channel: string) => {
        console.log(`Subscribed to ${channel} channel`);
        setIsConnected(true);
      };

      userChannel
        .error(handleError)
        .subscribed(() => handleSubscribed(`user.${user.id}`));

      notificationChannel
        .error(handleError)
        .subscribed(() => handleSubscribed(`user.notifications.${user.id}`));

      echo.connector.pusher.connection.bind('connected', () => {
        console.log('Reconnected to WebSocket server');
        setIsConnected(true);
      });

      echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('Disconnected from WebSocket server');
        setIsConnected(false);
      });

      return () => {
        echo.leave(`private-user.${user.id}`);
        echo.leave(`private-user.notifications.${user.id}`);
        echo.connector.pusher.connection.unbind_all();
      };
    }
  }, [isAuthenticated, user]);

  return { echo, isConnected };
};
```

### 3. Hook de Notificaciones (`@/hooks/useNotifications.ts`)

```typescript
// Ver código completo en la implementación anterior del hook useNotifications
```

### 4. Tipos de Datos (`@/lib/types/echo.ts`)

```typescript
export interface EchoError {
  type: string;
  error: any;
  data?: any;
}

export interface ServiceRequestNotification {
  id: string;
  type: string;
  timestamp: string;
  service_request: {
    id: number;
    title: string;
    slug: string;
    description: string;
    budget: number;
    priority: string;
    service_type: string;
    created_at: string;
  };
  notification: {
    title: string;
    message: string;
    action_url: string;
  };
}
```

## Implementación en la Aplicación

### 1. Configuración del Proveedor de Notificaciones

En tu archivo `_app.tsx` o componente raíz:

```typescript
import { Toaster } from 'react-hot-toast';

function App({ Component, pageProps }) {
  return (
    <>
      <Toaster position="top-right" />
      <Component {...pageProps} />
    </>
  );
}

export default App;
```

### 2. Implementación en Componentes

```typescript
import { useEcho } from '@/hooks/useEcho';
import { useNotifications } from '@/hooks/useNotifications';

function Layout({ children }) {
  const { isConnected } = useEcho();
  const { notifications } = useNotifications();

  return (
    <div>
      {/* Opcional: Indicador de estado de conexión */}
      {!isConnected && (
        <div className="bg-yellow-100 p-2 text-sm">
          Reconectando al servidor de notificaciones...
        </div>
      )}
      
      {children}
    </div>
  );
}
```

## Estilos CSS Necesarios

Agregar estos estilos a tu archivo CSS global:

```css
@keyframes enter {
  0% {
    transform: translateX(100%);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes leave {
  0% {
    transform: translateX(0);
    opacity: 1;
  }
  100% {
    transform: translateX(100%);
    opacity: 0;
  }
}

.animate-enter {
  animation: enter 0.2s ease-out;
}

.animate-leave {
  animation: leave 0.2s ease-in forwards;
}
```

## Pruebas y Verificación

1. Asegurarse de que el servidor Laravel esté ejecutando:
   - Queue worker: `php artisan queue:work`
   - Servidor Reverb: `php artisan reverb:start`

2. Verificar en la consola del navegador:
   - Conexión exitosa a WebSocket
   - Suscripción a canales correcta
   - Recepción de eventos

3. Probar creando una nueva solicitud de servicio:
   - Debería aparecer una notificación toast
   - Debería aparecer una notificación del sistema
   - La notificación debería persistir en el almacenamiento local

## Consideraciones de Seguridad

1. Asegurarse de que el token de autenticación se actualice correctamente después de:
   - Inicio de sesión
   - Cierre de sesión
   - Renovación de token

2. Manejar apropiadamente los errores de autenticación en los canales privados

3. No exponer información sensible en los logs de desarrollo

## Solución de Problemas

### Problemas Comunes y Soluciones

1. No se reciben notificaciones:
   - Verificar conexión WebSocket en la consola del navegador
   - Confirmar que el usuario tiene habilidades configuradas
   - Verificar que el token de autenticación sea válido

2. Errores de conexión:
   - Verificar que el servidor Reverb esté ejecutándose
   - Confirmar configuración de CORS en el backend
   - Verificar variables de entorno

3. Notificaciones duplicadas:
   - Verificar que no haya múltiples suscripciones al mismo canal
   - Confirmar que el cleanup en useEffect se ejecute correctamente

### Debugging

Habilitar logs detallados agregando al localStorage:
```javascript
localStorage.setItem('debug', '*');
```

## Mantenimiento

1. Limpiar notificaciones antiguas periódicamente:
```typescript
// Ejemplo: Limpiar notificaciones más antiguas que 30 días
const cleanupOldNotifications = () => {
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
  
  setNotifications(prev => 
    prev.filter(n => new Date(n.timestamp) > thirtyDaysAgo)
  );
};
```

2. Monitorear el uso de memoria del almacenamiento local

3. Implementar un sistema de paginación si el número de notificaciones crece significativamente 