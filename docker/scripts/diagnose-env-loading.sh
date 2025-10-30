#!/bin/bash

# =============================================================================
# DIAGNÓSTICO DE CARGA DE VARIABLES DE ENTORNO
# Investiga por qué las variables del .env no se cargan en el contenedor
# =============================================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 DIAGNÓSTICO DE VARIABLES DE ENTORNO${NC}"
echo "======================================"

# 1. Verificar contenido del archivo .env
echo ""
echo -e "${YELLOW}📄 CONTENIDO DEL ARCHIVO .ENV:${NC}"
echo "================================"
docker exec oidivi-helper-api head -20 /var/www/html/.env

# 2. Verificar variables de entorno del contenedor
echo ""
echo -e "${YELLOW}🔧 VARIABLES DE ENTORNO DEL CONTENEDOR:${NC}"
echo "======================================="
docker exec oidivi-helper-api env | grep -E "(APP_|DB_|FILESYSTEM_)" | sort

# 3. Verificar configuración de Laravel
echo ""
echo -e "${YELLOW}⚙️ CONFIGURACIÓN DE LARAVEL:${NC}"
echo "============================"
echo "APP_URL desde config:"
docker exec oidivi-helper-api php artisan tinker --execute="echo config('app.url');"

echo ""
echo "FILESYSTEM_DISK desde config:"
docker exec oidivi-helper-api php artisan tinker --execute="echo config('filesystems.default');"

echo ""
echo "APP_ENV desde config:"
docker exec oidivi-helper-api php artisan tinker --execute="echo config('app.env');"

# 4. Verificar permisos del archivo .env
echo ""
echo -e "${YELLOW}🔐 PERMISOS DEL ARCHIVO .ENV:${NC}"
echo "============================"
docker exec oidivi-helper-api ls -la /var/www/html/.env

# 5. Verificar si Laravel puede leer el .env
echo ""
echo -e "${YELLOW}📖 PRUEBA DE LECTURA DEL .ENV:${NC}"
echo "=============================="
docker exec oidivi-helper-api php artisan tinker --execute="
\$envFile = base_path('.env');
echo 'Archivo .env existe: ' . (file_exists(\$envFile) ? 'SÍ' : 'NO') . PHP_EOL;
echo 'Archivo .env es legible: ' . (is_readable(\$envFile) ? 'SÍ' : 'NO') . PHP_EOL;
echo 'Tamaño del archivo: ' . filesize(\$envFile) . ' bytes' . PHP_EOL;
"

# 6. Verificar cache de configuración
echo ""
echo -e "${YELLOW}💾 ESTADO DEL CACHE DE CONFIGURACIÓN:${NC}"
echo "===================================="
docker exec oidivi-helper-api ls -la /var/www/html/bootstrap/cache/config.php 2>/dev/null || echo "No hay cache de configuración"

# 7. Verificar variables específicas con diferentes métodos
echo ""
echo -e "${YELLOW}🧪 PRUEBAS DE VARIABLES ESPECÍFICAS:${NC}"
echo "==================================="

echo "Método 1 - env() helper:"
docker exec oidivi-helper-api php artisan tinker --execute="
echo 'APP_URL: ' . env('APP_URL', 'NO_DEFINIDA') . PHP_EOL;
echo 'FILESYSTEM_DISK: ' . env('FILESYSTEM_DISK', 'NO_DEFINIDA') . PHP_EOL;
echo 'APP_ENV: ' . env('APP_ENV', 'NO_DEFINIDA') . PHP_EOL;
"

echo ""
echo "Método 2 - config() helper:"
docker exec oidivi-helper-api php artisan tinker --execute="
echo 'APP_URL: ' . config('app.url', 'NO_DEFINIDA') . PHP_EOL;
echo 'FILESYSTEM_DISK: ' . config('filesystems.default', 'NO_DEFINIDA') . PHP_EOL;
echo 'APP_ENV: ' . config('app.env', 'NO_DEFINIDA') . PHP_EOL;
"

# 8. Verificar proceso de carga del .env
echo ""
echo -e "${YELLOW}🔄 PROCESO DE CARGA DEL .ENV:${NC}"
echo "============================"
docker exec oidivi-helper-api php artisan tinker --execute="
try {
    \$dotenv = Dotenv\Dotenv::createImmutable(base_path());
    \$dotenv->load();
    echo 'Dotenv cargado exitosamente' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Error cargando Dotenv: ' . \$e->getMessage() . PHP_EOL;
}
"

# 9. Verificar directorio de trabajo
echo ""
echo -e "${YELLOW}📁 DIRECTORIO DE TRABAJO:${NC}"
echo "========================"
docker exec oidivi-helper-api pwd
docker exec oidivi-helper-api ls -la /var/www/html/ | grep -E "(\.env|artisan|composer)"

echo ""
echo -e "${GREEN}🎯 DIAGNÓSTICO COMPLETADO${NC}"
echo "========================="
echo -e "${BLUE}📋 ANÁLISIS:${NC}"
echo "Si las variables del .env no aparecen en el contenedor pero el archivo existe,"
echo "es probable que:"
echo "1. El contenedor no esté leyendo el archivo .env al iniciar"
echo "2. Haya un problema con el cache de configuración"
echo "3. Las variables se estén sobrescribiendo por variables de entorno del Docker"
echo ""
echo -e "${YELLOW}💡 SOLUCIONES SUGERIDAS:${NC}"
echo "1. Limpiar cache: php artisan config:clear"
echo "2. Recrear cache: php artisan config:cache"
echo "3. Reiniciar el contenedor completamente"
echo "4. Verificar docker-compose.yml para variables hardcodeadas"