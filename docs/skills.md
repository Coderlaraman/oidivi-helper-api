
# 📘 Lógica de Habilidades y Notificaciones - Sistema de Información OiDiVi

## 1️⃣ Visión General

En el sistema OiDiVi, todos los usuarios poseen el mismo rol (`user`), permitiéndoles actuar tanto como solicitantes de servicio (clientes) como como prestadores de servicios (helpers). Esta decisión ofrece flexibilidad y dinamismo, ya que cualquier usuario puede alternar entre estas funciones según su contexto.

La funcionalidad clave de emparejamiento de solicitudes de servicio se basa en **habilidades del usuario**, **categorías**, y **notificaciones push/websocket**.

---

## 2️⃣ Habilidades y Categorías

### 🔹 Estructura Lógica

- Cada usuario puede definir **una o varias habilidades**.
- Cada habilidad está asociada a **una o varias categorías**.
- Cada solicitud de servicio está asociada **obligatoriamente a una categoría**.
- Las categorías están conectadas de forma **polimórfica** a solicitudes y habilidades.

### 📐 Relación Polimórfica

- Tabla intermedia: `categoryables`
  - `category_id`
  - `categoryable_type` (`App\\Models\\Skill`, `App\\Models\\ServiceRequest`, etc.)
  - `categoryable_id`

---

## 3️⃣ Notificaciones por Coincidencia de Habilidades

### 🔔 Lógica de Matching

Cuando un usuario crea una nueva solicitud de servicio:
1. Se identifica su categoría.
2. Se busca a todos los usuarios que posean habilidades **asociadas a esa categoría**.
3. A esos usuarios se les emite:
   - Una notificación WebSocket (`Reverb` + `Laravel Echo`)
   - Una notificación Push (via FCM si corresponde)

### 🧱 Requisitos para recibir notificaciones

- El usuario debe tener **al menos una habilidad definida**.
- Esa habilidad debe estar **asociada a una categoría**.
- Esa categoría debe **coincidir** con la de la solicitud creada.

### 🔄 Comportamiento para usuarios sin habilidades

- No reciben notificaciones push/websocket.
- Se les muestra un mensaje en el dashboard indicando:
  > “No estás recibiendo notificaciones de solicitudes porque aún no has definido tus habilidades.”

---

## 4️⃣ Interfaz de Usuario y Gestión de Habilidades

### 🔧 Ubicación de la edición de habilidades

- Sección en el panel lateral: `Perfil → Habilidades`
- Se recomienda mantener una interfaz **modular y clara**, separada de la información personal.

### 🧩 Componente de Gestión

- Autocompletado para seleccionar habilidades existentes.
- Posibilidad de asociar múltiples habilidades.
- Mostrar habilidades actuales + opción de eliminar.
- Validación para asegurar que cada habilidad tenga al menos una categoría.

---

## 5️⃣ Consideraciones de UX y Seguridad

- Alertas activas para perfiles incompletos.
- No permitir ofertar sin tener habilidades.
- Evitar enviar notificaciones sin pertinencia.
- Posibilidad futura de auditar cambios de habilidades.

---

## 6️⃣ Conclusión

Este enfoque basado en habilidades y categorías proporciona un sistema escalable, preciso y profesional para emparejar usuarios con solicitudes relevantes. La flexibilidad del rol único de usuario complementa la estructura lógica de forma efectiva.

"""

# Guardar el contenido en un archivo Markdown
file_path = Path("/mnt/data/OiDiVi_Habilidades_Notificaciones.md")
file_path.write_text(document_content, encoding="utf-8")

file_path.name  # Para mostrar nombre del archivo descargable al usuario

