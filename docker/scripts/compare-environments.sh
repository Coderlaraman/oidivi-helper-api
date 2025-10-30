#!/bin/bash

# Script para comparar configuraciones entre local y producción
# Ayuda a identificar diferencias que pueden causar problemas

echo "🔍 COMPARACIÓN LOCAL vs PRODUCCIÓN"
echo "=================================="

# Función para mostrar comparación
compare_config() {
    echo ""
    echo "📊 $1:"
    echo "$(printf '=%.0s' {1..50})"
    echo "LOCAL (dev):"
    echo "$2"
    echo ""
    echo "PRODUCCIÓN:"
    echo "$3"
    echo ""
}

# 1. Comparar límites de PHP
echo "🐘 Obteniendo configuración PHP..."
LOCAL_PHP=$(docker exec oidivi-helper-api-dev php -i 2>/dev/null | grep -E "(upload_max_filesize|post_max_size|max_file_uploads|memory_limit)" || echo "Contenedor local no disponible")
PROD_PHP=$(docker exec oidivi-helper-api php -i 2>/dev/null | grep -E "(upload_max_filesize|post_max_size|max_file_uploads|memory_limit)" || echo "Contenedor producción no disponible")

compare_config "LÍMITES PHP" "$LOCAL_PHP" "$PROD_PHP"

# 2. Comparar configuración Nginx
echo "🌐 Obteniendo configuración Nginx..."
LOCAL_NGINX=$(docker exec oidivi-helper-api-dev nginx -T 2>/dev/null | grep "client_max_body_size" || echo "Contenedor local no disponible")
PROD_NGINX=$(docker exec oidivi-helper-api nginx -T 2>/dev/null | grep "client_max_body_size" || echo "Contenedor producción no disponible")

compare_config "LÍMITES NGINX" "$LOCAL_NGINX" "$PROD_NGINX"

# 3. Comparar estructura de directorios
echo "📁 Obteniendo estructura de storage..."
LOCAL_DIRS=$(docker exec oidivi-helper-api-dev ls -la /var/www/html/storage/app/public/ 2>/dev/null || echo "Contenedor local no disponible")
PROD_DIRS=$(docker exec oidivi-helper-api ls -la /var/www/html/storage/app/public/ 2>/dev/null || echo "Contenedor producción no disponible")

compare_config "ESTRUCTURA STORAGE" "$LOCAL_DIRS" "$PROD_DIRS"

# 4. Comparar permisos
echo "🔐 Obteniendo permisos..."
LOCAL_PERMS=$(docker exec oidivi-helper-api-dev stat -c "%a %U:%G" /var/www/html/storage/app/public/profile-videos 2>/dev/null || echo "Contenedor local no disponible")
PROD_PERMS=$(docker exec oidivi-helper-api stat -c "%a %U:%G" /var/www/html/storage/app/public/profile-videos 2>/dev/null || echo "Contenedor producción no disponible")

compare_config "PERMISOS PROFILE-VIDEOS" "$LOCAL_PERMS" "$PROD_PERMS"

# 5. Comparar enlaces simbólicos
echo "🔗 Obteniendo enlaces simbólicos..."
LOCAL_LINK=$(docker exec oidivi-helper-api-dev ls -la /var/www/html/public/storage 2>/dev/null || echo "Contenedor local no disponible")
PROD_LINK=$(docker exec oidivi-helper-api ls -la /var/www/html/public/storage 2>/dev/null || echo "Contenedor producción no disponible")

compare_config "ENLACE SIMBÓLICO" "$LOCAL_LINK" "$PROD_LINK"

# 6. Comparar variables de entorno
echo "🔧 Obteniendo variables de entorno..."
LOCAL_ENV=$(docker exec oidivi-helper-api-dev env 2>/dev/null | grep -E "(APP_URL|FILESYSTEM_DISK|APP_ENV)" | sort || echo "Contenedor local no disponible")
PROD_ENV=$(docker exec oidivi-helper-api env 2>/dev/null | grep -E "(APP_URL|FILESYSTEM_DISK|APP_ENV)" | sort || echo "Contenedor producción no disponible")

compare_config "VARIABLES DE ENTORNO" "$LOCAL_ENV" "$PROD_ENV"

echo ""
echo "🎯 RECOMENDACIONES:"
echo "=================="
echo "1. Asegúrate de que los límites PHP/Nginx sean iguales en ambos entornos"
echo "2. Verifica que los directorios de storage existan en producción"
echo "3. Confirma que los permisos sean 775 para www-data:www-data"
echo "4. Valida que el enlace simbólico apunte correctamente"
echo "5. Revisa que APP_URL esté configurado correctamente en producción"