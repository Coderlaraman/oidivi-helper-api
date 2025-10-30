#!/bin/bash

# Script de corrección para problemas de storage en producción
# Ejecuta las correcciones necesarias para que funcione la carga de videos

echo "🔧 CORRECCIÓN DE STORAGE EN PRODUCCIÓN"
echo "======================================"

# Función para mostrar estado
show_status() {
    if [ $1 -eq 0 ]; then
        echo "✅ $2"
    else
        echo "❌ $2"
        return 1
    fi
}

# 1. Verificar que el contenedor esté corriendo
echo ""
echo "📦 Verificando contenedor..."
if ! docker ps | grep -q "oidivi-helper-api"; then
    echo "❌ El contenedor oidivi-helper-api no está corriendo"
    echo "Ejecuta: docker-compose up -d"
    exit 1
fi
echo "✅ Contenedor oidivi-helper-api está corriendo"

# 2. Crear directorios necesarios
echo ""
echo "📁 Creando directorios de storage..."
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/app/public/profile-photos
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/app/public/profile-videos
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/app/public/temp
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/logs
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/framework/cache
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/framework/sessions
docker exec oidivi-helper-api mkdir -p /var/www/html/storage/framework/views
show_status $? "Directorios creados"

# 3. Establecer permisos correctos
echo ""
echo "🔐 Configurando permisos..."
docker exec oidivi-helper-api chown -R www-data:www-data /var/www/html/storage
docker exec oidivi-helper-api chmod -R 775 /var/www/html/storage
docker exec oidivi-helper-api chmod -R 755 /var/www/html/bootstrap/cache
show_status $? "Permisos configurados"

# 4. Crear/verificar enlace simbólico
echo ""
echo "🔗 Configurando enlace simbólico..."
docker exec oidivi-helper-api rm -f /var/www/html/public/storage
docker exec oidivi-helper-api ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
show_status $? "Enlace simbólico creado"

# 5. Verificar configuración de Laravel
echo ""
echo "🎯 Ejecutando comandos de Laravel..."
docker exec oidivi-helper-api php artisan storage:link --force
show_status $? "storage:link ejecutado"

docker exec oidivi-helper-api php artisan config:cache
show_status $? "config:cache ejecutado"

# 6. Probar escritura
echo ""
echo "🧪 Probando escritura en directorios..."
docker exec oidivi-helper-api sh -c 'echo "Test photo $(date)" > /var/www/html/storage/app/public/profile-photos/test.txt && rm /var/www/html/storage/app/public/profile-photos/test.txt'
show_status $? "Escritura en profile-photos"

docker exec oidivi-helper-api sh -c 'echo "Test video $(date)" > /var/www/html/storage/app/public/profile-videos/test.txt && rm /var/www/html/storage/app/public/profile-videos/test.txt'
show_status $? "Escritura en profile-videos"

# 7. Reiniciar servicios dentro del contenedor
echo ""
echo "🔄 Reiniciando servicios..."
docker exec oidivi-helper-api supervisorctl restart all
show_status $? "Servicios reiniciados"

# 8. Verificación final
echo ""
echo "✅ VERIFICACIÓN FINAL:"
echo "====================="
echo "Directorios de storage:"
docker exec oidivi-helper-api ls -la /var/www/html/storage/app/public/

echo ""
echo "Enlace simbólico:"
docker exec oidivi-helper-api ls -la /var/www/html/public/storage

echo ""
echo "Permisos:"
docker exec oidivi-helper-api stat -c "%a %U:%G" /var/www/html/storage/app/public/profile-videos

echo ""
echo "🎉 CORRECCIÓN COMPLETADA"
echo "========================"
echo "Ahora prueba subir un video desde la aplicación web."
echo "Si persisten los problemas, revisa los logs:"
echo "  - docker logs oidivi-helper-api"
echo "  - docker exec oidivi-helper-api tail -f /var/www/html/storage/logs/laravel.log"