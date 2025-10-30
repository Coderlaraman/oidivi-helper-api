# Guía Completa de Despliegue - Configuración de Storage

## 🎯 Objetivo
Esta guía te ayudará a configurar correctamente el sistema de almacenamiento de archivos (fotos y videos de perfil) tanto en desarrollo local como en producción.

## 🔍 Problemas Identificados y Solucionados

### Problemas Originales:
1. **Falta de volúmenes persistentes** en Docker Compose
2. **Enlaces simbólicos no configurados** (`php artisan storage:link`)
3. **Nginx no servía archivos de storage** correctamente
4. **Permisos insuficientes** en directorios de storage
5. **CORS no configurado** para archivos estáticos

### Soluciones Implementadas:
✅ Volúmenes persistentes en Docker Compose  
✅ Script de inicialización automática  
✅ Configuración de Nginx para servir storage  
✅ Permisos correctos y enlaces simbólicos  
✅ Headers CORS para acceso desde frontend  

---

## 🚀 DESPLIEGUE EN PRODUCCIÓN

### Paso 1: Preparación del Servidor

```bash
# Conectar al servidor de producción
ssh tu-usuario@tu-servidor.com

# Navegar al directorio del proyecto
cd /ruta/a/tu/proyecto/oidivi-helper-api
```

### Paso 2: Backup de Seguridad (IMPORTANTE)

```bash
# Crear backup de la base de datos
docker exec oidivi-helper-api-mysql-1 mysqldump -u root -p${DB_ROOT_PASSWORD} oidivi_helper > backup_$(date +%Y%m%d_%H%M%S).sql

# Crear backup de archivos existentes (si los hay)
sudo cp -r storage/app/public storage_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "No hay archivos previos"
```

### Paso 3: Detener Servicios Actuales

```bash
# Detener contenedores actuales
docker-compose down

# Verificar que no hay contenedores corriendo
docker ps
```

### Paso 4: Actualizar Código

```bash
# Si usas Git (recomendado)
git pull origin main

# O si subes archivos manualmente, asegúrate de que todos los archivos nuevos estén presentes:
# - docker/scripts/init-storage.sh
# - docker/nginx/default.conf (actualizado)
# - docker-compose.yml (actualizado)
# - Dockerfile (actualizado)
```

### Paso 5: Verificar Archivos Críticos

```bash
# Verificar que el script existe y es ejecutable
ls -la docker/scripts/init-storage.sh
chmod +x docker/scripts/init-storage.sh

# Verificar configuración de Nginx
cat docker/nginx/default.conf | grep -A 10 "location /storage/"
```

### Paso 6: Despliegue

```bash
# Construir y levantar servicios
docker-compose up -d --build

# Verificar que los contenedores están corriendo
docker-compose ps

# Verificar logs del contenedor API
docker-compose logs -f api
```

### Paso 7: Verificación Post-Despliegue

```bash
# Verificar que el enlace simbólico existe
docker exec oidivi-helper-api-api-1 ls -la /var/www/html/public/storage

# Verificar permisos de storage
docker exec oidivi-helper-api-api-1 ls -la /var/www/html/storage/app/public/

# Verificar que Nginx está sirviendo archivos
curl -I http://localhost:8000/storage/test.jpg
```

---

## 💻 CONFIGURACIÓN EN DESARROLLO LOCAL

### Paso 1: Actualizar Código Local

```bash
# En tu máquina local, navegar al proyecto
cd /ruta/a/tu/proyecto/oidivi-helper-api

# Asegurarte de tener los últimos cambios
git pull origin main
```

### Paso 2: Reconstruir Contenedores de Desarrollo

```bash
# Detener contenedores actuales
docker-compose -f docker-compose.dev.yml down

# Reconstruir con los nuevos cambios
docker-compose -f docker-compose.dev.yml up -d --build

# Verificar logs
docker-compose -f docker-compose.dev.yml logs -f api
```

### Paso 3: Verificación Local

```bash
# Verificar enlace simbólico
docker exec oidivi-helper-api-api-1 ls -la /var/www/html/public/storage

# Probar subida de archivo (desde el frontend o Postman)
# URL: http://localhost:8000/api/v1/profile/uploadProfilePhoto
```

---

## 🔧 COMANDOS DE VERIFICACIÓN Y TROUBLESHOOTING

### Verificar Estado del Sistema

```bash
# Estado de contenedores
docker-compose ps

# Logs del API
docker-compose logs api

# Logs de Nginx
docker exec oidivi-helper-api-api-1 tail -f /var/log/nginx/error.log

# Verificar volúmenes
docker volume ls | grep oidivi
```

