# Guía de Creación de Solicitudes de Servicio

Esta guía detalla la estructura de datos esperada por el backend para crear una nueva solicitud de servicio.

## Endpoint

```
POST /api/v1/user/service-requests
```

## Headers Requeridos

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Estructura de Datos

### Campos Requeridos

| Campo | Tipo | Descripción | Validación |
|-------|------|-------------|------------|
| `title` | string | Título de la solicitud | - Máximo 255 caracteres<br>- Solo letras, números, espacios y caracteres básicos (.-_,&)<br>- Debe ser único |
| `description` | string | Descripción detallada | - Mínimo 20 caracteres |
| `address` | string | Dirección física | - Máximo 255 caracteres |
| `zip_code` | string | Código postal | - Máximo 10 caracteres |
| `latitude` | number | Latitud | - Entre -90 y 90<br>- Decimal con hasta 7 dígitos |
| `longitude` | number | Longitud | - Entre -180 y 180<br>- Decimal con hasta 7 dígitos |
| `budget` | number | Presupuesto | - Entre 0 y 999999.99<br>- Máximo 2 decimales |
| `visibility` | string | Visibilidad | - Valores: 'public' o 'private' |
| `service_type` | string | Tipo de servicio | - Valores: 'one_time' o 'recurring' |
| `priority` | string | Prioridad | - Valores: 'low', 'medium', 'high', 'urgent' |
| `category_ids` | array | IDs de categorías | - Mínimo 1 categoría<br>- IDs deben existir en la base de datos |

### Campos Opcionales

| Campo | Tipo | Descripción | Validación |
|-------|------|-------------|------------|
| `payment_method` | string | Método de pago | - Valores: 'paypal', 'credit_card', 'bank_transfer'<br>- Puede ser null |
| `due_date` | string | Fecha límite | - Formato: 'YYYY-MM-DD HH:mm:ss'<br>- Debe ser fecha futura |

## Ejemplo de Payload

```json
{
  "title": "Necesito un desarrollador React",
  "description": "Busco desarrollador con experiencia en React para proyecto de 3 meses...",
  "address": "Calle Principal 123",
  "zip_code": "12345",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "budget": 5000.00,
  "visibility": "public",
  "service_type": "one_time",
  "priority": "high",
  "category_ids": [1, 3],
  "payment_method": "paypal",
  "due_date": "2024-12-31 23:59:59"
}
```

## Respuesta Exitosa

```json
{
  "data": {
    "id": 1,
    "title": "Necesito un desarrollador React",
    "slug": "necesito-un-desarrollador-react",
    "description": "Busco desarrollador con experiencia en React...",
    "address": "Calle Principal 123",
    "zip_code": "12345",
    "location": {
      "latitude": 40.7128,
      "longitude": -74.0060
    },
    "budget": {
      "amount": 5000.00,
      "formatted": "5,000.00"
    },
    "visibility": {
      "code": "public",
      "text": "Public"
    },
    "status": {
      "code": "published",
      "text": "Published"
    },
    "priority": {
      "code": "high",
      "text": "High"
    },
    "payment_method": {
      "code": "paypal",
      "text": "PayPal"
    },
    "service_type": {
      "code": "one_time",
      "text": "One Time"
    },
    "dates": {
      "due_date": "2024-12-31 23:59:59",
      "created_at": "2024-03-15 10:30:00",
      "updated_at": "2024-03-15 10:30:00"
    },
    "flags": {
      "is_overdue": false,
      "is_published": true,
      "is_in_progress": false,
      "is_completed": false,
      "is_canceled": false,
      "is_urgent": false,
      "is_owner": true
    },
    "relationships": {
      "categories": [
        {
          "id": 1,
          "name": "Web Development",
          "slug": "web-development"
        },
        {
          "id": 3,
          "name": "Frontend",
          "slug": "frontend"
        }
      ]
    },
    "permissions": {
      "can_edit": true,
      "can_delete": true,
      "can_make_offer": false,
      "can_cancel": true
    }
  },
  "message": "Service request created successfully"
}
```

## Consideraciones Importantes

### Campo Budget
- Enviar como número decimal sin formato
- No usar separadores de miles
- Usar punto como separador decimal
- Máximo 2 decimales
- No exceder 999,999.99
- No enviar valores negativos

### Campos de Fecha
- Usar formato ISO: 'YYYY-MM-DD HH:mm:ss'
- La fecha límite (due_date) debe ser futura
- Zona horaria: UTC

### Categorías
- Enviar array de IDs numéricos
- Mínimo una categoría
- Verificar que las categorías existan previamente

### Validaciones Adicionales
- El título debe ser único en el sistema
- La descripción debe ser suficientemente detallada
- Las coordenadas deben ser válidas
- Los enums (visibility, priority, etc.) deben usar valores exactos

## Errores Comunes

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "budget": [
      "The budget must be between 0 and 999999.99"
    ],
    "category_ids": [
      "Please select at least one category"
    ]
  }
}
```

### 403 Forbidden
```json
{
  "message": "You need to add at least one skill before publishing a service request"
}
```

## Recomendaciones
1. Implementar validación en el frontend antes de enviar
2. Formatear números y fechas según las especificaciones
3. Manejar errores de validación y mostrarlos al usuario
4. Verificar permisos del usuario antes de intentar crear
5. Implementar feedback visual durante el proceso de creación 