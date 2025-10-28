# OiDiVi Helper API

API backend para la plataforma OiDiVi Helper - Conectando personas y simplificando tareas diarias a través de profesionales de confianza.

## 📋 Tabla de Contenidos

- [Descripción](#descripción)
- [Proyectos Relacionados](#-proyectos-relacionados)
- [Arquitectura del Sistema](#arquitectura-del-sistema)
- [Características Principales](#características-principales)
- [Tecnologías](#tecnologías)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Integración con Frontend](#integración-con-frontend)
- [API Endpoints](#api-endpoints)
- [Sistema de Transacciones](#sistema-de-transacciones)
- [Testing](#testing)
- [Comandos Artisan Personalizados](#comandos-artisan-personalizados)
- [Deployment](#deployment)
- [Contribución](#contribución)
- [Licencia](#licencia)
- [Soporte](#soporte)

## Descripción

OiDiVi Helper API es una aplicación Laravel que proporciona servicios backend para la plataforma de servicios domésticos y profesionales. La API maneja autenticación, gestión de usuarios, solicitudes de servicios, pagos, transacciones y más.

### 🔗 Proyectos Relacionados

- **Frontend Web**: [OiDiVi Helper Web (Next.js)](../oidivi-helper-web/README.md) - Interfaz de usuario construida con Next.js que consume esta API
- **Documentación del Sistema**: Ver [docs/](docs/) para documentación técnica detallada
- **Arquitectura Completa**: Este backend funciona en conjunto con el frontend para formar la plataforma completa OiDiVi Helper

## Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    PLATAFORMA OIDIVI HELPER                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────┐    HTTP/REST API    ┌─────────────┐ │
│  │                     │◄──────────────────►│             │ │
│  │   Frontend Web      │                    │  Backend    │ │
│  │   (Next.js 15)      │                    │  API        │ │
│  │                     │                    │ (Laravel)   │ │
│  │ - React 19 + TS     │                    │             │ │
│  │ - Tailwind CSS      │                    │ - Sanctum   │ │
│  │ - TanStack Query    │                    │ - Eloquent  │ │
│  │ - Laravel Echo      │                    │ - Reverb    │ │
│  │                     │                    │             │ │
│  └─────────────────────┘                    └─────────────┘ │
│           │                                        │        │
│           │ WebSocket (Reverb)                     │        │
│           └────────────────────────────────────────┘        │
│                                                             │
│  ┌─────────────────────┐                    ┌─────────────┐ │
│  │   Infraestructura   │                    │  Servicios  │ │
│  │                     │                    │ Externos    │ │
│  │ - Docker Network    │                    │             │ │
│  │ - MySQL 8.0+        │                    │ - Stripe    │ │
│  │ - Redis Cache       │                    │ - PayPal    │ │
│  │ - Nginx Proxy       │                    │ - Maps API  │ │
│  │                     │                    │             │ │
│  └─────────────────────┘                    └─────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de Datos
1. **Frontend** → Solicitudes HTTP → **Backend API**
2. **Backend** → Procesa lógica de negocio → **Base de Datos**
3. **Backend** → Eventos en tiempo real → **Reverb** → **Frontend**
4. **Backend** → Integración → **Servicios de Pago/Mapas**

## Características Principales

- 🔐 **Autenticación y Autorización**: Sistema completo con roles y permisos
- 👥 **Gestión de Usuarios**: Clientes, helpers y administradores
- 🛠️ **Servicios**: Creación, gestión y seguimiento de solicitudes de servicios
- 💳 **Sistema de Transacciones**: Pagos, reembolsos, comisiones y retiros unificados
- 📊 **Dashboard y Analytics**: Estadísticas y métricas en tiempo real
- 🔍 **Búsqueda Avanzada**: Búsqueda de usuarios y servicios con filtros
- 📱 **API RESTful**: Endpoints bien documentados para web y móvil
- 🌐 **Multiidioma**: Soporte para múltiples idiomas
- 📧 **Notificaciones**: Sistema de notificaciones por email y push

## Tecnologías

- **Framework**: Laravel 11.x
- **Base de Datos**: MySQL 8.0+
- **Autenticación**: Laravel Sanctum
- **Cache**: Redis
- **Queue**: Redis/Database
- **Storage**: Local/S3
- **Testing**: PHPUnit

## Instalación

### Requisitos

- PHP 8.2+
- Composer
- MySQL 8.0+
- Redis (opcional pero recomendado)
- Node.js y NPM (para assets)

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd oidivi-helper-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
Editar `.env` con tus credenciales de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oidivi_helper
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Configurar storage**
```bash
php artisan storage:link
```

7. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

La API estará disponible en `http://localhost:8000`

## Configuración

### Variables de Entorno Importantes

```env
# Aplicación
APP_NAME="OiDiVi Helper API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oidivi_helper
DB_USERNAME=root
DB_PASSWORD=

# Redis (Cache y Queue)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Email
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Pagos
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
PAYPAL_CLIENT_ID=...
PAYPAL_CLIENT_SECRET=...

# Configuración de comisiones
PLATFORM_COMMISSION_RATE=0.15
WITHDRAWAL_FEE=2.50
```

## Estructura del Proyecto

```
oidivi-helper-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Admin/          # Endpoints de administración
│   │   │           ├── User/           # Endpoints de usuarios
│   │   │           └── Auth/           # Autenticación
│   │   ├── Requests/                   # Form Requests
│   │   ├── Resources/                  # API Resources
│   │   └── Middleware/                 # Middleware personalizado
│   ├── Models/                         # Modelos Eloquent
│   ├── Services/                       # Lógica de negocio
│   └── Traits/                         # Traits reutilizables
├── database/
│   ├── migrations/                     # Migraciones de BD
│   ├── seeders/                        # Seeders
│   └── factories/                      # Model Factories
├── docs/                               # Documentación
│   ├── transaction_system.md           # Sistema de transacciones
│   └── terms_and_conditions.md         # Términos y condiciones
├── routes/
│   └── api.php                         # Rutas de API
└── tests/                              # Tests automatizados
```

## Integración con Frontend

Esta API está diseñada para trabajar con [OiDiVi Helper Web (Next.js)](../oidivi-helper-web/README.md). La integración incluye:

- **Comunicación en Tiempo Real**: Laravel Reverb (puerto 8080) + Laravel Echo en el frontend
- **Autenticación**: Laravel Sanctum proporciona tokens para el frontend Next.js
- **Multiidioma**: El backend soporta múltiples idiomas que se sincronizan con el sistema de traducciones del frontend
- **Imágenes**: El frontend está configurado para mostrar imágenes servidas por esta API

### URLs de Integración
- **API Base URL**: `http://localhost:8000` (desarrollo)
- **Reverb WebSocket**: `ws://localhost:8080` (tiempo real)
- **Red Docker**: `oidivi_helper_net` (compartida con el frontend)

## API Endpoints

### Autenticación
- `POST /api/v1/auth/register` - Registro de usuario
- `POST /api/v1/auth/login` - Inicio de sesión
- `POST /api/v1/auth/logout` - Cerrar sesión
- `POST /api/v1/auth/refresh` - Renovar token

### Usuarios
- `GET /api/v1/user/profile` - Perfil del usuario
- `PUT /api/v1/user/profile` - Actualizar perfil
- `GET /api/v1/user/search/users` - Buscar usuarios
- `GET /api/v1/user/search/service-requests` - Buscar servicios

### Servicios
- `GET /api/v1/user/service-requests` - Listar solicitudes
- `POST /api/v1/user/service-requests` - Crear solicitud
- `GET /api/v1/user/service-requests/{id}` - Ver solicitud
- `PUT /api/v1/user/service-requests/{id}` - Actualizar solicitud

### Transacciones
- `GET /api/v1/user/transactions` - Listar transacciones
- `GET /api/v1/user/transactions/{id}` - Ver transacción
- `GET /api/v1/user/transactions/statistics` - Estadísticas
- `GET /api/v1/user/transactions/export` - Exportar datos

### Administración
- `GET /api/v1/admin/users` - Gestión de usuarios
- `GET /api/v1/admin/service-requests` - Gestión de servicios
- `GET /api/v1/admin/analytics` - Analytics del sistema

## Sistema de Transacciones

La API incluye un sistema completo de transacciones que maneja:

- **Pagos**: Procesamiento de pagos por servicios
- **Reembolsos**: Gestión de devoluciones
- **Comisiones**: Cálculo automático de comisiones de plataforma
- **Retiros**: Procesamiento de retiros para helpers
- **Estadísticas**: Métricas financieras en tiempo real

Para más detalles, consulta la [documentación del sistema de transacciones](docs/transaction_system.md).

## Testing

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests con coverage
php artisan test --coverage

# Ejecutar tests específicos
php artisan test --filter TransactionTest
```

## Comandos Artisan Personalizados

```bash
# Procesar transacciones pendientes
php artisan transactions:process-pending

# Generar reportes mensuales
php artisan transactions:monthly-report

# Limpiar transacciones fallidas
php artisan transactions:cleanup-failed

# Sincronizar datos con gateways de pago
php artisan payments:sync-gateways
```

## Deployment

### Producción

1. **Configurar variables de entorno de producción**
2. **Optimizar aplicación**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Ejecutar migraciones**
```bash
php artisan migrate --force
```

4. **Configurar supervisor para queues**
5. **Configurar cron jobs**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto es privado y propietario de OiDiVi Helper.

## Soporte

Para soporte técnico, contacta al equipo de desarrollo:
- Email: dev@oidivihelper.com
- Documentación: [docs/](docs/)

---

**Versión**: 1.0.0  
**Última actualización**: Enero 2024  
**Frontend Relacionado**: [OiDiVi Helper Web (Next.js)](../oidivi-helper-web/README.md)
