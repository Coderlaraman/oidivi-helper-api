#!/bin/bash

# Script para verificar que los nombres de contenedores estandarizados funcionen correctamente
# Autor: Sistema de estandarización de contenedores
# Fecha: $(date +%Y-%m-%d)

set -e

echo "🔍 Verificando nombres de contenedores estandarizados..."
echo "=================================================="

# Función para verificar si un contenedor está corriendo
check_container() {
    local container_name=$1
    local environment=$2
    
    echo "📦 Verificando contenedor: $container_name ($environment)"
    
    if docker ps --format "table {{.Names}}" | grep -q "^$container_name$"; then
        echo "✅ Contenedor '$container_name' está corriendo"
        
        # Verificar que el contenedor responde
        if [[ "$container_name" == *"api"* ]]; then
            echo "🌐 Verificando conectividad del API..."
            if docker exec "$container_name" curl -s -o /dev/null -w "%{http_code}" http://localhost/api/health 2>/dev/null | grep -q "200\|404"; then
                echo "✅ API responde correctamente"
            else
                echo "⚠️  API no responde (puede ser normal si no hay endpoint /health)"
            fi
        fi
        
        if [[ "$container_name" == *"mysql"* ]]; then
            echo "🗄️  Verificando conectividad de MySQL..."
            if docker exec "$container_name" mysqladmin ping -h localhost --silent 2>/dev/null; then
                echo "✅ MySQL responde correctamente"
            else
                echo "❌ MySQL no responde"
                return 1
            fi
        fi
        
    else
        echo "❌ Contenedor '$container_name' NO está corriendo"
        return 1
    fi
    
    echo ""
}

# Función para mostrar contenedores actuales
show_current_containers() {
    echo "📋 Contenedores actualmente corriendo:"
    echo "======================================"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "(oidivi|NAMES)" || echo "No hay contenedores de OiDiVi corriendo"
    echo ""
}

# Función principal de verificación
main() {
    echo "🚀 Iniciando verificación de nombres de contenedores..."
    echo ""
    
    show_current_containers
    
    # Detectar entorno basado en contenedores corriendo
    local environment="unknown"
    local api_container=""
    local mysql_container=""
    
    if docker ps --format "{{.Names}}" | grep -q "oidivi-helper-api-dev"; then
        environment="development"
        api_container="oidivi-helper-api-dev"
        mysql_container="oidivi-helper-mysql-dev"
    elif docker ps --format "{{.Names}}" | grep -q "oidivi-helper-api$"; then
        environment="production"
        api_container="oidivi-helper-api"
        mysql_container="oidivi-helper-mysql"
    else
        echo "❌ No se detectaron contenedores de OiDiVi Helper corriendo"
        echo ""
        echo "💡 Para iniciar los contenedores:"
        echo "   Desarrollo: docker-compose -f docker-compose.dev.yml up -d"
        echo "   Producción: docker-compose up -d"
        exit 1
    fi
    
    echo "🎯 Entorno detectado: $environment"
    echo ""
    
    # Verificar contenedores
    local success=true
    
    if ! check_container "$api_container" "$environment"; then
        success=false
    fi
    
    if ! check_container "$mysql_container" "$environment"; then
        success=false
    fi
    
    # Verificar que no existan contenedores con nombres antiguos
    echo "🔍 Verificando que no existan contenedores con nombres antiguos..."
    echo "================================================================"
    
    local old_containers=$(docker ps -a --format "{{.Names}}" | grep -E "oidivi.*helper.*api.*_.*_|oidivi.*helper.*mysql.*_.*_" || true)
    
    if [ -n "$old_containers" ]; then
        echo "⚠️  Se encontraron contenedores con nombres antiguos:"
        echo "$old_containers"
        echo ""
        echo "💡 Para limpiar contenedores antiguos:"
        echo "   docker-compose down --remove-orphans"
        echo "   docker container prune -f"
    else
        echo "✅ No se encontraron contenedores con nombres antiguos"
    fi
    
    echo ""
    
    # Resumen final
    if [ "$success" = true ]; then
        echo "🎉 ¡Verificación completada exitosamente!"
        echo "✅ Todos los contenedores están usando nombres estandarizados"
        echo "✅ Entorno: $environment"
        echo "✅ API: $api_container"
        echo "✅ MySQL: $mysql_container"
    else
        echo "❌ Verificación falló"
        echo "Algunos contenedores no están funcionando correctamente"
        exit 1
    fi
}

# Ejecutar función principal
main "$@"