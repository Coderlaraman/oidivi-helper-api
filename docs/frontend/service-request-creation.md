# Guía de Implementación: Creación de Solicitudes de Servicio

## Estructura de Archivos

```
src/
├── app/
│   └── service-requests/
│       └── create/
│           └── page.tsx
├── components/
│   └── service-requests/
│       ├── ServiceRequestForm.tsx
│       └── CategorySelector.tsx
├── lib/
│   ├── api/
│   │   ├── user/
│   │   │   ├── categories.ts
│   │   │   └── service-requests.ts
│   │   └── admin/
│   │       └── categories.ts
│   └── types/
│       ├── service-request.ts
│       └── category.ts
└── hooks/
    └── useCategories.ts
```

## Tipos y Interfaces

### `src/lib/types/category.ts`

```typescript
export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
}

export interface CategoryResponse {
  data: Category[];
}
```

### `src/lib/types/service-request.ts`

```typescript
export interface ServiceRequestFormData {
  title: string;
  description: string;
  address: string;
  zip_code: string;
  latitude: number;
  longitude: number;
  budget: number;
  visibility: 'public' | 'private';
  payment_method: 'paypal' | 'credit_card' | 'bank_transfer';
  service_type: 'one_time' | 'recurring';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  due_date: string;
  category_ids: number[];
}

export interface ServiceRequestResponse {
  data: {
    id: number;
    title: string;
    description: string;
    status: string;
    categories: Category[];
    // ... otros campos
  };
  message: string;
}
```

## Hooks y Funciones de API

### `src/hooks/useCategories.ts`

```typescript
import { useQuery } from '@tanstack/react-query';
import { getCategories } from '@/lib/api/user/categories';

export const useCategories = () => {
  return useQuery({
    queryKey: ['categories'],
    queryFn: getCategories,
  });
};
```

### `src/lib/api/user/categories.ts`

```typescript
import { CategoryResponse } from '@/lib/types/category';

export const getCategories = async (): Promise<CategoryResponse> => {
  const response = await fetch('/api/v1/user/categories');
  if (!response.ok) {
    throw new Error('Error al obtener las categorías');
  }
  return response.json();
};
```

### `src/lib/api/user/service-requests.ts`

```typescript
import { ServiceRequestFormData, ServiceRequestResponse } from '@/lib/types/service-request';

export const createServiceRequest = async (data: ServiceRequestFormData): Promise<ServiceRequestResponse> => {
  const response = await fetch('/api/v1/user/service-requests', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });

  if (!response.ok) {
    throw new Error('Error al crear la solicitud de servicio');
  }

  return response.json();
};
```

## Componentes

### `src/components/service-requests/CategorySelector.tsx`

```typescript
import { Category } from '@/lib/types/category';
import { useCategories } from '@/hooks/useCategories';

interface CategorySelectorProps {
  selectedCategories: number[];
  onCategoryChange: (categoryIds: number[]) => void;
}

export const CategorySelector = ({ selectedCategories, onCategoryChange }: CategorySelectorProps) => {
  const { data, isLoading, error } = useCategories();

  if (isLoading) return <div>Cargando categorías...</div>;
  if (error) return <div>Error al cargar las categorías</div>;

  const handleCategoryToggle = (categoryId: number) => {
    const newSelected = selectedCategories.includes(categoryId)
      ? selectedCategories.filter(id => id !== categoryId)
      : [...selectedCategories, categoryId];
    onCategoryChange(newSelected);
  };

  return (
    <div className="space-y-4">
      <h3 className="text-lg font-medium">Selecciona las categorías</h3>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {data?.data.map((category: Category) => (
          <div
            key={category.id}
            className={`p-4 border rounded-lg cursor-pointer transition-colors ${
              selectedCategories.includes(category.id)
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'
            }`}
            onClick={() => handleCategoryToggle(category.id)}
          >
            <h4 className="font-medium">{category.name}</h4>
            {category.description && (
              <p className="text-sm text-gray-600 mt-1">{category.description}</p>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};
```

### `src/components/service-requests/ServiceRequestForm.tsx`

