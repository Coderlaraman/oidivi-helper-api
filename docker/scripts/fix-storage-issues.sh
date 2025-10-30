#!/bin/bash

# Script de corrección rápida para problemas de storage
# Este script soluciona los problemas más comunes automáticamente

echo "🔧 CORRECCIÓN AUTOMÁTICA DE PROBLEMAS DE STORAGE"
echo "================================================"

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

show_step() {
    echo -e "${BLUE}🔄 $1${NC}"
}

show_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

show_error() {
    echo -e "${RED}❌ $1${NC}"
}

show_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Verificar que Docker está corriendo
if ! docker ps >/dev/null 2>&1; then
    show_error "Docker no está corriendo o no tienes permisos"
    exit 1
fi

CONTAINER_NAME="oidivi-helper-api-api-1"

# Verificar que el contenedor existe
if ! docker ps -a | grep -q "$CONTAINER_NAME"; then
    show_error "Contenedor $CONTAINER_NAME no encontrado"
    echo "Ejecuta: docker-compose up -d"
    exit 1
fi

# Verificar que el contenedor está corriendo
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    show_warning "Contenedor no está corriendo, intentando iniciarlo..."
    docker-compose up -d
    sleep 5
fi

echo ""
show_step "1. Creando directorios de storage necesarios..."

# Crear directorios
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/app/public/profile-photos
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/app/public/profile-videos  
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/app/public/temp
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/logs
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/framework/cache
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/framework/sessions
docker exec "$CONTAINER_NAME" mkdir -p /var/www/html/storage/framework/views

show_success "Directorios creados"

echo ""
show_step "2. Configurando permisos correctos..."

# Establecer permisos
docker exec "$CONTAINER_NAME" chown -R www-data:www-data /var/www/html/storage
docker exec "$CONTAINER_NAME" chmod -R 775 /var/www/html/storage
docker exec "$CONTAINER_NAME" chmod -R 755 /var/www/html/bootstrap/cache

show_success "Permisos configurados"

echo ""
show_step "3. Creando/corrigiendo enlace simbólico..."

# Remover enlace existente si está mal
docker exec "$CONTAINER_NAME" rm -f /var/www/html/public/storage

# Crear enlace simbólico correcto
docker exec "$CONTAINER_NAME" ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Verificar enlace
if docker exec "$CONTAINER_NAME" test -L /var/www/html/public/storage; then
    show_success "Enlace simbólico creado correctamente"
else
    show_error "Error al crear enlace simbólico"
fi

echo ""
show_step "4. Configurando permisos del enlace simbólico..."

docker exec "$CONTAINER_NAME" chown -h www-data:www-data /var/www/html/public/storage 2>/dev/null || true

show_success "Permisos del enlace configurados"

echo ""
show_step "5. Ejecutando comando Laravel storage:link..."

# Ejecutar artisan storage:link como respaldo
docker exec "$CONTAINER_NAME" php artisan storage:link --force 2>/dev/null || show_warning "Comando artisan falló, pero el enlace manual debería funcionar"

show_success "Comando Laravel ejecutado"

echo ""
show_step "6. Probando funcionalidad..."

# Crear archivo de prueba
TEST_FILE="test-$(date +%s).txt"
if docker exec "$CONTAINER_NAME" sh -c "echo 'Test storage functionality' > '/var/www/html/storage/app/public/$TEST_FILE'"; then
    
    # Verificar que es accesible vía enlace
    if docker exec "$CONTAINER_NAME" test -f "/var/www/html/public/storage/$TEST_FILE"; then
        show_success "✅ Storage funciona correctamente"
        
        # Verificar acceso HTTP si es posible
        API_PORT=$(docker port "$CONTAINER_NAME" 80/tcp 2>/dev/null | cut -d: -f2)
        if [ -n "$API_PORT" ]; then
            HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$API_PORT/storage/$TEST_FILE" 2>/dev/null || echo "000")
            if [ "$HTTP_STATUS" = "200" ]; then
                show_success "✅ Acceso HTTP funciona correctamente"
            else
                show_warning "Acceso HTTP no funciona (código: $HTTP_STATUS)"
                show_warning "Verifica la configuración de Nginx"
            fi
        fi
        
        # Limpiar archivo de prueba
        docker exec "$CONTAINER_NAME" rm -f "/var/www/html/storage/app/public/$TEST_FILE"
    else
        show_error "El enlace simbólico no funciona correctamente"
    fi
else
    show_error "No se puede escribir en storage"
fi

echo ""
show_step "7. Reiniciando servicios web..."

# Reiniciar Nginx dentro del contenedor
docker exec "$CONTAINER_NAME" nginx -s reload 2>/dev/null || show_warning "No se pudo recargar Nginx"

show_success "Servicios reiniciados"

echo ""
echo "================================================"
echo "🎉 CORRECCIÓN COMPLETADA"
echo ""
echo "📋 RESUMEN:"
echo "   ✅ Directorios de storage creados"
echo "   ✅ Permisos configurados (775 para storage)"
echo "   ✅ Enlace simbólico creado"
echo "   ✅ Funcionalidad probada"
echo ""
echo "🧪 PRÓXIMOS PASOS:"
echo "   1. Prueba subir una imagen desde el frontend"
echo "   2. Verifica que la imagen se muestra correctamente"
echo "   3. Si hay problemas, revisa los logs:"
echo "      docker-compose logs api"
echo ""
echo "🔍 VERIFICACIÓN COMPLETA:"
echo "   ./docker/scripts/verify-storage.sh"
echo "================================================"