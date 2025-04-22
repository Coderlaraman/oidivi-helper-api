# Documentación del Sistema de Notificaciones

## Descripción General
El sistema de notificaciones está diseñado para informar a los usuarios sobre eventos relacionados con solicitudes de servicio y ofertas. Las notificaciones se generan automáticamente en respuesta a acciones específicas dentro del sistema.

## Tipos de Notificaciones y Contextos

### 1. Nueva Solicitud de Servicio (`NEW_SERVICE_REQUEST`)
- **Trigger**: Cuando se crea una nueva solicitud de servicio que coincide con las habilidades del usuario
- **Destinatarios**: Usuarios con habilidades que coinciden con las categorías de la solicitud
- **Datos Adicionales**:
  ```typescript
  {
    service_request_id: number;
    title: string;
    // Información de la solicitud
  }
  ```

### 2. Nueva Oferta (`NEW_OFFER`)
- **Trigger**: Cuando un proveedor hace una oferta en una solicitud de servicio
- **Destinatarios**: Creador de la solicitud de servicio
- **Datos Adicionales**:
  ```typescript
  {
    offer_id: number;
    price_proposed: number;
    estimated_time: number;
  }
  ```

### 3. Actualización de Estado de Oferta (`OFFER_STATUS_UPDATED`)
- **Trigger**: Cuando se actualiza el estado de una oferta
- **Destinatarios**: Proveedor que realizó la oferta
- **Datos Adicionales**:
  ```typescript
  {
    offer_id: number;
    old_status: string;
    new_status: string;
  }
  ```

## Estructura de Notificación

```typescript
interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  data: Record<string, any>;
  read_at: string | null;
  created_at: string;
  time_ago: string;
  is_read: boolean;
  service_request?: {
    id: number;
    title: string;
    status: string;
    slug: string;
  };
  user?: {
    id: number;
    name: string;
    profile_photo_url: string | null;
  };
}
```

## Endpoints de la API

### 1. Listar Notificaciones
```http
GET /api/v1/user/notifications
```

**Query Parameters:**
- `per_page`: número de notificaciones por página (default: 10)

**Respuesta:**
```typescript
{
  success: true;
  message: string;
  data: {
    notifications: Notification[];
    unread_count: number;
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    }
  }
}
```

### 2. Contador de No Leídas
```http
GET /api/v1/user/notifications/unread-count
```

**Respuesta:**
```typescript
{
  success: true;
  data: {
    unread_count: number;
  }
}
```

### 3. Marcar como Leída
```http
PATCH /api/v1/user/notifications/{id}/read
```

### 4. Marcar Todas como Leídas
```http
PATCH /api/v1/user/notifications/read-all
```

### 5. Eliminar Notificación
```http
DELETE /api/v1/user/notifications/{id}
```

## Implementación en Frontend

### 1. Configuración Inicial

```typescript
// types/notification.ts
export interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  data: any;
  read_at: string | null;
  created_at: string;
  time_ago: string;
  is_read: boolean;
  service_request?: {
    id: number;
    title: string;
    status: string;
    slug: string;
  };
  user?: {
    id: number;
    name: string;
    profile_photo_url: string | null;
  };
}

// services/notification.service.ts
export class NotificationService {
  private readonly baseUrl = '/api/v1/user/notifications';

  async getNotifications(page = 1, perPage = 10) {
    const response = await fetch(
      `${this.baseUrl}?page=${page}&per_page=${perPage}`
    );
    return response.json();
  }

  async getUnreadCount() {
    const response = await fetch(`${this.baseUrl}/unread-count`);
    return response.json();
  }

  async markAsRead(id: number) {
    const response = await fetch(`${this.baseUrl}/${id}/read`, {
      method: 'PATCH'
    });
    return response.json();
  }

  async markAllAsRead() {
    const response = await fetch(`${this.baseUrl}/read-all`, {
      method: 'PATCH'
    });
    return response.json();
  }

  async delete(id: number) {
    const response = await fetch(`${this.baseUrl}/${id}`, {
      method: 'DELETE'
    });
    return response.json();
  }
}
```

### 2. Hook Personalizado

