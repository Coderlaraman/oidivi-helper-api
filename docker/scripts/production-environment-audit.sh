#!/bin/bash

echo "🔍 AUDITORÍA COMPLETA DEL ENTORNO DE PRODUCCIÓN"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}📍 INFORMACIÓN DEL SISTEMA${NC}"
echo "=========================="
echo "Hostname: $(hostname)"
echo "Usuario actual: $(whoami)"
echo "Directorio actual: $(pwd)"
echo "Fecha y hora: $(date)"
echo "Sistema operativo: $(uname -a)"
echo "Distribución: $(cat /etc/os-release 2>/dev/null | grep PRETTY_NAME | cut -d'=' -f2 | tr -d '\"')"

echo -e "\n${BLUE}🐳 INFORMACIÓN DE DOCKER${NC}"
echo "========================"
echo "Versión de Docker:"
docker --version 2>/dev/null || echo "Docker no encontrado"
echo ""
echo "Versión de Docker Compose:"
docker-compose --version 2>/dev/null || docker compose version 2>/dev/null || echo "Docker Compose no encontrado"

echo -e "\n${BLUE}📦 CONTENEDORES EN EJECUCIÓN${NC}"
echo "============================"
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "No se pueden listar contenedores"

echo -e "\n${BLUE}📦 TODOS LOS CONTENEDORES${NC}"
echo "========================="
docker ps -a --format "table {{.Names}}\t{{.Image}}\t{{.Status}}" 2>/dev/null || echo "No se pueden listar contenedores"

echo -e "\n${BLUE}🗂️ VOLÚMENES DE DOCKER${NC}"
echo "======================"
docker volume ls 2>/dev/null || echo "No se pueden listar volúmenes"

echo -e "\n${BLUE}🌐 REDES DE DOCKER${NC}"
echo "=================="
docker network ls 2>/dev/null || echo "No se pueden listar redes"

echo -e "\n${BLUE}📁 ESTRUCTURA DE DIRECTORIOS DEL PROYECTO${NC}"
echo "=========================================="
echo "Buscando directorios del proyecto..."

# Find project directories
find /home -name "*oidivi*" -type d 2>/dev/null | head -20
find /opt -name "*oidivi*" -type d 2>/dev/null | head -10
find /var -name "*oidivi*" -type d 2>/dev/null | head -10
find /root -name "*oidivi*" -type d 2>/dev/null | head -10

echo -e "\n${BLUE}🔍 ARCHIVOS DOCKER-COMPOSE${NC}"
echo "=========================="
echo "Buscando archivos docker-compose..."
find / -name "docker-compose*.yml" -o -name "docker-compose*.yaml" 2>/dev/null | head -20

echo -e "\n${BLUE}🔍 ARCHIVOS DOCKERFILE${NC}"
echo "======================"
echo "Buscando Dockerfiles..."
find / -name "Dockerfile*" 2>/dev/null | head -20

echo -e "\n${BLUE}📋 CONFIGURACIÓN DE CONTENEDORES ESPECÍFICOS${NC}"
echo "============================================="

# Check for oidivi containers specifically
OIDIVI_CONTAINERS=$(docker ps --format "{{.Names}}" | grep -i oidivi 2>/dev/null)

if [ ! -z "$OIDIVI_CONTAINERS" ]; then
    for container in $OIDIVI_CONTAINERS; do
        echo -e "\n${CYAN}🔧 CONTENEDOR: $container${NC}"
        echo "================================"
        
        echo "Imagen:"
        docker inspect $container --format='{{.Config.Image}}' 2>/dev/null
        
        echo -e "\nVolúmenes montados:"
        docker inspect $container --format='{{range .Mounts}}{{.Source}} -> {{.Destination}} ({{.Type}}){{"\n"}}{{end}}' 2>/dev/null
        
        echo -e "\nPuertos expuestos:"
        docker inspect $container --format='{{range $p, $conf := .NetworkSettings.Ports}}{{$p}} -> {{(index $conf 0).HostPort}}{{"\n"}}{{end}}' 2>/dev/null
        
        echo -e "\nVariables de entorno:"
        docker inspect $container --format='{{range .Config.Env}}{{.}}{{"\n"}}{{end}}' 2>/dev/null | grep -E "(APP_|DB_|FILESYSTEM_|URL)" | sort
        
        echo -e "\nDirectorio de trabajo:"
        docker inspect $container --format='{{.Config.WorkingDir}}' 2>/dev/null
        
        echo -e "\nComando de inicio:"
        docker inspect $container --format='{{.Config.Cmd}}' 2>/dev/null
        
        echo -e "\nRed:"
        docker inspect $container --format='{{range $net, $conf := .NetworkSettings.Networks}}{{$net}}: {{$conf.IPAddress}}{{"\n"}}{{end}}' 2>/dev/null
    done
