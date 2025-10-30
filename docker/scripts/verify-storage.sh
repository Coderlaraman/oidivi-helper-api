#!/bin/bash

# Script de verificación completa del sistema de storage
# Uso: ./verify-storage.sh [--fix]

echo "🔍 VERIFICACIÓN COMPLETA DEL SISTEMA DE STORAGE"
echo "=============================================="

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

# Función para mostrar advertencia
show_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Función para mostrar info
show_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

FIX_MODE=false
if [ "$1" = "--fix" ]; then
    FIX_MODE=true
    show_info "Modo de corrección automática activado"
fi

echo ""
echo "1. VERIFICANDO CONTENEDORES..."
echo "------------------------------"

# Verificar que el contenedor está corriendo
CONTAINER_NAME="oidivi-helper-api-api-1"
if docker ps | grep -q "$CONTAINER_NAME"; then
    show_status 0 "Contenedor API está corriendo"
else
    show_status 1 "Contenedor API no está corriendo"
    echo "   Ejecuta: docker-compose up -d"
    exit 1
fi

echo ""
echo "2. VERIFICANDO DIRECTORIOS DE STORAGE..."
echo "----------------------------------------"

# Verificar directorios principales
directories=(
    "/var/www/html/storage/app/public"
    "/var/www/html/storage/app/public/profile-photos"
    "/var/www/html/storage/app/public/profile-videos"
    "/var/www/html/storage/app/public/temp"
)

for dir in "${directories[@]}"; do
    if docker exec "$CONTAINER_NAME" test -d "$dir"; then
        show_status 0 "Directorio existe: $dir"
    else
        show_status 1 "Directorio faltante: $dir"
        if [ "$FIX_MODE" = true ]; then
            docker exec "$CONTAINER_NAME" mkdir -p "$dir"
            show_info "Directorio creado: $dir"
        fi
    fi
done

echo ""
echo "3. VERIFICANDO ENLACE SIMBÓLICO..."
echo "----------------------------------"

# Verificar enlace simbólico
if docker exec "$CONTAINER_NAME" test -L "/var/www/html/public/storage"; then
    show_status 0 "Enlace simbólico existe"
    
    # Verificar que apunta al lugar correcto
    LINK_TARGET=$(docker exec "$CONTAINER_NAME" readlink "/var/www/html/public/storage")
    if [ "$LINK_TARGET" = "/var/www/html/storage/app/public" ]; then
        show_status 0 "Enlace simbólico apunta correctamente"
    else
        show_status 1 "Enlace simbólico apunta a: $LINK_TARGET (incorrecto)"
        if [ "$FIX_MODE" = true ]; then
            docker exec "$CONTAINER_NAME" rm -f "/var/www/html/public/storage"
            docker exec "$CONTAINER_NAME" ln -sf "/var/www/html/storage/app/public" "/var/www/html/public/storage"
            show_info "Enlace simbólico corregido"
        fi
    fi
else
    show_status 1 "Enlace simbólico no existe"
    if [ "$FIX_MODE" = true ]; then
        docker exec "$CONTAINER_NAME" ln -sf "/var/www/html/storage/app/public" "/var/www/html/public/storage"
        show_info "Enlace simbólico creado"
    fi
fi

echo ""
echo "4. VERIFICANDO PERMISOS..."
echo "-------------------------"

# Verificar permisos de storage
STORAGE_PERMS=$(docker exec "$CONTAINER_NAME" stat -c "%a" "/var/www/html/storage/app/public" 2>/dev/null)
if [ "$STORAGE_PERMS" = "775" ] || [ "$STORAGE_PERMS" = "755" ]; then
    show_status 0 "Permisos de storage correctos ($STORAGE_PERMS)"
else
    show_status 1 "Permisos de storage incorrectos ($STORAGE_PERMS)"
    if [ "$FIX_MODE" = true ]; then
        docker exec "$CONTAINER_NAME" chmod -R 775 "/var/www/html/storage"
        show_info "Permisos corregidos"
    fi
fi

# Verificar propietario
STORAGE_OWNER=$(docker exec "$CONTAINER_NAME" stat -c "%U:%G" "/var/www/html/storage/app/public" 2>/dev/null)
if [ "$STORAGE_OWNER" = "www-data:www-data" ]; then
    show_status 0 "Propietario de storage correcto ($STORAGE_OWNER)"
