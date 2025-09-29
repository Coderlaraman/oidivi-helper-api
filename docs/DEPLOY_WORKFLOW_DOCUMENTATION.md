# Documentación del Workflow de Despliegue - API (oidivi-helper-api)

## Descripción General

Este documento describe las particularidades y consideraciones importantes para el archivo `deploy.yml` del proyecto API de OiDiVi Helper, que utiliza Laravel y Docker para el despliegue en Amazon Lightsail.

## Estructura del Workflow

### 0. Configuraciones de Seguridad y Robustez

```yaml
name: Deploy API to Production

on:
  push:
    branches:
      - docker_work_branch
      - main
      - work_branch
  workflow_dispatch:

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    timeout-minutes: 10
```

**Mejoras de seguridad implementadas:**

#### Configuración de concurrencia:
- **`concurrency.group`:** Agrupa ejecuciones por workflow y rama
- **`cancel-in-progress: true`:** Cancela ejecuciones previas si hay una nueva
- **Beneficio:** Evita despliegues simultáneos que pueden causar conflictos

#### Configuración de timeout:
- **`timeout-minutes: 10`:** Limita la duración máxima del job
- **Beneficio:** Evita jobs colgados que consumen recursos indefinidamente
- **Valor recomendado:** 10 minutos es suficiente para despliegues típicos de Laravel

#### Manejo robusto de errores:
- **`set -e`:** Incluido en todos los scripts SSH
- **Beneficio:** El script falla inmediatamente si cualquier comando falla
- **Resultado:** Detección temprana de problemas y rollback automático

### 1. Configuración de Triggers

```yaml
on:
  push:
    branches:
      - docker_work_branch
      - main
      - work_branch
  workflow_dispatch:
```

**Consideraciones importantes:**
- El workflow se ejecuta automáticamente en push a las ramas especificadas
- `workflow_dispatch` permite ejecución manual desde GitHub Actions
- Asegúrate de que las ramas coincidan con tu estrategia de branching
- Sincronizar con las ramas del Frontend para despliegues coordinados

### 2. Paso 1: Subida de Archivos al Servidor

```yaml
- name: Upload API project to server
  uses: easingthemes/ssh-deploy@main
  with:
    SSH_PRIVATE_KEY: ${{ secrets.SSH_KEY }}
    ARGS: "-rlgoDzvc -i --delete"
    SOURCE: "."
    REMOTE_HOST: ${{ secrets.SSH_HOST }}
    REMOTE_USER: ${{ secrets.SSH_USER }}
    TARGET: ${{ secrets.DEPLOY_PATH_API }}
```

**Particularidades críticas:**

#### Acción utilizada: `easingthemes/ssh-deploy@main`
- **Cambio importante:** Migrado de `appleboy/scp-action@v0.1.7` a `easingthemes/ssh-deploy@main`
- **Razón del cambio:** Mayor robustez y consistencia con el workflow del Frontend
- **Ventajas:** Mejor manejo de archivos grandes, transferencias más confiables, y sincronización mejorada

#### Parámetros importantes:
- `SOURCE: "."`: Transfiere todo el contenido del repositorio
- `TARGET: ${{ secrets.DEPLOY_PATH_API }}`: Usa variable de entorno para flexibilidad
- `ARGS: "-rlgoDzvc -i --delete"`: Argumentos de rsync para transferencia optimizada
  - `-r`: Recursivo
  - `-l`: Preservar enlaces simbólicos
  - `-g`: Preservar grupo
  - `-o`: Preservar propietario
  - `-D`: Preservar dispositivos y archivos especiales
  - `-z`: Compresión durante transferencia
  - `-v`: Modo verbose
  - `-c`: Usar checksums para determinar archivos a transferir
  - `-i`: Mostrar cambios item por item
  - `--delete`: Eliminar archivos en destino que no existen en origen

#### Consideraciones de la ruta:
- **Cambio importante:** Ahora usa variable `${{ secrets.DEPLOY_PATH_API }}` en lugar de ruta hardcodeada
- **Flexibilidad:** Permite cambiar la ruta de despliegue sin modificar el workflow
- **Consistencia:** Alineado con el patrón del Frontend para mejor mantenimiento

### 3. Paso 2: Creación del archivo .env

```yaml
- name: Write API .env file from secret
  uses: appleboy/ssh-action@v1
  with:
    host: ${{ secrets.SSH_HOST }}
    username: ${{ secrets.SSH_USER }}
    key: ${{ secrets.SSH_KEY }}
    script: |
      set -e
      cd ${{ secrets.DEPLOY_PATH_API }}
      cat > .env << 'EOF'
      ${{ secrets.API_ENV_FILE }}
      EOF
```