else
    echo "No se encontraron contenedores con 'oidivi' en el nombre"
fi

echo -e "\n${BLUE}📂 ESTRUCTURA INTERNA DE CONTENEDORES${NC}"
echo "====================================="

if [ ! -z "$OIDIVI_CONTAINERS" ]; then
    for container in $OIDIVI_CONTAINERS; do
        echo -e "\n${CYAN}📁 Estructura de $container:${NC}"
        echo "docker exec $container find /var/www -type d -maxdepth 3 2>/dev/null | head -20"
        docker exec $container find /var/www -type d -maxdepth 3 2>/dev/null | head -20 || echo "No se puede acceder al contenedor"
        
        echo -e "\n${CYAN}🔧 Configuración PHP en $container:${NC}"
        docker exec $container php --ini 2>/dev/null | head -10 || echo "PHP no disponible en el contenedor"
        
        echo -e "\n${CYAN}🌐 Configuración Nginx en $container:${NC}"
        docker exec $container find /etc/nginx -name "*.conf" 2>/dev/null | head -10 || echo "Nginx no disponible en el contenedor"
        
        echo -e "\n${CYAN}📋 Archivos .env en $container:${NC}"
        docker exec $container find /var/www -name ".env*" 2>/dev/null || echo "No se encontraron archivos .env"
    done
fi

echo -e "\n${BLUE}🔍 CONFIGURACIÓN DE NGINX (HOST)${NC}"
echo "================================"
echo "Buscando configuración de Nginx en el host..."
find /etc/nginx -name "*.conf" 2>/dev/null | head -10
find /etc/nginx -name "*oidivi*" 2>/dev/null

echo -e "\n${BLUE}🔍 CONFIGURACIÓN DE APACHE (HOST)${NC}"
echo "================================="
echo "Buscando configuración de Apache en el host..."
find /etc/apache2 -name "*oidivi*" 2>/dev/null
find /etc/httpd -name "*oidivi*" 2>/dev/null

echo -e "\n${BLUE}🔍 SERVICIOS SYSTEMD${NC}"
echo "==================="
echo "Servicios relacionados con el proyecto:"
systemctl list-units --type=service | grep -i oidivi 2>/dev/null || echo "No se encontraron servicios systemd relacionados"

echo -e "\n${BLUE}🔍 PROCESOS EN EJECUCIÓN${NC}"
echo "========================"
echo "Procesos relacionados con el proyecto:"
ps aux | grep -i oidivi | grep -v grep || echo "No se encontraron procesos relacionados"

echo -e "\n${BLUE}🔍 PUERTOS EN USO${NC}"
echo "=================="
echo "Puertos abiertos en el sistema:"
netstat -tlnp 2>/dev/null | grep -E ":(80|443|8000|8080|3000|9000)" || ss -tlnp | grep -E ":(80|443|8000|8080|3000|9000)" 2>/dev/null || echo "No se puede obtener información de puertos"

echo -e "\n${BLUE}🔍 CONFIGURACIÓN DE FIREWALL${NC}"
echo "============================"
echo "Estado del firewall:"
ufw status 2>/dev/null || iptables -L -n 2>/dev/null | head -20 || echo "No se puede obtener información del firewall"

echo -e "\n${BLUE}🔍 LOGS RECIENTES${NC}"
echo "=================="
echo "Logs de Docker:"
docker logs --tail=20 $(docker ps --format "{{.Names}}" | grep -i oidivi | head -1) 2>/dev/null || echo "No se pueden obtener logs de Docker"

echo -e "\n${BLUE}🔍 ESPACIO EN DISCO${NC}"
echo "==================="
df -h | grep -E "(/$|/var|/home|/opt)"

echo -e "\n${BLUE}🔍 MEMORIA Y CPU${NC}"
echo "================"
free -h
echo ""
lscpu | grep -E "(Model name|CPU\(s\)|Thread|Core)"

echo -e "\n${BLUE}🔍 CONFIGURACIÓN DE DNS${NC}"
echo "======================="
cat /etc/resolv.conf 2>/dev/null || echo "No se puede leer configuración DNS"

echo -e "\n${GREEN}✅ AUDITORÍA COMPLETADA${NC}"
echo "======================="
echo -e "\n${YELLOW}📋 INFORMACIÓN RECOPILADA:${NC}"
echo "- Información del sistema y Docker"
echo "- Contenedores y sus configuraciones"
echo "- Estructura de directorios del proyecto"
echo "- Configuración de servicios web"
echo "- Puertos y redes"
echo "- Logs y recursos del sistema"

echo -e "\n${CYAN}📤 PRÓXIMO PASO:${NC}"
echo "Envía toda esta información para poder comparar con tu entorno local"
echo "y crear la configuración sincronizada."