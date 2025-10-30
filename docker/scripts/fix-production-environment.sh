#!/bin/bash

# =============================================================================
# SCRIPT DE CORRECCIÓN COMPLETA DEL ENTORNO DE PRODUCCIÓN
# Soluciona problemas de variables de entorno y configuración PHP para videos
# =============================================================================

set -e  # Salir si hay errores

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar estado
show_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

echo -e "${BLUE}🔧 INICIANDO CORRECCIÓN DEL ENTORNO DE PRODUCCIÓN${NC}"
echo "=================================================="

# 1. Verificar que el contenedor existe
echo ""
echo -e "${YELLOW}📋 VERIFICANDO CONTENEDOR...${NC}"
if ! docker ps | grep -q "oidivi-helper-api"; then
    echo -e "${RED}❌ Contenedor oidivi-helper-api no está ejecutándose${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Contenedor encontrado${NC}"

# 2. Verificar archivo .env
echo ""
echo -e "${YELLOW}📄 VERIFICANDO ARCHIVO .ENV...${NC}"
if ! docker exec oidivi-helper-api test -f /var/www/html/.env; then
    echo -e "${RED}❌ Archivo .env no encontrado en el contenedor${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Archivo .env existe${NC}"

# 3. Crear configuración PHP optimizada para videos
echo ""
echo -e "${YELLOW}🐘 CONFIGURANDO PHP PARA VIDEOS GRANDES...${NC}"
docker exec oidivi-helper-api sh -c 'cat > /usr/local/etc/php/conf.d/99-video-uploads.ini << EOF
; Configuración optimizada para carga de videos
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 900
max_input_time = 900
memory_limit = 1024M
max_file_uploads = 50
output_buffering = Off
implicit_flush = On
default_socket_timeout = 900

; Configuración adicional para archivos grandes
file_uploads = On
max_input_vars = 3000
max_input_nesting_level = 64

; Configuración de sesión para uploads largos
session.gc_maxlifetime = 1800
session.cookie_lifetime = 1800
EOF'
show_status $? "Configuración PHP creada"

# 4. Configurar Nginx para archivos grandes
echo ""
echo -e "${YELLOW}🌐 CONFIGURANDO NGINX PARA ARCHIVOS GRANDES...${NC}"
docker exec oidivi-helper-api sh -c 'sed -i "/http {/a\\    client_max_body_size 500M;" /etc/nginx/nginx.conf'
show_status $? "Configuración Nginx actualizada"

# 5. Forzar recarga de variables de entorno
echo ""
echo -e "${YELLOW}🔄 FORZANDO RECARGA DE VARIABLES DE ENTORNO...${NC}"

# Limpiar cache de Laravel
docker exec oidivi-helper-api php artisan config:clear
docker exec oidivi-helper-api php artisan cache:clear
docker exec oidivi-helper-api php artisan route:clear
docker exec oidivi-helper-api php artisan view:clear

# Recrear cache con las nuevas configuraciones
docker exec oidivi-helper-api php artisan config:cache
show_status $? "Cache de Laravel limpiado y recreado"

# 6. Verificar y corregir permisos de storage
echo ""
echo -e "${YELLOW}🔐 VERIFICANDO PERMISOS DE STORAGE...${NC}"
docker exec oidivi-helper-api chown -R www-data:www-data /var/www/html/storage
docker exec oidivi-helper-api chmod -R 775 /var/www/html/storage
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/app/public/profile-videos
docker exec oidivi-helper-api chown -R www-data:www-data /var/www/html/storage/app/public/profile-videos
docker exec oidivi-helper-api chmod 775 /var/www/html/storage/app/public/profile-videos
show_status $? "Permisos de storage corregidos"

# 7. Recrear enlace simbólico de storage
echo ""
echo -e "${YELLOW}🔗 RECREANDO ENLACE SIMBÓLICO...${NC}"
docker exec oidivi-helper-api rm -f /var/www/html/public/storage
docker exec oidivi-helper-api php artisan storage:link
show_status $? "Enlace simbólico recreado"

# 8. Reiniciar servicios PHP-FPM y Nginx
echo ""
echo -e "${YELLOW}🔄 REINICIANDO SERVICIOS...${NC}"
docker exec oidivi-helper-api service php8.2-fpm restart
docker exec oidivi-helper-api service nginx restart
show_status $? "Servicios reiniciados"

# 9. Verificar configuración PHP final
echo ""
echo -e "${YELLOW}🧪 VERIFICANDO CONFIGURACIÓN FINAL...${NC}"
echo "Configuración PHP actual:"
docker exec oidivi-helper-api php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time|memory_limit)"

echo ""
echo "Variables de entorno críticas:"
docker exec oidivi-helper-api php artisan tinker --execute="echo 'APP_URL: ' . config('app.url') . PHP_EOL; echo 'FILESYSTEM_DISK: ' . config('filesystems.default') . PHP_EOL; echo 'APP_ENV: ' . config('app.env') . PHP_EOL;"

# 10. Prueba de escritura en storage
echo ""
echo -e "${YELLOW}🧪 PROBANDO ESCRITURA EN STORAGE...${NC}"
docker exec oidivi-helper-api sh -c 'echo "Test $(date)" > /var/www/html/storage/app/public/profile-videos/test-write.txt && ls -la /var/www/html/storage/app/public/profile-videos/test-write.txt && rm /var/www/html/storage/app/public/profile-videos/test-write.txt'
show_status $? "Escritura en storage funcional"

# 11. Verificar acceso web a storage
echo ""
echo -e "${YELLOW}🌐 VERIFICANDO ACCESO WEB A STORAGE...${NC}"
echo "Probando acceso a: https://api.oidivi-helper.com/storage/"
curl -I https://api.oidivi-helper.com/storage/ 2>/dev/null | head -1 || echo "No se pudo verificar acceso web"

echo ""
echo -e "${GREEN}🎉 CORRECCIÓN COMPLETADA${NC}"
echo "========================="
echo -e "${BLUE}📋 RESUMEN DE CAMBIOS:${NC}"
echo "• PHP configurado para archivos de hasta 500MB"
echo "• Nginx configurado para archivos de hasta 500MB"
echo "• Cache de Laravel limpiado y recreado"
echo "• Permisos de storage corregidos"
echo "• Enlace simbólico recreado"
echo "• Servicios reiniciados"
echo ""
echo -e "${YELLOW}🧪 PRÓXIMOS PASOS:${NC}"
echo "1. Probar carga de video desde la aplicación web"
echo "2. Si persisten problemas, revisar logs:"
echo "   docker logs oidivi-helper-api"
echo "   docker exec oidivi-helper-api tail -f /var/www/html/storage/logs/laravel.log"
echo ""
echo -e "${BLUE}📊 MONITOREO:${NC}"
echo "Para verificar el estado en tiempo real:"
echo "docker exec oidivi-helper-api tail -f /var/log/nginx/access.log"