**Consideraciones críticas:**

#### Cambios importantes en la sintaxis:
- **Método mejorado:** Uso de `cat > .env << 'EOF'` en lugar de `echo` directo
- **Seguridad:** Las comillas simples en `'EOF'` previenen la expansión de variables
- **Robustez:** `set -e` asegura que el script falle si hay errores
- **Flexibilidad:** Uso de `${{ secrets.DEPLOY_PATH_API }}` para la ruta

#### Diferencias con el Frontend:
- **Archivo:** `.env` (no `.env.production`)
- **Método:** Heredoc con `cat` (consistente y seguro)
- **Ruta:** Variable de entorno `${{ secrets.DEPLOY_PATH_API }}`

#### Manejo de variables:
- Laravel requiere archivo `.env` en la raíz del proyecto
- El contenido debe incluir todas las variables necesarias para Laravel
- Especial atención a `APP_KEY`, `DB_*`, y configuraciones de servicios
- **Corrección crítica:** La sintaxis anterior causaba errores de expansión de variables

### 4. Paso 3: Despliegue con Docker Compose

```yaml
- name: Deploy API via Docker Compose
  uses: appleboy/ssh-action@v1
  with:
    host: ${{ secrets.SSH_HOST }}
    username: ${{ secrets.SSH_USER }}
    key: ${{ secrets.SSH_KEY }}
    script: |
      set -e
      # Ensure shared Docker network exists
      docker network create oidivi_helper_net 2>/dev/null || true
      
      # Navigate to API deployment path
      cd ${{ secrets.DEPLOY_PATH_API }}
      
      # Bring down existing services
      docker compose down
      
      # Build and start services
      docker compose up -d --build
      
      # Prune old images to save space
      docker image prune -f
```

**Consideraciones críticas:**

#### Mejoras de seguridad y robustez:
- **`set -e`:** Asegura que el script falle inmediatamente si cualquier comando falla
- **Manejo de errores:** Mejor control de errores en el proceso de despliegue
- **Ruta variable:** Uso de `${{ secrets.DEPLOY_PATH_API }}` para flexibilidad

#### Red de Docker:
- **Nombre de red:** `oidivi_helper_net` (ahora consistente con el Frontend)
- **Creación silenciosa:** `2>/dev/null || true` evita errores si ya existe
- **Coordinación:** Permite comunicación entre API y Frontend en la misma red

#### Directorio de trabajo:
- **Ruta variable:** `${{ secrets.DEPLOY_PATH_API }}` en lugar de ruta hardcodeada
- **Consistencia:** Debe coincidir con la ruta del paso de upload
- El archivo `docker-compose.yml` debe estar en la raíz del proyecto

#### Comandos de Docker específicos para Laravel:
- `docker compose down`: Para servicios existentes (sin `--remove-orphans`)
- `docker compose up -d --build`: Rebuild necesario para cambios en código
- `docker image prune -f`: Limpieza de imágenes (importante para Laravel con Composer)

## Secrets Requeridos en GitHub

### Configuración en GitHub Repository Settings > Secrets and variables > Actions:

1. **SSH_KEY**: Clave privada SSH en formato OpenSSH (compartida con Frontend)
2. **SSH_HOST**: IP del servidor Amazon Lightsail (compartida con Frontend)
3. **SSH_USER**: Usuario SSH, típicamente 'ubuntu' (compartida con Frontend)
4. **API_ENV_FILE**: Contenido completo del archivo .env para Laravel
5. **DEPLOY_PATH_API**: Ruta de despliegue en el servidor (ej: `/home/ubuntu/oidivi-helper-api`)

### Contenido típico de API_ENV_FILE:

```env
APP_NAME="OiDiVi Helper API"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oidivi_helper
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Otros servicios según necesidades del proyecto
```

## Diferencias Clave con el Frontend (Actualizadas)

### 1. Método de transferencia de archivos:
- **API:** `easingthemes/ssh-deploy@main` (ahora consistente)
- **Frontend:** `easingthemes/ssh-deploy@main` (mismo método)
- **Resultado:** Ambos workflows usan la misma acción robusta

### 2. Manejo de rutas:
- **API:** Variables de entorno `${{ secrets.DEPLOY_PATH_API }}`
- **Frontend:** Variables de entorno `${{ secrets.DEPLOY_PATH_WEB }}`
- **Resultado:** Ambos usan el mismo patrón flexible

### 3. Archivo de configuración:
- **API:** `.env` (estándar Laravel)
- **Frontend:** `.env.production` (específico Next.js)
- **Diferencia:** Solo el nombre del archivo, ambos usan heredoc seguro

