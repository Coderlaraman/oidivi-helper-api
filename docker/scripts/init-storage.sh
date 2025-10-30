#!/bin/bash

# Script de inicialización para configurar storage en Laravel
# Este script debe ejecutarse después de que el contenedor esté listo

echo "🚀 Iniciando configuración de storage..."

# Crear directorios necesarios si no existen
echo "📁 Creando directorios de storage..."
mkdir -p /var/www/html/storage/app/public/profile-photos
mkdir -p /var/www/html/storage/app/public/profile-videos
mkdir -p /var/www/html/storage/app/public/temp
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views

# Establecer permisos correctos
echo "🔐 Configurando permisos..."
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Crear enlace simbólico si no existe
echo "🔗 Configurando enlace simbólico..."
if [ ! -L "/var/www/html/public/storage" ]; then
    ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
    echo "✅ Enlace simbólico creado"
else
    echo "ℹ️ Enlace simbólico ya existe"
fi

# Verificar que el enlace funciona correctamente
if [ -L "/var/www/html/public/storage" ] && [ -d "/var/www/html/storage/app/public" ]; then
    echo "✅ Enlace simbólico verificado correctamente"
else
    echo "❌ Error: Enlace simbólico no funciona correctamente"
    exit 1
fi

# Crear archivo de prueba para verificar escritura
echo "🧪 Probando escritura en storage..."
test_file="/var/www/html/storage/app/public/test-write.txt"
echo "Test file created at $(date)" > "$test_file"
if [ -f "$test_file" ]; then
    echo "✅ Escritura en storage verificada"
    rm "$test_file"
else
    echo "❌ Error: No se puede escribir en storage"
    exit 1
fi

# Configurar permisos finales
chown -R www-data:www-data /var/www/html/public/storage
chmod -R 755 /var/www/html/public/storage

echo "🎉 Configuración de storage completada exitosamente!"

# Mostrar información de debug
echo "📊 Información de debug:"
echo "Storage directory: $(ls -la /var/www/html/storage/app/public/)"
echo "Public storage link: $(ls -la /var/www/html/public/storage)"
echo "Disk space: $(df -h /var/www/html/storage)"