```typescript
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { CategorySelector } from './CategorySelector';
import { createServiceRequest } from '@/lib/api/user/service-requests';
import { ServiceRequestFormData } from '@/lib/types/service-request';

const serviceRequestSchema = z.object({
  title: z.string().min(1, 'El título es requerido'),
  description: z.string().min(20, 'La descripción debe tener al menos 20 caracteres'),
  address: z.string().min(1, 'La dirección es requerida'),
  zip_code: z.string().min(1, 'El código postal es requerido'),
  latitude: z.number().min(-90).max(90),
  longitude: z.number().min(-180).max(180),
  budget: z.number().min(0).max(999999.99),
  visibility: z.enum(['public', 'private']),
  payment_method: z.enum(['paypal', 'credit_card', 'bank_transfer']),
  service_type: z.enum(['one_time', 'recurring']),
  priority: z.enum(['low', 'medium', 'high', 'urgent']),
  due_date: z.string().datetime(),
  category_ids: z.array(z.number()).min(1, 'Debes seleccionar al menos una categoría'),
});

export const ServiceRequestForm = () => {
  const [selectedCategories, setSelectedCategories] = useState<number[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
  } = useForm<ServiceRequestFormData>({
    resolver: zodResolver(serviceRequestSchema),
  });

  const onSubmit = async (data: ServiceRequestFormData) => {
    try {
      setIsSubmitting(true);
      const response = await createServiceRequest({
        ...data,
        category_ids: selectedCategories,
      });
      // Manejar éxito (redirección, mensaje, etc.)
    } catch (error) {
      // Manejar error
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
      <div>
        <label htmlFor="title" className="block text-sm font-medium text-gray-700">
          Título
        </label>
        <input
          type="text"
          id="title"
          {...register('title')}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
        {errors.title && (
          <p className="mt-1 text-sm text-red-600">{errors.title.message}</p>
        )}
      </div>

      {/* Otros campos del formulario */}

      <CategorySelector
        selectedCategories={selectedCategories}
        onCategoryChange={setSelectedCategories}
      />

      <button
        type="submit"
        disabled={isSubmitting}
        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        {isSubmitting ? 'Creando...' : 'Crear Solicitud'}
      </button>
    </form>
  );
};
```

### `src/app/service-requests/create/page.tsx`

```typescript
import { ServiceRequestForm } from '@/components/service-requests/ServiceRequestForm';

export default function CreateServiceRequestPage() {
  return (
    <div className="max-w-4xl mx-auto py-8 px-4">
      <h1 className="text-2xl font-bold mb-6">Crear Nueva Solicitud de Servicio</h1>
      <ServiceRequestForm />
    </div>
  );
}
```

## Flujo de Implementación

1. **Obtener Categorías**:
   - El componente `CategorySelector` utiliza el hook `useCategories` para obtener la lista de categorías
   - Las categorías se cargan al montar el componente
   - Se muestran en una cuadrícula con opción de selección múltiple

2. **Formulario de Creación**:
   - Utiliza `react-hook-form` para el manejo del formulario
   - Implementa validación con Zod
   - Incluye todos los campos requeridos según la API
   - Maneja el estado de envío y errores

3. **Envío de Datos**:
   - Al enviar el formulario, se incluyen los IDs de las categorías seleccionadas
   - Se manejan los estados de carga y error
   - Se muestra feedback al usuario

## Consideraciones Importantes

1. **Separación de APIs**:
   - Las llamadas a la API de categorías para usuarios comunes están en `src/lib/api/user/categories.ts`
   - Las llamadas a la API de categorías para administradores están en `src/lib/api/admin/categories.ts`
   - No mezclar las implementaciones

2. **Validación**:
   - Implementar validación tanto en el frontend como en el backend
   - Usar Zod para la validación del formulario
   - Mostrar mensajes de error claros al usuario

3. **UX/UI**:
   - Proporcionar feedback visual durante la carga
   - Implementar manejo de errores amigable
   - Usar componentes de shadcn/ui para consistencia visual

4. **Seguridad**:
   - No exponer endpoints de administración a usuarios comunes
   - Validar permisos en el frontend
   - Implementar protección de rutas según el rol del usuario

## Próximos Pasos

1. Implementar la página de edición de solicitudes
2. Agregar funcionalidad de carga de imágenes
3. Implementar búsqueda y filtrado de categorías
4. Agregar validación de ubicación con mapas
5. Implementar sistema de notificaciones 