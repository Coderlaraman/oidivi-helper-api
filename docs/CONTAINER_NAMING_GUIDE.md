# 📦 Guía de Nombres de Contenedores Estandarizados

## 🎯 Objetivo

Esta guía documenta la estandarización de nombres de contenedores en el proyecto OiDiVi Helper para eliminar la inconsistencia de usar guiones medios (-) y bajos (_) mezclados en los nombres.

## 📋 Convención Adoptada

### ✅ Nombres Estandarizados

Utilizamos **guiones medios (-)** exclusivamente para separar palabras en los nombres de contenedores:

#### Producción
- **API**: `oidivi-helper-api`
- **MySQL**: `oidivi-helper-mysql`

#### Desarrollo
- **API**: `oidivi-helper-api-dev`
- **MySQL**: `oidivi-helper-mysql-dev`

### ❌ Nombres Antiguos (Deprecados)

Los siguientes nombres ya NO se utilizan:
- `oidivi-helper-api_api_1`
- `oidivi-helper-api_mysql_1`
- Cualquier combinación con guiones bajos (_)

## 🔧 Archivos Modificados

### Docker Compose
- `docker-compose.yml` - Agregado `container_name` para producción
- `docker-compose.dev.yml` - Agregado `container_name` para desarrollo

### Documentación
- `STORAGE_FIX_SUMMARY.md` - Actualizado con nuevos nombres
- `docs/CONTAINER_NAMING_GUIDE.md` - Esta guía (nuevo)

### Scripts
- `docker/scripts/verify-container-names.sh` - Script de verificación (nuevo)

## 🚀 Migración

### Pasos para Aplicar los Cambios

#### 1. Desarrollo Local
```bash
# Detener contenedores actuales
docker-compose -f docker-compose.dev.yml down --remove-orphans

# Limpiar contenedores antiguos (opcional)
docker container prune -f

# Levantar con nuevos nombres
docker-compose -f docker-compose.dev.yml up -d --build

# Verificar nombres
./docker/scripts/verify-container-names.sh
```

#### 2. Producción
```bash
# Detener contenedores actuales
docker-compose down --remove-orphans

# Limpiar contenedores antiguos (opcional)
docker container prune -f

# Levantar con nuevos nombres
docker-compose up -d --build

# Verificar nombres
./docker/scripts/verify-container-names.sh
```

## 🔍 Verificación

### Script de Verificación Automática
```bash
./docker/scripts/verify-container-names.sh
```

Este script:
- ✅ Detecta automáticamente el entorno (desarrollo/producción)
- ✅ Verifica que los contenedores estén corriendo
- ✅ Comprueba conectividad básica
- ✅ Detecta contenedores con nombres antiguos
- ✅ Proporciona instrucciones de limpieza

### Verificación Manual
```bash
# Listar contenedores actuales
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Verificar conectividad API
docker exec oidivi-helper-api curl -s http://localhost/api/health || echo "Endpoint no disponible"

# Verificar conectividad MySQL
docker exec oidivi-helper-mysql mysqladmin ping -h localhost
```

## 📚 Comandos Actualizados

### Comandos Comunes con Nuevos Nombres

#### Acceso a Contenedores
```bash
# Producción
docker exec -it oidivi-helper-api bash
docker exec -it oidivi-helper-mysql mysql -u root -p

# Desarrollo
docker exec -it oidivi-helper-api-dev bash
docker exec -it oidivi-helper-mysql-dev mysql -u root -p
```

#### Logs
```bash
# Producción
docker logs oidivi-helper-api
docker logs oidivi-helper-mysql

# Desarrollo
docker logs oidivi-helper-api-dev
docker logs oidivi-helper-mysql-dev
```

#### Artisan Commands
```bash
# Producción
docker exec oidivi-helper-api php artisan migrate
docker exec oidivi-helper-api php artisan storage:link

# Desarrollo
docker exec oidivi-helper-api-dev php artisan migrate
docker exec oidivi-helper-api-dev php artisan storage:link
```

## 🎯 Beneficios

### ✅ Consistencia
- Nombres uniformes en todos los entornos
- Fácil identificación de contenedores
- Eliminación de confusión por nombres mixtos

### ✅ Mantenibilidad
- Scripts más legibles y mantenibles
- Documentación más clara
- Menos errores por nombres incorrectos

### ✅ Escalabilidad
- Patrón claro para futuros servicios
- Fácil adición de nuevos entornos
- Convención estándar de la industria

## 🔄 Compatibilidad

### Retrocompatibilidad
- Los alias de red se mantienen iguales
- Las aplicaciones siguen funcionando sin cambios
- Solo cambian los nombres de contenedores, no la funcionalidad

### Migración Gradual
- Los contenedores antiguos pueden coexistir temporalmente
- No hay downtime requerido para la migración
- Cambio transparente para los usuarios finales

## 📞 Soporte

### Problemas Comunes

#### Contenedores con Nombres Antiguos
```bash
# Limpiar contenedores antiguos
docker-compose down --remove-orphans
docker container prune -f
```

#### Verificar Estado
```bash
# Usar script de verificación
./docker/scripts/verify-container-names.sh

# O verificar manualmente
docker ps --filter "name=oidivi-helper"
```

#### Recrear Contenedores
```bash
# Forzar recreación con nuevos nombres
docker-compose up -d --force-recreate
```

---

**Fecha de Implementación**: $(date +%Y-%m-%d)  
**Versión**: 1.0  
**Autor**: Sistema de Estandarización de Contenedores