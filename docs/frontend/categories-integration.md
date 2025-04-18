# Integración de Categorías en el Frontend

## Descripción General
Este documento detalla cómo se implementa y gestiona el sistema de categorías en el contexto de usuarios comunes, específicamente para su uso en solicitudes de servicio y habilidades de usuario.

## Estructura de Base de Datos

### Tabla `categories`
```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla `categorizables` (Relación Polimórfica)
```sql
CREATE TABLE categorizables (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT,
    categorizable_id BIGINT,
    categorizable_type VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE INDEX categorizables_unique (category_id, categorizable_id, categorizable_type)
);
```

Esta estructura permite que las categorías se relacionen con diferentes modelos del sistema (ServiceRequest, Skill, etc.) a través de una relación polimórfica.

## Endpoint API para Categorías

### Obtener Todas las Categorías
```http
GET /api/v1/user/categories
Headers:
  Authorization: Bearer {token}
  Accept: application/json
```

#### Respuesta
```json
{
  "data": [
    {
      "id": 1,
      "name": "Plomería",
      "slug": "plomeria",
      "description": "Servicios relacionados con sistemas de agua y drenaje",
      "created_at": "2024-02-20T15:30:00Z",
      "updated_at": "2024-02-20T15:30:00Z"
    },
    // ... más categorías
  ],
  "message": "Categories retrieved successfully"
}
```

### Obtener una Categoría Específica
```http
GET /api/v1/user/categories/{id}
Headers:
  Authorization: Bearer {token}
  Accept: application/json
```

## Implementación en React/Next.js

### 1. Tipos TypeScript para Categorías
```typescript
// types/category.ts
interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  created_at: string;
  updated_at: string;
}

interface CategoriesResponse {
  data: Category[];
  message: string;
}
```

### 2. Hook para Gestionar Categorías
```typescript
// hooks/useCategories.ts
import { useQuery } from 'react-query';

export function useCategories() {
  return useQuery<CategoriesResponse>('categories', async () => {
    const response = await fetch('/api/v1/user/categories', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error('Error al obtener las categorías');
    }

    return response.json();
  });
}
```

### 3. Componente Selector de Categorías
```typescript
// components/CategorySelector.tsx
import { useCategories } from '../hooks/useCategories';

interface CategorySelectorProps {
  selectedCategories: number[];
  onChange: (categoryIds: number[]) => void;
  error?: string;
}

export function CategorySelector({ selectedCategories, onChange, error }: CategorySelectorProps) {
  const { data, isLoading, error: fetchError } = useCategories();

  if (isLoading) return <div>Cargando categorías...</div>;
  if (fetchError) return <div>Error al cargar las categorías</div>;

  return (
    <div className="space-y-2">
      <label className="block text-sm font-medium text-gray-700">
        Categorías
      </label>
      <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
        {data?.data.map(category => (
          <label
            key={category.id}
            className={`
              flex items-center p-3 rounded-lg border cursor-pointer
              ${selectedCategories.includes(category.id)
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'
              }
            `}
          >
            <input
              type="checkbox"
              className="hidden"
              checked={selectedCategories.includes(category.id)}
              onChange={(e) => {
                const newSelected = e.target.checked
                  ? [...selectedCategories, category.id]
                  : selectedCategories.filter(id => id !== category.id);
                onChange(newSelected);
              }}
            />
            <span className="ml-2">{category.name}</span>
          </label>
        ))}
      </div>
      {error && (
        <p className="mt-1 text-sm text-red-600">{error}</p>
      )}
    </div>
  );
}
```

### 4. Integración en el Formulario de Solicitud de Servicio
```typescript
// components/ServiceRequestForm.tsx
import { CategorySelector } from './CategorySelector';

export function ServiceRequestForm() {
  const [selectedCategories, setSelectedCategories] = useState<number[]>([]);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrors({});

    if (selectedCategories.length === 0) {
      setErrors(prev => ({
        ...prev,
        categories: 'Debes seleccionar al menos una categoría'
      }));
      return;
    }

    // Resto de la lógica de envío del formulario
    const formData = {
      // ... otros campos del formulario
      category_ids: selectedCategories
    };

    try {
      const response = await fetch('/api/v1/user/service-requests', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(formData)
      });

      if (!response.ok) {
        const error = await response.json();
        setErrors(error.errors || {});
        return;
      }

      // Manejar éxito
    } catch (error) {
      // Manejar error
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {/* Otros campos del formulario */}
      
      <CategorySelector
        selectedCategories={selectedCategories}
        onChange={setSelectedCategories}
        error={errors.categories}
      />

      {/* Resto del formulario */}
    </form>
  );
}
```

## Consideraciones Importantes

1. **Validación**:
   - Al menos una categoría debe ser seleccionada
   - El backend valida la existencia de las categorías seleccionadas
   - Las categorías deben estar activas para ser seleccionadas

2. **Optimización**:
   - Las categorías se cachean con React Query
   - La lista de categorías se carga una sola vez y se reutiliza
   - Se implementa manejo de estados de carga y error

3. **UX/UI**:
   - Selector visual con checkbox
   - Feedback inmediato de selección
   - Indicadores claros de error
   - Diseño responsive

4. **Seguridad**:
   - Todas las peticiones requieren autenticación
   - Validación en frontend y backend
   - Sanitización de datos

## Relaciones Polimórficas

Es importante entender que las categorías pueden estar relacionadas con:
- Solicitudes de Servicio (`ServiceRequest`)
- Habilidades de Usuario (`Skill`)
- Otros modelos futuros

Esto significa que:
1. Una categoría puede aparecer en múltiples contextos
2. La misma estructura de categorías se usa en diferentes partes del sistema
3. Las relaciones se manejan transparentemente en el backend

## Ejemplos de Uso

### 1. En Solicitudes de Servicio
```typescript
// Crear solicitud con categorías
const createServiceRequest = async (data) => {
  const response = await fetch('/api/v1/user/service-requests', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      ...data,
      category_ids: selectedCategories
    })
  });
  return response.json();
};
```

### 2. En Habilidades de Usuario
```typescript
// Actualizar habilidades con categorías
const updateUserSkills = async (skillData) => {
  const response = await fetch('/api/v1/user/skills', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      ...skillData,
      category_ids: selectedCategories
    })
  });
  return response.json();
};
```

## Próximos Pasos

1. Implementar búsqueda y filtrado de categorías
2. Añadir categorías destacadas o populares
3. Implementar sugerencias de categorías basadas en el contexto
4. Mejorar la UI del selector de categorías
5. Añadir subcategorías (si se requiere en el futuro) 