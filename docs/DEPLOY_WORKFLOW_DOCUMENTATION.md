# Documentación del Workflow de Despliegue - API (oidivi-helper-api)

## Descripción General

Este documento describe las particularidades y consideraciones importantes para el archivo `deploy.yml` del proyecto API de OiDiVi Helper, que utiliza Laravel y Docker para el despliegue en Amazon Lightsail.

## Estructura del Workflow

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
- name: Upload API project via SCP
  uses: appleboy/scp-action@v0.1.7
  with:
    host: ${{ secrets.SSH_HOST }}
    username: ${{ secrets.SSH_USER }}
    key: ${{ secrets.SSH_KEY }}
    source: "."
    target: "/home/ubuntu/oidivi-helper-api"
    rm: true
```

**Particularidades críticas:**

#### Acción utilizada: `appleboy/scp-action@v0.1.7`
- **Versión específica:** v0.1.7 ha demostrado ser estable para proyectos Laravel
- **¿Por qué funciona aquí y no en Frontend?** Los proyectos Laravel tienen menos archivos complejos que Next.js

#### Parámetros importantes:
- `source: "."`: Transfiere todo el contenido del repositorio
- `target: "/home/ubuntu/oidivi-helper-api"`: Ruta absoluta en el servidor
- `rm: true`: **CRÍTICO** - Elimina archivos existentes antes de la transferencia

#### Consideraciones de la ruta:
- Usar ruta absoluta completa `/home/ubuntu/oidivi-helper-api`
- No usar variables de entorno para la ruta en este paso (diferente al Frontend)
- El directorio se crea automáticamente si no existe

### 3. Paso 2: Creación del archivo .env

```yaml
- name: Write API .env file
  uses: appleboy/ssh-action@v1
  with:
    host: ${{ secrets.SSH_HOST }}
    username: ${{ secrets.SSH_USER }}
    key: ${{ secrets.SSH_KEY }}
    script: |
      echo '${{ secrets.API_ENV_FILE }}' > /home/ubuntu/oidivi-helper-api/.env
```

**Consideraciones críticas:**

#### Diferencias con el Frontend:
- **Archivo:** `.env` (no `.env.production`)
- **Método:** `echo` directo (más simple que heredoc)
- **Ruta:** Hardcodeada `/home/ubuntu/oidivi-helper-api/.env`

#### Manejo de variables:
- Laravel requiere archivo `.env` en la raíz del proyecto
- El contenido debe incluir todas las variables necesarias para Laravel
- Especial atención a `APP_KEY`, `DB_*`, y configuraciones de servicios

### 4. Paso 3: Despliegue con Docker Compose

```yaml
- name: Deploy API via Docker Compose
  uses: appleboy/ssh-action@v1
  with:
    host: ${{ secrets.SSH_HOST }}
    username: ${{ secrets.SSH_USER }}
    key: ${{ secrets.SSH_KEY }}
    script: |
      # Ensure shared Docker network exists
      docker network create oidivi-network 2>/dev/null || true
      
      # Navigate to API deployment path
      cd /home/ubuntu/oidivi-helper-api
      
      # Bring down existing services
      docker compose down
      
      # Build and start services
      docker compose up -d --build
      
      # Prune old images to save space
      docker image prune -f
```

**Consideraciones críticas:**

#### Red de Docker:
- **Nombre de red:** `oidivi-network` (diferente al Frontend: `oidivi_helper_net`)
- **Creación silenciosa:** `2>/dev/null || true` evita errores si ya existe
- **Coordinación:** Debe permitir comunicación con el Frontend si es necesario

#### Directorio de trabajo:
- **Ruta hardcodeada:** `/home/ubuntu/oidivi-helper-api`
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

## Diferencias Clave con el Frontend

### 1. Método de transferencia de archivos:
- **API:** `appleboy/scp-action@v0.1.7` (funciona bien)
- **Frontend:** `easingthemes/ssh-deploy@main` (más robusto para Next.js)

### 2. Manejo de rutas:
- **API:** Rutas hardcodeadas `/home/ubuntu/oidivi-helper-api`
- **Frontend:** Variables de entorno `${{ secrets.DEPLOY_PATH_WEB }}`

### 3. Archivo de configuración:
- **API:** `.env` (estándar Laravel)
- **Frontend:** `.env.production` (específico Next.js)

### 4. Red de Docker:
- **API:** `oidivi-network`
- **Frontend:** `oidivi_helper_net`

## Problemas Comunes y Soluciones

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

## Notas de Versiones y Compatibilidad

- **appleboy/scp-action@v0.1.7**: Versión estable para Laravel
- **appleboy/ssh-action@v1**: Versión estable para comandos SSH
- **Laravel 10+**: Requiere PHP 8.1+
- **Docker Compose v2**: Sintaxis `docker compose` (no `docker-compose`)

## Checklist de Despliegue

- [ ] Secrets configurados en GitHub
- [ ] Archivo .env completo y válido
- [ ] docker-compose.yml configurado correctamente
- [ ] Permisos de directorio verificados
- [ ] Red Docker coordinada con Frontend
- [ ] Base de datos accesible
- [ ] APP_KEY generada y configurada