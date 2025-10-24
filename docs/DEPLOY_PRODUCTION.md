# Guía oficial de despliegue a producción

Sistema: OiDiVi Helper (Laravel API + Next.js Web) en contenedores Docker sobre Ubuntu 24.04 en Amazon Lightsail

IP pública: 23.22.219.196

Dominios:
- Frontend: https://oidivi-helper.com
- API: https://api.oidivi-helper.com

Red Docker: oidivi_helper_net

---

## Tabla de contenidos
- [Resumen de arquitectura](#resumen-de-arquitectura)
- [Checklist previo](#checklist-previo)
- [Preparación del servidor](#preparación-del-servidor)
- [Generación y registro de claves SSH](#generación-y-registro-de-claves-ssh)
- [Clonado de repositorios](#clonado-de-repositorios)
- [Despliegue del API (Laravel)](#despliegue-del-api-laravel)
- [Despliegue en Producción (Next.js)](#despliegue-en-producción-nextjs)
- [Configuración de Nginx y Certbot (SSL + proxy inverso)](#configuración-de-nginx-y-certbot-ssl--proxy-inverso)
- [Validaciones y pruebas finales](#validaciones-y-pruebas-finales)
- [Mantenimiento y buenas prácticas](#mantenimiento-y-buenas-prácticas)
- [Normalización de .env en contenedores (Laravel)](#normalización-de-env-en-contenedores-laravel)

---

## Resumen de arquitectura
- Backend (Laravel API)
  - Ruta: `/oidivi-helper-api`
  - Contenedor expone `80` → host `8000`
  - Usa colas (`QUEUE_CONNECTION=database`) y scheduler bajo Supervisor
- Frontend (Next.js)
  - Ruta: `/oidivi-helper-web`
  - Contenedor expone `3000` → host `3000`
  - `NEXT_PUBLIC_API_BASE_URL` debe pasarse tanto en `environment` como en `build.args`
- Proxy inverso y SSL (en el host)
  - Nginx + Certbot (Let’s Encrypt)
  - Redirección obligatoria a HTTPS
  - Rutas: 
    - `oidivi-helper.com` → `127.0.0.1:3000`
    - `api.oidivi-helper.com` → `127.0.0.1:8000`

---

## Checklist previo
- DNS
  - Registros A de `oidivi-helper.com` y `api.oidivi-helper.com` apuntan a `23.22.219.196`
- Puertos abiertos (Lightsail + UFW)
  - 22 (SSH), 80 (HTTP), 443 (HTTPS), 3000 (web), 8000 (api)
- Red Docker
  - Crear `oidivi_helper_net` antes de levantar los contenedores
- Acceso GitHub por SSH
  - Generar clave en el servidor y añadir la clave pública en GitHub
- Rama de despliegue: usar `docker_work_branch` en ambos repositorios (API y Web)

---

## Preparación del servidor
Ejecutar en la instancia Ubuntu 24.04:

```bash
# Actualizar sistema
sudo apt update && sudo apt -y upgrade

# Instalar Docker y Compose v2
sudo apt -y install ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \ 
https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | \
sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt -y install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Añadir usuario al grupo docker (salir y entrar de sesión luego)
sudo usermod -aG docker $USER

# Crear red externa compartida
sudo docker network create oidivi_helper_net

# Configurar firewall básico (UFW)
sudo ufw allow OpenSSH
sudo ufw allow 80
sudo ufw allow 443
sudo ufw allow 3000
sudo ufw allow 8000
sudo ufw enable
```

---

## Generación y registro de claves SSH
Generar clave en el servidor y registrar en GitHub:

```bash
ssh-keygen -t ed25519 -C "coderlaraman@gmail.com"
# Acepta la ruta por defecto (~/.ssh/id_ed25519) y protege con passphrase

# Mostrar clave pública para copiar en GitHub
cat ~/.ssh/id_ed25519.pub
```

- Añadir la clave pública en GitHub: Settings → SSH and GPG keys → New SSH key
- Probar conexión:

```bash
ssh -T git@github.com
# Debe mostrar un mensaje de bienvenida de GitHub
```

---

## Clonado de repositorios
Rutas recomendadas (según especificación) y rama a usar: `docker_work_branch`

```bash
# API (usar rama docker_work_branch)
sudo mkdir -p /oidivi-helper-api && sudo chown $USER:$USER /oidivi-helper-api
cd /
git clone -b docker_work_branch --single-branch git@github.com:Coderlaraman/oidivi-helper-api.git oidivi-helper-api

# Web (usar rama docker_work_branch)
sudo mkdir -p /oidivi-helper-web && sudo chown $USER:$USER /oidivi-helper-web
cd /
git clone -b docker_work_branch --single-branch git@github.com:Coderlaraman/oidivi-helper-web.git oidivi-helper-web
```

Verificar que la rama activa sea la correcta:

```bash
cd /oidivi-helper-api && git branch --show-current
cd /oidivi-helper-web && git branch --show-current
# Deben mostrar: docker_work_branch
```

Si ya clonaste otra rama por error, cambia y actualiza:

```bash
cd /oidivi-helper-api && git fetch origin && git checkout docker_work_branch && git pull --ff-only --prune origin docker_work_branch
cd /oidivi-helper-web && git fetch origin && git checkout docker_work_branch && git pull --ff-only --prune origin docker_work_branch
```

---

## Despliegue del API (Laravel)
1) Configurar `.env` de producción (no comprometer secretos en repositorios):

Existe una plantilla `.env.production` adaptada para despliegue con Docker + Nginx. Úsala como base y copia a `.env` en el servidor:

```bash
cd /oidivi-helper-api
cp .env.production .env
```

Editar `.env` (valores clave). Asegúrate de NO usar backticks en las URLs y de mantener una línea `APP_KEY=` (vacía o con placeholder) para que artisan pueda reemplazarla:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.oidivi-helper.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oidivi_helper
DB_USERNAME=coderman
DB_PASSWORD=<CONFIGURAR_PASSWORD_DE_PRODUCCIÓN>

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Clave (artisan la reemplazará)
APP_KEY=base64:REEMPLAZAR_CLAVE

# Frontend base URL (sin backticks)
FRONTEND_URL=https://oidivi-helper.com
```

> Nota: Mantén el password fuera del control de versiones. Define el valor sólo en el servidor.

2) Levantar el contenedor en producción:

```bash
cd /oidivi-helper-api
sudo docker compose up -d --build
```

- Verificar que el servicio esté “Up” y que el nombre de servicio sea el esperado (normalmente: `api`):

```bash
sudo docker compose ps
sudo docker compose config --services
```

- Si al usar `exec` ves “service "api" is not running”, asegúrate de haber corrido el paso anterior (`up -d`) y espera unos segundos a que el contenedor inicie antes de reintentar.
- Si aparece la advertencia “the attribute version is obsolete”, es solo informativa (Compose v2 ya no usa la clave `version:`). Puedes eliminar la clave `version:` de tu `docker-compose.yml` para evitar la advertencia.

3) Inicialización de la aplicación (Opción B con `exec`)

Verifica que el archivo `.env` existe dentro del contenedor y contiene la línea `APP_KEY=`:

```bash
# Debe existir /var/www/html/.env en el contenedor
sudo docker compose exec api ls -la /var/www/html/.env
sudo docker compose exec api grep -n '^APP_KEY' /var/www/html/.env || true
```

Si el archivo no existe (o no contiene `APP_KEY=`), probablemente la imagen se construyó antes de crear `.env` en el host. Tienes dos alternativas:

A) Copiar el `.env` del host dentro del contenedor (rápido, sin rebuild):
```bash
CONTAINER_ID=$(sudo docker compose ps -q api)
sudo docker cp .env "$CONTAINER_ID":/var/www/html/.env
```

B) Reconstruir la imagen con `.env` presente (recomendado para persistir en imagen):
```bash
sudo docker compose down
sudo docker compose up -d --build
```

Ahora sí, genera la APP_KEY:

```bash
# Opción automática (escribe en /var/www/html/.env)
sudo docker compose exec api php artisan key:generate --force
```

Si prefieres pegarla manualmente:
```bash
# Muestra la clave, no escribe archivo
sudo docker compose exec api php artisan key:generate --show
```
- Copia el valor (`base64:...`) y pégalo en tu `/oidivi-helper-api/.env` del host reemplazando `APP_KEY=...`.
- Luego sincroniza al contenedor (si no vas a reconstruir):
```bash
CONTAINER_ID=$(sudo docker compose ps -q api)
sudo docker cp .env "$CONTAINER_ID":/var/www/html/.env
```

Limpia y recalcula cachés para tomar la nueva APP_KEY:
```bash
sudo docker compose exec api php artisan config:clear
sudo docker compose exec api php artisan optimize:clear
sudo docker compose exec api php artisan config:cache
```

Verificaciones:
```bash
# En el host
grep ^APP_KEY .env

# En el contenedor
sudo docker compose exec api grep -n '^APP_KEY' /var/www/html/.env
```

### MySQL en producción (Dockerizado)

Esta sección describe cómo definir el servicio MySQL en producción dentro de Docker Compose y cómo preparar la base de datos (migraciones + datos base) de forma segura.

Consideraciones clave
- Mantén las credenciales en `.env` del host y NO las commitees al repositorio.
- Usa una red externa compartida (`oidivi_helper_net`) para que los contenedores (api y mysql) se vean.
- Establece DB_HOST al nombre del servicio Docker (`mysql`) cuando la base de datos esté dockerizada.
- Evita usar `env_file` en el servicio `api`: Laravel debe leer de `/var/www/html/.env` dentro del contenedor para que artisan pueda escribir `APP_KEY` y otros valores.

Ejemplo de servicio MySQL en docker-compose.yml (producción)
```yaml
services:
  mysql:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_DATABASE: oidivi_helper
      MYSQL_USER: coderman
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    ports:
      - "3306:3306"
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "coderman", "-p${DB_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 10
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - oidivi_helper_net

  api:
    # ...
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - oidivi_helper_net

networks:
  oidivi_helper_net:
    external: true

volumes:
  mysql_data:
    driver: local
```

Variables recomendadas en `.env` (producción)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.oidivi-helper.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=oidivi_helper
DB_USERNAME=coderman
DB_PASSWORD=<DEFINE_AQUI_PASSWORD_PROD>
MYSQL_ROOT_PASSWORD=<DEFINE_AQUI_PASSWORD_ROOT_TEMPORAL>

SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Clave de APP (artisan la reemplazará en el contenedor)
APP_KEY=base64:REEMPLAZAR_CLAVE

# Frontend base URL
FRONTEND_URL=https://oidivi-helper.com

# Seeder de admin en producción (password inicial)
ADMIN_INITIAL_PASSWORD=<DEFINE_AQUI_PASSWORD_ADMIN_INICIAL>
```

Paso a paso: Inicialización de la base de datos (migraciones + datos base)
1) Levantar servicios
- Asegúrate de tener la red externa creada:
  - `sudo docker network create oidivi_helper_net`
- Levanta los servicios (api y mysql):
  - `sudo docker compose -f docker-compose.yml up -d --build`

2) Verificar salud de MySQL y conectividad
- Comprueba que `mysql` esté healthy:
  - `sudo docker compose -f docker-compose.yml ps`
- (Opcional) Probar conexión desde el contenedor `api` con las variables de `.env`:
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'php -r "\$db=new PDO(\"mysql:host=\${DB_HOST};dbname=\${DB_DATABASE};port=\${DB_PORT}\", getenv(\"DB_USERNAME\"), getenv(\"DB_PASSWORD\")); echo \$db?\"OK\\n\":\"FAIL\\n\";"'`

3) APP_KEY y cachés
- Genera APP_KEY si falta:
  - `sudo docker compose -f docker-compose.yml exec api php artisan key:generate --force`
- Limpia y reconstruye cachés:
  - `sudo docker compose -f docker-compose.yml exec api php artisan config:clear`
  - `sudo docker compose -f docker-compose.yml exec api php artisan optimize:clear`
  - `sudo docker compose -f docker-compose.yml exec api php artisan config:cache`

4) Migraciones y tablas adicionales
- Ejecuta todas las migraciones:
  - `sudo docker compose -f docker-compose.yml exec api php artisan migrate --force`
- Si `SESSION_DRIVER=database`, crea la tabla de sesiones:
  - `sudo docker compose -f docker-compose.yml exec api php artisan session:table`
  - `sudo docker compose -f docker-compose.yml exec api php artisan migrate --force`
- Si `QUEUE_CONNECTION=database`, crea la tabla de jobs:
  - `sudo docker compose -f docker-compose.yml exec api php artisan queue:table`
  - `sudo docker compose -f docker-compose.yml exec api php artisan migrate --force`

5) Seeders en producción (datos base deterministas, sin Faker)
- Verifica que `APP_ENV=production` dentro del contenedor:
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'printenv APP_ENV'`
- Define `ADMIN_INITIAL_PASSWORD` en `/var/www/html/.env` (contenerizarlo desde el host o edítalo dentro del contenedor).
- Ejecuta:
  - `sudo docker compose -f docker-compose.yml exec api php artisan db:seed --force`
- Esto creará:
  - Roles, permisos, categorías y habilidades base.
  - Un usuario admin determinista (email: `admin@oidivi-helper.com`) con la contraseña definida en `ADMIN_INITIAL_PASSWORD`.

6) Storage y permisos
- Genera el symlink de storage (si no existe):
  - `sudo docker compose -f docker-compose.yml exec api php artisan storage:link`
- Asegura permisos para escritura:
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache'`

7) Colas y scheduler
- Si usas `QUEUE_CONNECTION=database`:
  - Verifica que el worker esté corriendo vía Supervisor (según tu configuración).
- Scheduler (cron) bajo Supervisor:
  - Asegúrate de que el proceso de `schedule:run` esté definido y activo.

8) Verificaciones finales
- Revisa logs de Laravel:
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'tail -n 200 storage/logs/laravel.log'`
- Valida endpoints críticos de la API vía HTTP/HTTPS.
- (Opcional) Consulta MySQL para verificar el admin creado:
  - `sudo docker compose -f docker-compose.yml exec mysql bash -lc "mysql -u ${DB_USERNAME} -p${DB_PASSWORD} -e \"USE ${DB_DATABASE}; SELECT id,email,name FROM users WHERE email='admin@oidivi-helper.com';\""`

Notas y buenas prácticas
- No uses `env_file` en el servicio `api`: Laravel debe leer `/var/www/html/.env` interno; los env inyectados por compose pueden impedir que artisan escriba APP_KEY en el archivo del contenedor.
- Si en algún momento necesitas datos de prueba “más ricos” (usuarios y solicitudes con factories/Faker), hazlo sólo en entornos no productivos. En producción, evita instalar dependencias de desarrollo. Alternativamente, instala temporalmente dev-deps, corre los seeders específicos y vuelve...
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'composer install --no-interaction'`
  - `sudo docker compose -f docker-compose.yml exec api php artisan db:seed --class=UserSeeder --force`
  - `sudo docker compose -f docker-compose.yml exec api bash -lc 'composer install --no-dev --optimize-autoloader --no-interaction'`

---

### Normalización de .env en contenedores (Laravel)

Objetivo
- Garantizar que Laravel siempre lea y pueda escribir en `/var/www/html/.env` dentro del contenedor, evitando divergencias con variables de entorno inyectadas por Docker Compose.

Reglas generales
- Evita `env_file` y no pases variables críticas (APP_*, DB_*, FRONTEND_URL, etc.) en `services.api.environment`. Déjalas en el `.env` del proyecto (host) y sincronízalas al contenedor.
- Está bien definir en `environment` sólo flags no sensibles y estables (p. ej. `CACHE_DRIVER`, `SESSION_DRIVER`, `QUEUE_CONNECTION`) para el contenedor `api`.

Procedimiento inicial recomendado
1) Asegúrate de tener `.env` en el host:
```bash
cd /oidivi-helper-api
```
```bash
test -f .env && echo "OK" || cp .env.production .env
```

2) Levanta contenedores:
```bash
sudo docker compose up -d --build
```

3) Copia el `.env` del host al contenedor (si no está ya dentro):
```bash
CONTAINER_ID=$(sudo docker compose ps -q api)
```
```bash
sudo docker cp .env "$CONTAINER_ID":/var/www/html/.env
```

4) Genera APP_KEY (si falta) y reconstruye cachés:
```bash
sudo docker compose exec api php artisan key:generate --force
```
```bash
sudo docker compose exec api php artisan config:clear
```
```bash
sudo docker compose exec api php artisan optimize:clear
```
```bash
sudo docker compose exec api php artisan config:cache
```

Procedimiento de actualización de `.env` (cambios futuros)
1) Edita `.env` en el host y sincroniza al contenedor:
```bash
cd /oidivi-helper-api
```
```bash
CONTAINER_ID=$(sudo docker compose ps -q api)
```
```bash
sudo docker cp .env "$CONTAINER_ID":/var/www/html/.env
```

