#!/bin/bash

# Script específico para corregir problemas de carga de videos en producción
# Basado en el diagnóstico realizado

echo "🔧 CORRECCIÓN ESPECÍFICA PARA CARGA DE VIDEOS"
echo "============================================="

# Función para mostrar estado
show_status() {
    if [ $1 -eq 0 ]; then
        echo "✅ $2"
    else
        echo "❌ $2"
        return 1
    fi
}

# 1. Limpiar archivos de prueba con permisos incorrectos
echo ""
echo "🧹 Limpiando archivos de prueba..."
docker exec oidivi-helper-api rm -f /var/www/html/storage/app/public/profile-videos/test.mp4
docker exec oidivi-helper-api rm -f /var/www/html/storage/app/public/test.txt
show_status $? "Archivos de prueba eliminados"

# 2. Corregir permisos específicos para videos
echo ""
echo "🔐 Corrigiendo permisos específicos..."
docker exec oidivi-helper-api chown -R www-data:www-data /var/www/html/storage/app/public/profile-videos
docker exec oidivi-helper-api chmod -R 775 /var/www/html/storage/app/public/profile-videos
show_status $? "Permisos de profile-videos corregidos"

# 3. Verificar y corregir configuración de timeouts
echo ""
echo "⏱️ Verificando configuración de timeouts..."
# Verificar si existe configuración de timeout en PHP-FPM
docker exec oidivi-helper-api grep -r "request_terminate_timeout" /usr/local/etc/php-fpm.d/ 2>/dev/null || echo "No se encontró configuración de timeout específica"

# 4. Crear archivo de prueba con el usuario correcto
echo ""
echo "🧪 Probando escritura con usuario correcto..."
docker exec -u www-data oidivi-helper-api sh -c 'echo "Test video $(date)" > /var/www/html/storage/app/public/profile-videos/test-video.txt'
show_status $? "Escritura con usuario www-data"

# 5. Verificar que el enlace simbólico sea accesible desde web
echo ""
echo "🌐 Verificando acceso web al storage..."
docker exec oidivi-helper-api curl -s -o /dev/null -w "%{http_code}" http://localhost/storage/test.txt 2>/dev/null || echo "Prueba de acceso web"

# 6. Agregar configuración específica para videos grandes
echo ""
echo "📹 Configurando parámetros específicos para videos..."

# Crear configuración temporal para PHP-FPM si no existe
docker exec oidivi-helper-api sh -c 'cat > /tmp/video-upload.conf << EOF
; Configuración específica para carga de videos
max_execution_time = 600
max_input_time = 600
memory_limit = 1024M
upload_max_filesize = 500M
post_max_size = 500M
EOF'

echo "Configuración temporal creada para videos grandes"

# 7. Reiniciar servicios para aplicar cambios
echo ""
echo "🔄 Reiniciando servicios..."
docker exec oidivi-helper-api supervisorctl restart php-fpm:*
docker exec oidivi-helper-api supervisorctl restart nginx
show_status $? "Servicios reiniciados"

# 8. Verificación final específica para videos
echo ""
echo "✅ VERIFICACIÓN FINAL:"
echo "====================="

echo "Permisos de profile-videos:"
docker exec oidivi-helper-api ls -la /var/www/html/storage/app/public/profile-videos/

echo ""
echo "Propietario del directorio:"
docker exec oidivi-helper-api stat -c "%U:%G" /var/www/html/storage/app/public/profile-videos/

echo ""
echo "Espacio disponible:"
docker exec oidivi-helper-api df -h /var/www/html/storage | grep -E "(Filesystem|overlay)"

echo ""
echo "Configuración PHP actual:"
docker exec oidivi-helper-api php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)" | head -3

# 9. Limpiar archivo de prueba
docker exec oidivi-helper-api rm -f /var/www/html/storage/app/public/profile-videos/test-video.txt

echo ""
echo "🎉 CORRECCIÓN COMPLETADA"
echo "========================"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Prueba subir un video pequeño (< 10MB) primero"
echo "2. Si funciona, prueba con videos más grandes"
echo "3. Si persisten problemas, revisa los logs durante la carga:"
echo "   docker logs -f oidivi-helper-api"
echo ""
echo "🔍 COMANDOS DE DEBUGGING:"
echo "docker exec oidivi-helper-api tail -f /var/www/html/storage/logs/laravel.log"
echo "docker exec oidivi-helper-api tail -f /var/log/nginx/error.log"