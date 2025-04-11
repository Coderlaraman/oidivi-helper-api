# Guía de Implementación de Solicitudes de Servicio

## Estructura de Datos

### Modelos Relacionados
- `ServiceRequest`: Modelo principal para las solicitudes de servicio
- `Category`: Modelo para las categorías
- `categorizables`: Tabla polimórfica para la relación entre categorías y otros modelos

### Relaciones
- `ServiceRequest` tiene una relación polimórfica `morphToMany` con `Category`
- Las categorías se pueden asociar a múltiples tipos de modelos a través de la tabla `categorizables`

## API Endpoints

### Para Usuarios Comunes
- `GET /api/v1/categories`: Listar categorías disponibles
- `POST /api/v1/service-requests`: Crear nueva solicitud de servicio
- `GET /api/v1/service-requests`: Listar solicitudes de servicio
- `GET /api/v1/service-requests/{id}`: Obtener detalles de una solicitud

### Para Administradores
- `GET /api/v1/admin/categories`: Gestión completa de categorías
- `GET /api/v1/admin/service-requests`: Listar todas las solicitudes
- `GET /api/v1/admin/service-requests/{id}`: Detalles de una solicitud

## Pruebas en Postman

### 1. Obtener Categorías Disponibles
```http
GET /api/v1/categories
Headers:
  Authorization: Bearer {token}
  Accept: application/json
```

### 2. Crear Nueva Solicitud de Servicio
```http
POST /api/v1/service-requests
Headers:
  Authorization: Bearer {token}
  Accept: application/json
  Content-Type: application/json

Body:
{
    "title": "Necesito un plomero",
    "description": "Busco un plomero para reparar una fuga en el baño",
    "address": "Calle Principal 123",
    "zip_code": "12345",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "budget": 150.00,
    "visibility": "public",
    "payment_method": "cash",
    "service_type": "one_time",
    "priority": "medium",
    "due_date": "2024-03-15",
    "category_ids": [1, 2, 3] // IDs de las categorías seleccionadas
}
```

## Implementación en Next.js

### 1. Componente de Formulario de Creación

```typescript
// components/ServiceRequestForm.tsx
import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useRouter } from 'next/router';

interface Category {
  id: number;
  name: string;
  description: string;
}

interface ServiceRequestFormData {
  title: string;
  description: string;
  address: string;
  zip_code: string;
  latitude: number;
  longitude: number;
  budget: number;
  visibility: 'public' | 'private';
  payment_method: string;
  service_type: 'one_time' | 'recurring';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  due_date: string;
  category_ids: number[];
}

export default function ServiceRequestForm() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(false);
  const router = useRouter();
  
  const { register, handleSubmit, formState: { errors } } = useForm<ServiceRequestFormData>();

  useEffect(() => {
    // Cargar categorías disponibles
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/v1/categories');
        const data = await response.json();
        setCategories(data);
      } catch (error) {
        console.error('Error al cargar categorías:', error);
      }
    };

    fetchCategories();
  }, []);

  const onSubmit = async (data: ServiceRequestFormData) => {
    setLoading(true);
    try {
      const response = await fetch('/api/v1/service-requests', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(data),
      });

      if (response.ok) {
        router.push('/service-requests');
      } else {
        throw new Error('Error al crear la solicitud');
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      {/* Campos del formulario */}
      <div>
        <label>Título</label>
        <input {...register('title', { required: true })} />
        {errors.title && <span>Este campo es requerido</span>}
      </div>

      {/* Selector de Categorías */}
      <div>
        <label>Categorías</label>
        <select
          multiple
          {...register('category_ids', { required: true })}
        >
          {categories.map(category => (
            <option key={category.id} value={category.id}>
              {category.name}
            </option>
          ))}
        </select>
        {errors.category_ids && <span>Debes seleccionar al menos una categoría</span>}
      </div>

      {/* Resto de campos del formulario */}
      
      <button type="submit" disabled={loading}>
        {loading ? 'Creando...' : 'Crear Solicitud'}
      </button>
    </form>
  );
}
```

### 2. Página de Creación

```typescript
// pages/service-requests/create.tsx
import ServiceRequestForm from '../../components/ServiceRequestForm';

export default function CreateServiceRequest() {
  return (
    <div>
      <h1>Crear Nueva Solicitud de Servicio</h1>
      <ServiceRequestForm />
    </div>
  );
}
```

### 3. API Route para Categorías

```typescript
// pages/api/categories.ts
import { NextApiRequest, NextApiResponse } from 'next';

export default async function handler(
  req: NextApiRequest,
  res: NextApiResponse
) {
  if (req.method === 'GET') {
    try {
      const response = await fetch(`${process.env.API_URL}/api/v1/categories`, {
        headers: {
          'Authorization': `Bearer ${req.headers.authorization?.split(' ')[1]}`,
        },
      });

      const data = await response.json();
      res.status(response.status).json(data);
    } catch (error) {
      res.status(500).json({ error: 'Error al obtener categorías' });
    }
  } else {
    res.setHeader('Allow', ['GET']);
    res.status(405).end(`Method ${req.method} Not Allowed`);
  }
}
```

## Consideraciones Importantes

1. **Manejo de Categorías**:
   - Las categorías se cargan solo una vez al montar el componente
   - Se muestran en un selector múltiple
   - Se validan en el frontend y backend

2. **Seguridad**:
   - Validar tokens de autenticación
   - Implementar CSRF protection
   - Validar datos en frontend y backend

3. **UX/UI**:
   - Mostrar feedback de carga
   - Manejar errores apropiadamente
   - Validación en tiempo real

4. **Optimización**:
   - Implementar caché para categorías
   - Lazy loading de componentes
   - Optimización de imágenes y recursos

## Próximos Pasos

1. Implementar validación de formularios más robusta
2. Agregar manejo de archivos adjuntos
3. Implementar búsqueda y filtrado
4. Agregar funcionalidad de edición
5. Implementar sistema de notificaciones 