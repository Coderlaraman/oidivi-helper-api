#!/bin/bash

# Función para limpiar procesos al salir
cleanup() {
    echo "🛑 Deteniendo servicios..."
    if [ ! -z "$REVERB_PID" ]; then
        kill $REVERB_PID 2>/dev/null
        echo "   - Reverb detenido"
    fi
    if [ ! -z "$QUEUE_PID" ]; then
        kill $QUEUE_PID 2>/dev/null
        echo "   - Queue Worker detenido"
    fi
    exit 0
}

# Configurar trap para limpiar al salir
trap cleanup SIGTERM SIGINT

echo "🚀 Iniciando servicios de WebSocket y Queue Worker..."

# Esperar a que la base de datos esté disponible
echo "⏳ Esperando conexión a la base de datos..."
until php artisan migrate:status > /dev/null 2>&1; do
    echo "   - Esperando MySQL..."
    sleep 2
done
echo "✅ Base de datos disponible"

# Ejecutar migraciones si es necesario
echo "🔄 Verificando migraciones..."
php artisan migrate --force

echo "📡 Iniciando servidor Reverb..."
php artisan reverb:start --host=0.0.0.0 --port=8081 &
REVERB_PID=$!

echo "⚙️ Iniciando Queue Worker..."
php artisan queue:work --daemon &
QUEUE_PID=$!

echo "✅ Servicios iniciados:"
echo "   - Reverb PID: $REVERB_PID"
echo "   - Queue Worker PID: $QUEUE_PID"
echo "   - Reverb WebSocket: ws://localhost:8081"

# Mantener el script corriendo
wait