2) Limpia y vuelve a cachear la config:
```bash
sudo docker compose exec api php artisan config:clear
```
```bash
sudo docker compose exec api php artisan optimize:clear
```
```bash
sudo docker compose exec api php artisan config:cache
```

Comprobaciones útiles
- Ver APP_ENV dentro del contenedor:
```bash
sudo docker compose exec api bash -lc 'printenv APP_ENV'
```
- Verifica líneas clave en el `.env` del contenedor:
```bash
sudo docker compose exec api grep -nE "^(APP_ENV|APP_URL|DB_HOST|DB_DATABASE|DB_USERNAME|QUEUE_CONNECTION|SESSION_DRIVER)=" /var/www/html/.env
```
- Validar conectividad a la BD con los valores de `.env`:
```bash
sudo docker compose exec api bash -lc 'php -r "\$db=new PDO(\"mysql:host=\${DB_HOST};dbname=\${DB_DATABASE};port=\${DB_PORT}\", getenv(\"DB_USERNAME\"), getenv(\"DB_PASSWORD\")); echo \$db?\"OK\\n\":\"FAIL\\n\";"'
```

Problemas comunes y solución
- “No application encryption key has been specified”: genera APP_KEY y recachea config.
```bash
sudo docker compose exec api php artisan key:generate --force
```
```bash
sudo docker compose exec api php artisan config:clear && sudo docker compose exec api php artisan config:cache
```
- Cambios de DB_* no surten efecto: asegúrate de copiar `.env` actualizado al contenedor y limpiar/cachar config.
```bash
CONTAINER_ID=$(sudo docker compose ps -q api)
```
```bash
sudo docker cp .env "$CONTAINER_ID":/var/www/html/.env
```
```bash
sudo docker compose exec api php artisan config:clear && sudo docker compose exec api php artisan config:cache
```

Notas sobre caracteres especiales en `.env`
- En archivos `.env` de Laravel, se permiten caracteres como `$`, `@`, `!` sin necesidad de escapar (p. ej. `ADMIN_INITIAL_PASSWORD=OidiviAdmin2026@$`). Sin embargo, evita pasar estos valores por la sección `environment` de Docker Compose, ya que Compose sí hace interpolación de `$` y podrías necesitar comillas o escapes.
- Mantén secretos sólo en el `.env` del host (y el del contenedor) y fuera del repositorio. Recomendación: en `.env.production` usa placeholders (ej.: `ADMIN_INITIAL_PASSWORD=<DEFINE_AQUI_PASSWORD_ADMIN_INICIAL>`) y define el valor real solo en el servidor.

---