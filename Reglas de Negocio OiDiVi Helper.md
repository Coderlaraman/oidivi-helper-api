# 📌 Documento Unificado de Reglas de Negocio - OiDiVi Helper

## 1️⃣ Introducción
Este documento unifica las reglas de negocio de la plataforma OiDiVi Helper, abarcando tanto la versión web como la aplicación móvil. Se estructura de manera que las reglas generales sean aplicables a ambas plataformas, y las especificidades de cada una se detallen por separado.

---

## 2️⃣ Reglas Generales de Negocio (Aplican a Web y Mobile)
### 🔹 2.1 Usuarios y Roles
- Dos tipos de usuarios principales:
  - **Usuarios comunes**: Buscan servicios y contratan helpers.
  - **Helpers**: Ofrecen sus servicios en la plataforma.
- Creación y gestión de perfiles para ambos tipos de usuarios.
- Verificación de identidad mediante correo electrónico o número de teléfono.
- Ubicación basada en ZIP code para operación en regiones específicas.

### 🔹 2.2 Publicación y Contratación de Servicios
- Usuarios pueden publicar solicitudes detalladas indicando:
  - Descripción del trabajo.
  - Ubicación y requisitos específicos.
  - Fecha y presupuesto estimado.
- Los helpers pueden aceptar ofertas y comunicarse con el usuario antes de cerrar un trato.
- Control de privacidad en la visibilidad de las ofertas.

### 🔹 2.3 Búsqueda y Filtrado Avanzado
- Búsqueda por:
  - Texto libre.
  - Filtros avanzados (ubicación, categoría, calificación y precio).
- Uso de **Google Maps API** u otras alternativas en este contexto 
  para mostrar ubicaciones cercanas.
- Radio de búsqueda dinámico basado en distancia entre usuario y helper.

### 🔹 2.4 Comunicación entre Usuarios
- **Mensajería instantánea** para negociación.
- **Notificaciones push** sobre mensajes, confirmaciones y actualizaciones.
- Envío de archivos adjuntos (ej. imágenes de trabajos previos).

### 🔹 2.5 Gestión de Pagos y Facturación
- **Integración con pasarelas de pago** (Stripe, PayPal, etc.).
- Pagos directos dentro de la plataforma.
- Generación automática de facturas electrónicas.
- Retención de pagos hasta confirmación de servicio satisfactorio.

### 🔹 2.6 Calificación y Reputación
- Sistema de calificación de **1 a 5 estrellas**.
- Reseñas escritas para feedback.
- Reputación calculada en base a calificaciones y comentarios.

### 🔹 2.7 Seguridad y Privacidad
- Protección de datos conforme a normativas internacionales.
- Autenticación y control de acceso robustos.
- Verificación manual de perfiles de helpers.
- Encriptación de datos sensibles.

---

## 3️⃣ Reglas Específicas para la Plataforma Web
### 🔹 3.1 Funcionalidades Clave
- **Interfaz web intuitiva** optimizada para experiencia de usuario.
- Integración con **OiDiVi Skills** para mostrar perfiles de helpers con:
  - Documentos (PDFs, currículum).
  - Imágenes (certificaciones, muestras de trabajo).
  - Videos (demostraciones de habilidades).
- **Soporte multilingüe** para expansión global.
- **Panel de administración** para gestión de usuarios, pagos y contenido.

---

## 4️⃣ Reglas Específicas para la Aplicación Móvil
### 🔹 4.1 Registro y Autenticación
- Registro con correo electrónico y verificación obligatoria.
- Autenticación mediante **JWT**.
- Posibilidad de login con redes sociales (opcional).

### 🔹 4.2 Publicación de Solicitudes de Servicio
- Ubicación definida mediante **API de LocationIQ**.
- Inclusión de descripción, precio base y multimedia (fotos/videos).
- Geocodificación y almacenamiento de coordenadas.
- Opción de cancelación antes de aceptación por un helper.

### 🔹 4.3 Asignación y Gestión de Helpers
- Visualización de solicitudes activas en mapa en tiempo real.
- Envío de contraofertas con precios ajustados.
- Restricción de múltiples ofertas por el mismo helper en una solicitud.
- Bloqueo de nuevas ofertas una vez asignado un helper.

### 🔹 4.4 Seguimiento en Tiempo Real
- Geolocalización en tiempo real al aceptar un servicio.
- Vista de ubicación del helper en el mapa.
- Notificación cuando el helper esté cerca del destino.
- Alerta si el helper desactiva la geolocalización antes de llegar.

### 🔹 4.5 Finalización y Evaluación
- Marcar la solicitud como **Completada** al finalizar.
- Calificación y comentarios por ambas partes.
- Suspensión temporal de helpers con calificaciones negativas repetitivas.

### 🔹 4.6 Restricciones y Seguridad
- Un usuario no puede aceptar su propia solicitud.
- Restricción para que un usuario rechazado no pueda ofertar de nuevo.
- Verificación de ubicaciones para evitar datos falsos.
- Código de seguridad para confirmar inicio del servicio en persona.

---

## 5️⃣ Interacciones Clave entre Funcionalidades
🔗 **Publicación de servicios** ↔ **Búsqueda avanzada**: Búsqueda eficiente mediante filtros y texto.
🔗 **Contratación** ↔ **Pagos seguros**: El pago solo se realiza tras la aceptación de la oferta.
🔗 **Mensajería** ↔ **Geolocalización**: Permite verificar cercanía antes de aceptar un servicio.
🔗 **Sistema de calificaciones** ↔ **Reputación de usuario**: Mayor puntaje mejora visibilidad del helper.
🔗 **Verificación de identidad** ↔ **Seguridad**: Solo usuarios verificados pueden publicar y contratar servicios.

---

## 6️⃣ Buenas Prácticas
✅ **Modularidad y escalabilidad**: Arquitectura flexible para futuras expansiones.
✅ **UX/UI optimizada**: Interfaces claras y amigables.
✅ **Pruebas automatizadas**: Uso de pruebas unitarias e integración.
✅ **Código limpio y documentado**: Facilita mantenimiento y futuras mejoras.
✅ **Monitorización y métricas**: Implementación de sistema de monitoreo de rendimiento.

---

📌 **Conclusión**
Este documento centraliza y organiza todas las reglas de negocio de OiDiVi Helper, garantizando coherencia entre las plataformas web y mobile. Facilita el desarrollo, mantenimiento y escalabilidad de la plataforma. 🚀