### Verificar Configuración de Storage

```bash
# Entrar al contenedor
docker exec -it oidivi-helper-api-api-1 bash

# Dentro del contenedor:
ls -la /var/www/html/storage/app/public/
ls -la /var/www/html/public/storage
df -h /var/www/html/storage
```

### Verificar Permisos

```bash
# Verificar propietario y permisos
docker exec oidivi-helper-api-api-1 ls -la /var/www/html/storage/app/public/

# Debería mostrar:
# drwxrwxr-x www-data www-data profile-photos/
# drwxrwxr-x www-data www-data profile-videos/
```

### Probar Subida de Archivos

```bash
# Crear archivo de prueba
docker exec oidivi-helper-api-api-1 touch /var/www/html/storage/app/public/test.txt

# Verificar que es accesible vía web
curl -I http://localhost:8000/storage/test.txt
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS COMUNES

### Problema: "404 Not Found" al acceder a archivos

**Solución:**
```bash
# Verificar enlace simbólico
docker exec oidivi-helper-api-api-1 ls -la /var/www/html/public/storage

# Si no existe, recrearlo
docker exec oidivi-helper-api-api-1 ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
```

### Problema: "Permission denied" al subir archivos

**Solución:**
```bash
# Corregir permisos
docker exec oidivi-helper-api-api-1 chown -R www-data:www-data /var/www/html/storage
docker exec oidivi-helper-api-api-1 chmod -R 775 /var/www/html/storage
```

### Problema: CORS al acceder desde frontend

**Verificación:**
```bash
# Verificar headers CORS
curl -H "Origin: https://oidivi-helper.com" -I http://api.oidivi-helper.com/storage/test.jpg
```

### Problema: Archivos se pierden al reiniciar contenedor

**Verificación:**
```bash
# Verificar que los volúmenes están configurados
docker-compose config | grep -A 5 volumes

# Verificar que los volúmenes existen
docker volume ls | grep storage
```

---

## 📋 CHECKLIST DE DESPLIEGUE

### Pre-despliegue:
- [ ] Backup de base de datos realizado
- [ ] Backup de archivos existentes realizado
- [ ] Código actualizado en servidor
- [ ] Script init-storage.sh es ejecutable

### Durante el despliegue:
- [ ] Contenedores detenidos correctamente
- [ ] Build completado sin errores
- [ ] Contenedores iniciados correctamente
- [ ] Logs no muestran errores críticos

### Post-despliegue:
- [ ] Enlace simbólico creado correctamente
- [ ] Permisos de storage configurados
- [ ] Nginx sirve archivos de storage
- [ ] Frontend puede subir archivos
- [ ] Frontend puede visualizar archivos
- [ ] CORS configurado correctamente

---

## 🔄 MANTENIMIENTO CONTINUO

### Monitoreo de Espacio en Disco

```bash
# Verificar espacio usado por volúmenes
docker system df -v

# Verificar espacio en storage
docker exec oidivi-helper-api-api-1 du -sh /var/www/html/storage/app/public/
```

### Limpieza Periódica

```bash
# Limpiar archivos temporales (si los hay)
docker exec oidivi-helper-api-api-1 find /var/www/html/storage/app/public/temp -type f -mtime +7 -delete

# Limpiar imágenes Docker no utilizadas
docker image prune -f
```

### Backup Automático (Recomendado)

```bash
# Crear script de backup automático
cat > backup_storage.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker run --rm -v oidivi-helper-api_storage_data:/data -v $(pwd):/backup alpine tar czf /backup/storage_backup_$DATE.tar.gz -C /data .
EOF

chmod +x backup_storage.sh

# Agregar a crontab para backup diario
# 0 2 * * * /ruta/al/backup_storage.sh
```

---

## 📞 CONTACTO Y SOPORTE

Si encuentras problemas durante el despliegue:

1. **Revisa los logs**: `docker-compose logs api`
2. **Verifica la configuración**: Usa los comandos de verificación
3. **Consulta este documento**: Busca en la sección de troubleshooting
4. **Documenta el error**: Guarda logs y mensajes de error específicos

---

## 📝 NOTAS IMPORTANTES

- **Siempre hacer backup** antes de desplegar cambios en producción
- **Probar en desarrollo** antes de aplicar en producción  
- **Monitorear logs** después del despliegue
- **Verificar funcionalidad** completa después de cada despliegue
- **Los volúmenes Docker** persisten los archivos entre reinicios
- **Los permisos** son críticos para el funcionamiento correcto