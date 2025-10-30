#!/bin/bash

# Script para aumentar límites específicos para carga de videos
# Modifica configuraciones de PHP y Nginx temporalmente

echo "📹 AUMENTANDO LÍMITES PARA VIDEOS"
echo "================================="

# 1. Crear configuración PHP optimizada para videos
echo ""
echo "🐘 Configurando PHP para videos grandes..."
docker exec oidivi-helper-api sh -c 'cat > /usr/local/etc/php/conf.d/99-video-uploads.ini << EOF
; Configuración optimizada para carga de videos
max_execution_time = 900
max_input_time = 900
memory_limit = 1024M
upload_max_filesize = 500M
post_max_size = 500M
max_file_uploads = 50

; Configuración de buffer para archivos grandes
output_buffering = Off
implicit_flush = On

; Timeouts para conexiones largas
default_socket_timeout = 900
EOF'

echo "✅ Configuración PHP para videos creada"

# 2. Verificar configuración de Nginx
echo ""
echo "🌐 Verificando configuración Nginx..."
docker exec oidivi-helper-api nginx -t
if [ $? -eq 0 ]; then
    echo "✅ Configuración Nginx válida"
else
    echo "❌ Error en configuración Nginx"
fi

# 3. Reiniciar servicios
echo ""
echo "🔄 Reiniciando servicios con nueva configuración..."
docker exec oidivi-helper-api supervisorctl restart php-fpm:*
docker exec oidivi-helper-api supervisorctl restart nginx

# 4. Verificar nueva configuración
echo ""
echo "📊 NUEVA CONFIGURACIÓN:"
echo "======================"
docker exec oidivi-helper-api php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time|memory_limit)"

echo ""
echo "🎯 LÍMITES AUMENTADOS EXITOSAMENTE"
echo "=================================="
echo "Nuevos límites:"
echo "- Tamaño máximo de archivo: 500MB"
echo "- Tiempo máximo de ejecución: 15 minutos"
echo "- Memoria: 1GB"
echo ""
echo "Ahora puedes probar subir videos más grandes."