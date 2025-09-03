# Flujo de Contratos en OiDiVi Helper

## Resumen

Este documento describe el nuevo flujo de contratos implementado en OiDiVi Helper, que establece un proceso estructurado desde la solicitud de servicio hasta el pago final.

## Flujo Completo

### 1. Solicitud de Servicio (ServiceRequest)
- El cliente crea una solicitud de servicio especificando:
  - Descripción del servicio requerido
  - Presupuesto estimado
  - Ubicación
  - Fecha límite
  - Categoría del servicio

### 2. Oferta de Servicio (ServiceOffer)
- Los proveedores pueden crear ofertas para las solicitudes:
  - Precio propuesto
  - Descripción de la propuesta
  - Tiempo estimado de entrega
  - Términos específicos

### 3. Contrato (Contract)
- Una vez que el cliente acepta una oferta, se crea automáticamente un contrato:
  - **Estados del contrato:**
    - `draft`: Borrador inicial
    - `sent`: Enviado al proveedor
    - `accepted`: Aceptado por el proveedor
    - `rejected`: Rechazado por el proveedor
    - `cancelled`: Cancelado por cualquiera de las partes
    - `expired`: Expirado por tiempo
    - `completed`: Completado exitosamente

### 4. Pago (Payment)
- **Requisito obligatorio:** Solo se puede procesar un pago si existe un contrato en estado `accepted`
- El pago se vincula directamente al contrato mediante `contract_id`
- Una vez completado el pago exitosamente, el contrato se marca como `completed`

## Modelos y Relaciones

### Contract Model
```php
// Campos principales
id, service_request_id, service_offer_id, client_id, provider_id, status, terms, completed_at, created_at, updated_at

// Relaciones
- belongsTo(ServiceRequest)
- belongsTo(ServiceOffer)
- belongsTo(User as client)
- belongsTo(User as provider)
- hasMany(Payment)
```

### Payment Model
```php
// Campo agregado
contract_id (obligatorio)

// Relación agregada
- belongsTo(Contract)
```

## Endpoints API

### Contratos
- `GET /api/contracts` - Listar contratos
- `GET /api/contracts/{id}` - Ver contrato específico
- `POST /api/contracts` - Crear contrato
- `PUT /api/contracts/{id}` - Actualizar contrato
- `DELETE /api/contracts/{id}` - Eliminar contrato
- `POST /api/contracts/{id}/send` - Enviar contrato
- `POST /api/contracts/{id}/accept` - Aceptar contrato
- `POST /api/contracts/{id}/reject` - Rechazar contrato
- `POST /api/contracts/{id}/cancel` - Cancelar contrato

## Sistema de Notificaciones

Se implementaron notificaciones automáticas para:
- Envío de contrato al proveedor
- Aceptación del contrato
- Rechazo del contrato
- Cancelación del contrato
- Finalización del contrato (al completar pago)

## Validaciones de Seguridad

1. **Creación de Pago:** Se valida que exista un contrato aceptado antes de permitir el procesamiento
2. **Estados Finales:** Los contratos en estados finales (`rejected`, `cancelled`, `expired`, `completed`) no pueden ser modificados
3. **Autorización:** Solo las partes involucradas (cliente y proveedor) pueden realizar acciones sobre el contrato

## Flujo de Estados del Contrato

```
draft → sent → accepted → [pago procesado] → completed
       ↓         ↓
    expired   rejected
       ↓         ↓
   [final]   [final]
       
   cancelled (desde cualquier estado no final)
       ↓
   [final]
```

## Consideraciones Técnicas

- Los contratos se crean automáticamente cuando se acepta una oferta de servicio
- El campo `contract_id` en la tabla `payments` es obligatorio (NOT NULL)
- Se mantiene la integridad referencial entre todas las entidades
- Los tipos TypeScript en el frontend están alineados con el modelo del backend

## Migración de Datos Existentes

Para datos existentes sin contratos:
1. Se deben crear contratos retroactivos para pagos existentes
2. Los contratos retroactivos se marcan automáticamente como `completed`
3. Se mantiene la trazabilidad completa del flujo

Este nuevo flujo garantiza un proceso más estructurado y seguro para la gestión de servicios y pagos en la plataforma.