else
    show_status 1 "Propietario de storage incorrecto ($STORAGE_OWNER)"
    if [ "$FIX_MODE" = true ]; then
        docker exec "$CONTAINER_NAME" chown -R www-data:www-data "/var/www/html/storage"
        show_info "Propietario corregido"
    fi
fi

echo ""
echo "5. VERIFICANDO CONFIGURACIÓN DE NGINX..."
echo "----------------------------------------"

# Verificar configuración de Nginx para storage
if docker exec "$CONTAINER_NAME" grep -q "location /storage/" "/etc/nginx/sites-available/default"; then
    show_status 0 "Nginx configurado para servir /storage/"
else
    show_status 1 "Nginx NO configurado para servir /storage/"
    show_warning "Necesitas actualizar la configuración de Nginx"
fi

echo ""
echo "6. VERIFICANDO VOLÚMENES DOCKER..."
echo "---------------------------------"

# Verificar volúmenes
if docker volume ls | grep -q "storage_data"; then
    show_status 0 "Volumen storage_data existe"
else
    show_status 1 "Volumen storage_data no existe"
    show_warning "Verifica la configuración de docker-compose.yml"
fi

echo ""
echo "7. PRUEBAS FUNCIONALES..."
echo "------------------------"

# Crear archivo de prueba
TEST_FILE="/var/www/html/storage/app/public/test-$(date +%s).txt"
if docker exec "$CONTAINER_NAME" sh -c "echo 'Test file' > '$TEST_FILE'"; then
    show_status 0 "Escritura en storage funciona"
    
    # Verificar que es accesible vía enlace simbólico
    if docker exec "$CONTAINER_NAME" test -f "/var/www/html/public/storage/test-$(basename $TEST_FILE)"; then
        show_status 0 "Acceso vía enlace simbólico funciona"
    else
        show_status 1 "Acceso vía enlace simbólico NO funciona"
    fi
    
    # Limpiar archivo de prueba
    docker exec "$CONTAINER_NAME" rm -f "$TEST_FILE"
else
    show_status 1 "NO se puede escribir en storage"
fi

echo ""
echo "8. VERIFICANDO CONECTIVIDAD WEB..."
echo "---------------------------------"

# Obtener puerto del contenedor
API_PORT=$(docker port "$CONTAINER_NAME" 80/tcp | cut -d: -f2)
if [ -n "$API_PORT" ]; then
    show_info "API corriendo en puerto: $API_PORT"
    
    # Crear archivo de prueba para verificar acceso web
    TEST_WEB_FILE="web-test-$(date +%s).txt"
    docker exec "$CONTAINER_NAME" sh -c "echo 'Web test' > '/var/www/html/storage/app/public/$TEST_WEB_FILE'"
    
    # Verificar acceso HTTP
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$API_PORT/storage/$TEST_WEB_FILE" 2>/dev/null || echo "000")
    if [ "$HTTP_STATUS" = "200" ]; then
        show_status 0 "Acceso HTTP a archivos de storage funciona"
    else
        show_status 1 "Acceso HTTP a archivos de storage NO funciona (HTTP $HTTP_STATUS)"
    fi
    
    # Limpiar archivo de prueba
    docker exec "$CONTAINER_NAME" rm -f "/var/www/html/storage/app/public/$TEST_WEB_FILE"
else
    show_status 1 "No se pudo determinar el puerto del API"
fi

echo ""
echo "9. INFORMACIÓN DEL SISTEMA..."
echo "----------------------------"

# Mostrar información útil
echo "📊 Espacio en disco:"
docker exec "$CONTAINER_NAME" df -h "/var/www/html/storage" 2>/dev/null || echo "   No disponible"

echo ""
echo "📁 Contenido de storage:"
docker exec "$CONTAINER_NAME" ls -la "/var/www/html/storage/app/public/" 2>/dev/null || echo "   Directorio vacío o inaccesible"

echo ""
echo "🔗 Estado del enlace simbólico:"
docker exec "$CONTAINER_NAME" ls -la "/var/www/html/public/storage" 2>/dev/null || echo "   Enlace no existe"

echo ""
echo "=============================================="
echo "🏁 VERIFICACIÓN COMPLETADA"

if [ "$FIX_MODE" = true ]; then
    echo "🔧 Modo de corrección automática ejecutado"
    echo "   Ejecuta el script sin --fix para verificar los cambios"
else
    echo "💡 Para corregir problemas automáticamente, ejecuta:"
    echo "   ./verify-storage.sh --fix"
fi

echo "=============================================="