### 4. Red de Docker:
- **API:** `oidivi_helper_net` (ahora consistente)
- **Frontend:** `oidivi_helper_net` (mismo nombre)
- **Resultado:** Comunicación directa entre servicios

### 5. Configuraciones de seguridad:
- **API:** Incluye `concurrency`, `timeout`, y `set -e` (ahora consistente)
- **Frontend:** Incluye `concurrency`, `timeout`, y `set -e`
- **Resultado:** Ambos workflows tienen las mismas protecciones

## Problemas Comunes y Soluciones (Actualizadas)

### 1. Error de permisos en Laravel
**Causa:** Directorios `storage/` y `bootstrap/cache/` sin permisos
**Solución:** Configurar permisos en Dockerfile o docker-compose.yml

### 2. APP_KEY no configurada
**Causa:** Variable APP_KEY faltante o inválida en .env
**Solución:** Generar con `php artisan key:generate` y agregar al secret

### 3. Error de conexión a base de datos
**Causa:** Configuración DB_* incorrecta
**Solución:** Verificar credenciales y conectividad en docker-compose.yml

### 4. Composer dependencies
**Causa:** Dependencias no instaladas o desactualizadas
**Solución:** Asegurar `composer install --no-dev` en Dockerfile

### 5. Error de expansión de variables en .env (RESUELTO)
**Causa:** Sintaxis incorrecta `$API_ENV_FILE` en lugar de `${{ secrets.API_ENV_FILE }}`
**Solución:** Usar heredoc con `cat > .env << 'EOF'` y comillas simples para prevenir expansión
**Estado:** ✅ Corregido en la versión actual del workflow

### 6. Despliegues simultáneos conflictivos (RESUELTO)
**Causa:** Múltiples ejecuciones del workflow al mismo tiempo
**Solución:** Configuración de `concurrency` con `cancel-in-progress: true`
**Estado:** ✅ Implementado en la versión actual del workflow

### 7. Jobs colgados consumiendo recursos (RESUELTO)
**Causa:** Workflows sin límite de tiempo de ejecución
**Solución:** `timeout-minutes: 10` en la configuración del job
**Estado:** ✅ Implementado en la versión actual del workflow

## Requisitos del Servidor para Laravel

1. **Docker y Docker Compose** instalados
2. **PHP 8.1+** (en contenedor)
3. **Composer** (en contenedor)
4. **Base de datos** (MySQL/PostgreSQL según configuración)
5. **Permisos de escritura** en directorios Laravel específicos

## Coordinación con Frontend

### Consideraciones de red:
- Si API y Frontend necesitan comunicarse, usar la misma red Docker
- Configurar variables de entorno para URLs de comunicación
- Considerar proxy reverso (Nginx) para routing

### Despliegue coordinado:
- Desplegar API antes que Frontend si hay cambios de esquema
- Usar mismas ramas para triggers coordinados
- Considerar workflows dependientes si es necesario

## Monitoreo y Debugging Laravel

### Logs específicos:
```bash
# Logs de Laravel
docker logs <laravel_container_name>

# Logs de base de datos
docker logs <db_container_name>

# Verificar servicios
docker ps
docker network ls
```

### Comandos útiles en contenedor:
```bash
# Entrar al contenedor Laravel
docker exec -it <container_name> bash

# Verificar configuración
php artisan config:show

# Limpiar caché
php artisan config:clear
php artisan cache:clear
```

## Notas de Versiones y Compatibilidad (Actualizadas)

- **easingthemes/ssh-deploy@main**: Acción robusta para transferencia de archivos (migrado desde appleboy/scp-action)
- **appleboy/ssh-action@v1**: Versión estable para comandos SSH
- **Laravel 10+**: Requiere PHP 8.1+
- **Docker Compose v2**: Sintaxis `docker compose` (no `docker-compose`)
- **GitHub Actions**: Configuraciones de seguridad modernas (concurrency, timeout)

## Checklist de Despliegue (Actualizado)

- [ ] Secrets configurados en GitHub (incluyendo `DEPLOY_PATH_API`)
- [ ] Archivo .env completo y válido en `API_ENV_FILE`
- [ ] docker-compose.yml configurado correctamente
- [ ] Permisos de directorio verificados
- [ ] Red Docker `oidivi_helper_net` coordinada con Frontend
- [ ] Base de datos accesible
- [ ] APP_KEY generada y configurada
- [ ] Configuraciones de seguridad del workflow verificadas (concurrency, timeout)
- [ ] Sintaxis de heredoc correcta para archivos .env
- [ ] Variables de entorno para rutas configuradas