```typescript
// hooks/useNotifications.ts
import { useState, useEffect } from 'react';
import { NotificationService } from '../services/notification.service';

export function useNotifications() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const service = new NotificationService();

  const fetchNotifications = async (page = 1) => {
    setLoading(true);
    try {
      const response = await service.getNotifications(page);
      setNotifications(response.data.notifications);
      setUnreadCount(response.data.unread_count);
    } catch (err) {
      setError('Error al cargar notificaciones');
    } finally {
      setLoading(false);
    }
  };

  const handleNotificationClick = async (notification: Notification) => {
    if (!notification.is_read) {
      await service.markAsRead(notification.id);
    }
    
    // Redirección según el tipo
    if (notification.service_request) {
      // Implementar lógica de redirección
      window.location.href = `/service-requests/${notification.service_request.id}`;
    }
  };

  // Polling para actualizaciones
  useEffect(() => {
    const interval = setInterval(async () => {
      const response = await service.getUnreadCount();
      setUnreadCount(response.data.unread_count);
    }, 30000); // cada 30 segundos

    return () => clearInterval(interval);
  }, []);

  return {
    notifications,
    unreadCount,
    loading,
    error,
    fetchNotifications,
    handleNotificationClick,
    markAllAsRead: service.markAllAsRead,
    deleteNotification: service.delete
  };
}
```

### 3. Componente de Notificaciones

```typescript
// components/NotificationList.tsx
import { useNotifications } from '../hooks/useNotifications';

export function NotificationList() {
  const {
    notifications,
    unreadCount,
    loading,
    error,
    handleNotificationClick,
    markAllAsRead,
    deleteNotification
  } = useNotifications();

  if (loading) return <div>Cargando...</div>;
  if (error) return <div>{error}</div>;

  return (
    <div className="notifications-container">
      <div className="notifications-header">
        <h2>Notificaciones ({unreadCount} sin leer)</h2>
        {unreadCount > 0 && (
          <button onClick={markAllAsRead}>
            Marcar todas como leídas
          </button>
        )}
      </div>

      <div className="notifications-list">
        {notifications.map(notification => (
          <div
            key={notification.id}
            className={`notification-item ${notification.is_read ? 'read' : 'unread'}`}
            onClick={() => handleNotificationClick(notification)}
          >
            {notification.user?.profile_photo_url && (
              <img
                src={notification.user.profile_photo_url}
                alt={notification.user.name}
                className="user-avatar"
              />
            )}
            <div className="notification-content">
              <h3>{notification.title}</h3>
              <p>{notification.message}</p>
              <span className="time-ago">{notification.time_ago}</span>
            </div>
            <button
              onClick={(e) => {
                e.stopPropagation();
                deleteNotification(notification.id);
              }}
              className="delete-btn"
            >
              Eliminar
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
```

### 4. Estilos Recomendados

```scss
.notifications-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 1rem;

  .notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .notification-item {
    display: flex;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;

    &.unread {
      background-color: #f0f7ff;
      font-weight: 500;
    }

    &:hover {
      background-color: #f5f5f5;
    }
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 1rem;
  }

  .notification-content {
    flex: 1;

    h3 {
      margin: 0;
      font-size: 1rem;
    }

    .time-ago {
      font-size: 0.8rem;
      color: #666;
    }
  }

  .delete-btn {
    opacity: 0;
    transition: opacity 0.2s;
  }

  .notification-item:hover .delete-btn {
    opacity: 1;
  }
}
```

## Consideraciones Importantes

1. **Autenticación**: Todos los endpoints requieren que el usuario esté autenticado.

2. **Manejo de Errores**: Implementar un manejador global de errores:
```typescript
async function handleApiError(error: any) {
  if (error.response) {
    // Error de respuesta del servidor
    const data = await error.response.json();
    return data.message || 'Error del servidor';
  }
  return 'Error de conexión';
}
```

3. **Estado de Carga**: Mostrar estados de carga apropiados para mejorar la UX.

4. **Optimización**: 
   - Implementar paginación infinita o "Cargar más"
   - Actualizar localmente el estado después de marcar como leída/eliminar
   - Considerar usar SWR o React Query para el manejo de estado del servidor

5. **Accesibilidad**:
   - Usar roles ARIA apropiados
   - Asegurar navegación por teclado
   - Proporcionar textos alternativos para imágenes

¿Necesitas alguna aclaración adicional sobre algún aspecto específico de la implementación? 