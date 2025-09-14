# Flujo de Acuerdos en OiDiVi Helper

## Resumen

Este documento describe el nuevo flujo de acuerdos implementado en OiDiVi Helper, que establece un proceso estructurado desde la solicitud de servicio hasta el pago final.

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

### 3. Acuerdo (Agreement)
- Una vez que el cliente acepta una oferta, se crea automáticamente un acuerdo:
  - **Estados del acuerdo:**
    - `draft`: Borrador inicial
    - `sent`: Enviado al proveedor
    - `accepted`: Aceptado por el proveedor
    - `rejected`: Rechazado por el proveedor
    - `cancelled`: Cancelado por cualquiera de las partes
    - `expired`: Expirado por tiempo
    - `completed`: Completado exitosamente

### 4. Pago (Payment)
- **Requisito obligatorio:** Solo se puede procesar un pago si existe un acuerdo en estado `accepted`
- El pago se vincula directamente al acuerdo mediante `agreement_id`
- Una vez completado el pago exitosamente, el acuerdo se marca como `completed`

## Modelos y Relaciones

### Agreement Model
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
agreement_id (obligatorio)

// Relación agregada
- belongsTo(Agreement)
```

## Endpoints API

### Acuerdos
- `GET /api/agreements` - Listar acuerdos
- `GET /api/agreements/{id}` - Ver acuerdo específico
- `POST /api/agreements` - Crear acuerdo
- `PUT /api/agreements/{id}` - Actualizar acuerdo
- `DELETE /api/agreements/{id}` - Eliminar acuerdo
- `POST /api/agreements/{id}/send` - Enviar acuerdo
- `POST /api/agreements/{id}/accept` - Aceptar acuerdo
- `POST /api/agreements/{id}/reject` - Rechazar acuerdo
- `POST /api/agreements/{id}/cancel` - Cancelar acuerdo

## Sistema de Notificaciones

Se implementaron notificaciones automáticas para:
- Envío de acuerdo al proveedor
- Aceptación del acuerdo
- Rechazo del acuerdo
- Cancelación del acuerdo
- Finalización del acuerdo (al completar pago)

## Validaciones de Seguridad

1. **Creación de Pago:** Se valida que exista un acuerdo aceptado antes de permitir el procesamiento
2. **Estados Finales:** Los acuerdos en estados finales (`rejected`, `cancelled`, `expired`, `completed`) no pueden ser modificados
3. **Autorización:** Solo las partes involucradas (cliente y proveedor) pueden realizar acciones sobre el acuerdo

## Flujo de Estados del Acuerdo

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

- Los acuerdos se crean automáticamente cuando se acepta una oferta de servicio
- El campo `agreement_id` en la tabla `payments` es obligatorio (NOT NULL)
- Se mantiene la integridad referencial entre todas las entidades
- Los tipos TypeScript en el frontend están alineados con el modelo del backend

## Migración de Datos Existentes

Para datos existentes sin acuerdos:
1. Se deben crear acuerdos retroactivos para pagos existentes
2. Los acuerdos retroactivos se marcan automáticamente como `completed`
3. Se mantiene la trazabilidad completa del flujo

Este nuevo flujo garantiza un proceso más estructurado y seguro para la gestión de servicios y pagos en la plataforma.

---

# Extensión: Flujo tras Rechazo (Edición y Reenvío) con Versionado

Objetivo: cubrir el escenario en que el helper rechaza un acuerdo y el cliente puede editarlo/reenviarlo o cancelarlo definitivamente, manteniendo consistencia con la regla actual de estados finales y garantizando trazabilidad completa.

## Enfoque recomendado: Versionado por revisiones

Se mantiene `rejected` como estado final del acuerdo rechazado. Cualquier edición posterior se realiza creando una nueva revisión del acuerdo (un nuevo registro de acuerdo enlazado al acuerdo "padre"). Esto preserva la regla "los estados finales no se modifican".

### Definiciones
- Hilo de negociación: la cadena de acuerdos relacionados por `parent_agreement_id` (el primero es la versión 1).
- Revisión: nuevo acuerdo que referencia a un acuerdo previo mediante `parent_agreement_id` y aumenta `version` (v2, v3, ...).

### Nuevos campos en Agreement
- `parent_agreement_id` (nullable): referencia al acuerdo padre (inicia en null para v1).
- `version` (int, default 1): número de versión dentro del hilo.
- `is_resent` (boolean, default false): indica si la versión fue reenviada tras rechazo.
- `client_changes_summary` (text, nullable): resumen de cambios aportado por el cliente en reenvíos.
- `rejection_reason` (text, nullable): motivo de rechazo (registrado en la versión rechazada).

Opcional: tabla/entidad `AgreementHistory`/`AgreementEvents` para auditar transiciones, con actor, timestamp y payload (motivo, diffs, etc.).

### Endpoints adicionales (revisiones)
- `POST /api/agreements/{id}/revisions` — Crea una revisión en `draft` a partir de un acuerdo (clona términos, incrementa `version`, ajusta `parent_agreement_id`, resetea estado y campos operativos).
- `PUT /api/agreements/{new_id}` — Permite editar la revisión en `draft` (incluye `client_changes_summary`).
- `POST /api/agreements/{new_id}/send` — Envía la revisión (marca `sent` + `is_resent=true`).
- `GET /api/agreements/{id}/history` — Devuelve el hilo completo de versiones y eventos para trazabilidad.
- `POST /api/agreements/{id}/cancel-negotiation` — Cierra el hilo de negociación tras un rechazo a petición del cliente (no cambia el estado del acuerdo rechazado, pero registra evento y bloquea nuevas revisiones desde ese `parent`).

Notas:
- Se mantienen y reutilizan los endpoints existentes de `accept`, `reject`, `cancel` sobre la versión activa.
- Pagos solo pueden operarse sobre versiones `accepted`.

### Transiciones y opciones después del rechazo

1) Rechazo del helper sobre una versión enviada
- `sent (vN)` → `rejected (vN)`
- Notificación al cliente: AgreementRejected (incluye `rejection_reason` y, si aplica, sugerencias).
- Opciones para el cliente:
  a) Editar y reenviar: crea `revisión vN+1` (draft) → edita → `send (vN+1)`.
  b) Cancelar definitivamente la negociación: `cancel-negotiation` (evento de cierre del hilo).

2) Edición y reenvío
- `rejected (vN)` → `draft (vN+1)` mediante `POST /revisions`.
- `draft (vN+1)` → `sent (vN+1)` al enviar, con `is_resent=true`.
- Notificación al helper: AgreementResent (incluye versión, `client_changes_summary` y diff de términos clave: precio, fechas, condiciones).
- El helper puede:
  - Aceptar: `sent (vN+1)` → `accepted (vN+1)` → pago → `completed (vN+1)`.
  - Rechazar: `sent (vN+1)` → `rejected (vN+1)` y repetir el ciclo.

3) Cancelación por el cliente tras rechazo
- `rejected (vN)` + `POST /cancel-negotiation` → registra evento de cierre de hilo.
- Notificación al helper: AgreementCancelledByClientAfterRejection (el cliente decidió no continuar con revisiones).
- Efecto: se bloquea la creación de nuevas revisiones para ese `parent_agreement_id`.

### Notificaciones
- AgreementRejected (helper → cliente) con `rejection_reason`.
- AgreementResent (cliente → helper) con `version`, `client_changes_summary` y diff de términos.
- AgreementCancelledByClientAfterRejection (cliente → helper).
- AgreementAccepted (helper → cliente), AgreementCancelled, AgreementExpired, AgreementCompleted (existentes).

### Reglas de negocio y validaciones
- Solo el helper puede aceptar/rechazar.
- Solo el cliente puede crear revisiones, editar y reenviar.
- Requiere `client_changes_summary` al reenviar.
- Límite opcional de revisiones por hilo (p. ej. máx. 5) para evitar loops.
- Auditoría obligatoria de todas las transiciones (historial por versión e historial del hilo).
- Enforce de integridad: (`parent_agreement_id`, `version`) únicos dentro del hilo; bloqueo de revisiones si el hilo está cerrado por `cancel-negotiation`.
- No se permiten ediciones sobre estados finales; las ediciones se hacen sobre nuevas revisiones en `draft`.

### Experiencia de Usuario (UX)
- Cliente (tras rechazo): pantalla con motivo y CTA "Editar y reenviar" y "Cancelar definitivamente".
- Editor de revisión: formulario pre-cargado, campo obligatorio "Resumen de cambios"; acciones "Guardar borrador" y "Reenviar".
- Helper: bandeja con etiqueta "Revisión vN" y visualización de diferencia de términos; acciones "Aceptar" / "Rechazar".

### Diagrama extendido (por hilo/versionado)

```
(v1) draft → sent → accepted → [pago] → completed
                 ↓
              rejected (final para v1)
                 ↓
        [cliente crea revisión]
                 ↓
(v2) draft → sent → accepted → [pago] → completed
                 ↓
              rejected (final para v2)
                 ↓
         [repetir o cerrar hilo]

En cualquier `rejected (vN)`: cliente puede `cancel-negotiation` → cierra hilo y notifica al helper.
```

### Métricas y trazabilidad
- Número de revisiones por hilo, tiempo entre rechazo y reenvío, ratio de aceptación post-revisión.
- Historial navegable por versión con eventos y diffs.

### Compatibilidad con el documento previo
- No se modifica la semántica de estados finales: `rejected` sigue siendo final por versión.
- El reenvío se logra mediante nuevas versiones enlazadas, preservando consistencia y trazabilidad.