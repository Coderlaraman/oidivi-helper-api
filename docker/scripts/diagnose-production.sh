#!/bin/bash

# Script de diagnóstico para producción
# Verifica configuración de storage, permisos y límites de archivos

echo "🔍 DIAGNÓSTICO DE PRODUCCIÓN - CARGA DE VIDEOS"
echo "=============================================="

# Función para mostrar estado
show_status() {
    if [ $1 -eq 0 ]; then
        echo "✅ $2"
    else
        echo "❌ $2"
    fi
}

# 1. Verificar contenedores
echo ""
echo "📦 ESTADO DE CONTENEDORES:"
echo "=========================="
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# 2. Verificar volúmenes
echo ""
echo "💾 VOLÚMENES DE DOCKER:"
echo "======================"
docker volume ls | grep oidivi-helper

# 3. Verificar directorios de storage dentro del contenedor
echo ""
echo "📁 DIRECTORIOS DE STORAGE:"
echo "========================="
docker exec oidivi-helper-api ls -la /var/www/html/storage/app/public/ 2>/dev/null
show_status $? "Directorio storage/app/public accesible"

docker exec oidivi-helper-api ls -la /var/www/html/storage/app/public/profile-videos/ 2>/dev/null
show_status $? "Directorio profile-videos existe"

# 4. Verificar permisos
echo ""
echo "🔐 PERMISOS DE STORAGE:"
echo "======================"
STORAGE_PERMS=$(docker exec oidivi-helper-api stat -c "%a %U:%G" /var/www/html/storage/app/public 2>/dev/null)
echo "Storage permissions: $STORAGE_PERMS"

VIDEO_PERMS=$(docker exec oidivi-helper-api stat -c "%a %U:%G" /var/www/html/storage/app/public/profile-videos 2>/dev/null)
echo "Profile-videos permissions: $VIDEO_PERMS"

# 5. Verificar enlace simbólico
echo ""
echo "🔗 ENLACE SIMBÓLICO:"
echo "==================="
docker exec oidivi-helper-api ls -la /var/www/html/public/storage 2>/dev/null
show_status $? "Enlace simbólico existe"

# 6. Verificar configuración PHP
echo ""
echo "🐘 CONFIGURACIÓN PHP:"
echo "===================="
docker exec oidivi-helper-api php -i | grep -E "(upload_max_filesize|post_max_size|max_file_uploads|memory_limit)" 2>/dev/null

# 7. Verificar configuración Nginx
echo ""
echo "🌐 CONFIGURACIÓN NGINX:"
echo "======================"
docker exec oidivi-helper-api nginx -T 2>/dev/null | grep -E "(client_max_body_size|server_name)" | head -5

# 8. Verificar logs de errores recientes
echo ""
echo "📋 LOGS DE ERRORES RECIENTES:"
echo "============================="
echo "--- PHP Errors ---"
docker exec oidivi-helper-api tail -10 /var/www/html/storage/logs/laravel.log 2>/dev/null | tail -5

echo ""
echo "--- Nginx Errors ---"
docker exec oidivi-helper-api tail -10 /var/log/nginx/error.log 2>/dev/null | tail -5

# 9. Probar escritura en storage
echo ""
echo "🧪 PRUEBA DE ESCRITURA:"
echo "======================="
docker exec oidivi-helper-api sh -c 'echo "Test $(date)" > /var/www/html/storage/app/public/test-write.txt && ls -la /var/www/html/storage/app/public/test-write.txt && rm /var/www/html/storage/app/public/test-write.txt' 2>/dev/null
show_status $? "Escritura en storage funcional"

# 10. Verificar espacio en disco
echo ""
echo "💽 ESPACIO EN DISCO:"
echo "==================="
docker exec oidivi-helper-api df -h /var/www/html/storage 2>/dev/null

# 11. Verificar variables de entorno relacionadas con storage
echo ""
echo "🔧 VARIABLES DE ENTORNO:"
echo "========================"
docker exec oidivi-helper-api env | grep -E "(APP_URL|FILESYSTEM_DISK)" 2>/dev/null

echo ""
echo "🎯 DIAGNÓSTICO COMPLETADO"
echo "========================="
echo "Si hay problemas con videos pero no con imágenes, revisa:"
echo "1. Límites de tamaño de archivo (videos son más grandes)"
echo "2. Timeout de PHP/Nginx para uploads largos"
echo "3. Permisos específicos del directorio profile-videos"
echo "4. Logs de errores durante la carga de videos"