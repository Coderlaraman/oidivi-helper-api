# Guía de Implementación: Edición de Solicitudes de Servicio

## Descripción General
Esta guía detalla la implementación de la funcionalidad de edición de solicitudes de servicio en el frontend utilizando Next.js 14 con App Router.

## Estructura de Archivos Recomendada

```
app/
├── (dashboard)/
│   └── service-requests/
│       └── [id]/
│           └── edit/
│               ├── page.tsx
│               └── loading.tsx
├── components/
│   └── service-requests/
│       ├── ServiceRequestForm.tsx
│       └── EditServiceRequest.tsx
└── lib/
    └── service-requests/
        ├── types.ts
        ├── schemas.ts
        └── actions.ts
```

## Tipos y Esquemas

```typescript
// lib/service-requests/types.ts
export interface ServiceRequest {
  id: number;
  title: string;
  description: string;
  address: string;
  zip_code: string;
  latitude: number;
  longitude: number;
  budget: number;
  visibility: 'public' | 'private';
  status: 'published' | 'in_progress' | 'completed' | 'canceled';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  payment_method: 'paypal' | 'credit_card' | 'bank_transfer';
  service_type: 'one_time' | 'recurring';
  due_date: string;
  category_ids: number[];
}

// lib/service-requests/schemas.ts
import { z } from 'zod';

export const serviceRequestSchema = z.object({
  title: z.string().min(5).max(255),
  description: z.string().min(20),
  address: z.string().min(5),
  zip_code: z.string().min(5),
  latitude: z.number(),
  longitude: z.number(),
  budget: z.number().positive(),
  visibility: z.enum(['public', 'private']),
  priority: z.enum(['low', 'medium', 'high', 'urgent']),
  payment_method: z.enum(['paypal', 'credit_card', 'bank_transfer']),
  service_type: z.enum(['one_time', 'recurring']),
  due_date: z.string().datetime(),
  category_ids: z.array(z.number()).min(1)
});
```

## Implementación de Acciones

```typescript
// lib/service-requests/actions.ts
import { ServiceRequest } from './types';

export async function getServiceRequest(id: number): Promise<ServiceRequest> {
  const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/user/service-requests/${id}`, {
    headers: {
      'Authorization': `Bearer ${getToken()}` // Implementa getToken según tu manejo de autenticación
    }
  });

  if (!response.ok) {
    throw new Error('Failed to fetch service request');
  }

  return response.json();
}

export async function updateServiceRequest(id: number, data: Partial<ServiceRequest>): Promise<ServiceRequest> {
  const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/user/service-requests/${id}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${getToken()}`
    },
    body: JSON.stringify(data)
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Failed to update service request');
  }

  return response.json();
}
```

## Componente de Formulario Reutilizable

```typescript
// components/service-requests/ServiceRequestForm.tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { serviceRequestSchema } from '@/lib/service-requests/schemas';
import type { ServiceRequest } from '@/lib/service-requests/types';

interface ServiceRequestFormProps {
  initialData?: ServiceRequest;
  onSubmit: (data: ServiceRequest) => Promise<void>;
  isLoading: boolean;
}

export function ServiceRequestForm({ initialData, onSubmit, isLoading }: ServiceRequestFormProps) {
  const form = useForm({
    resolver: zodResolver(serviceRequestSchema),
    defaultValues: initialData || {
      title: '',
      description: '',
      // ... otros campos con valores por defecto
    }
  });

  return (
    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
      {/* Implementa los campos del formulario según tu diseño UI */}
      <div>
        <label htmlFor="title" className="block text-sm font-medium text-gray-700">
          Título
        </label>
        <input
          type="text"
          {...form.register('title')}
          className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
        />
        {form.formState.errors.title && (
          <p className="mt-1 text-sm text-red-600">{form.formState.errors.title.message}</p>
        )}
      </div>
      
      {/* Implementa el resto de campos del formulario */}
      
      <button
        type="submit"
        disabled={isLoading}
        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        {isLoading ? 'Guardando...' : 'Guardar Cambios'}
      </button>
    </form>
  );
}
```

## Página de Edición

```typescript
// app/(dashboard)/service-requests/[id]/edit/page.tsx
import { Suspense } from 'react';
import { notFound } from 'next/navigation';
import { EditServiceRequest } from '@/components/service-requests/EditServiceRequest';

interface PageProps {
  params: {
    id: string;
  }
}

export default function EditServiceRequestPage({ params }: PageProps) {
  return (
    <div className="max-w-4xl mx-auto py-10">
      <h1 className="text-2xl font-bold mb-8">Editar Solicitud de Servicio</h1>
      <Suspense fallback={<div>Cargando...</div>}>
        <EditServiceRequest id={parseInt(params.id)} />
      </Suspense>
    </div>
  );
}
```

## Componente de Edición

```typescript
// components/service-requests/EditServiceRequest.tsx
'use client';

import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { ServiceRequestForm } from './ServiceRequestForm';
import { getServiceRequest, updateServiceRequest } from '@/lib/service-requests/actions';
import { useQuery, useMutation } from '@tanstack/react-query';
import { toast } from 'sonner';

interface EditServiceRequestProps {
  id: number;
}

export function EditServiceRequest({ id }: EditServiceRequestProps) {
  const router = useRouter();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const { data: serviceRequest, isLoading, error } = useQuery({
    queryKey: ['service-request', id],
    queryFn: () => getServiceRequest(id)
  });

  const mutation = useMutation({
    mutationFn: (data: ServiceRequest) => updateServiceRequest(id, data),
    onSuccess: () => {
      toast.success('Solicitud actualizada exitosamente');
      router.push('/service-requests');
      router.refresh();
    },
    onError: (error) => {
      toast.error(error.message || 'Error al actualizar la solicitud');
    }
  });

  if (isLoading) {
    return <div>Cargando...</div>;
  }

  if (error || !serviceRequest) {
    return <div>Error al cargar la solicitud</div>;
  }

  const handleSubmit = async (data: ServiceRequest) => {
    setIsSubmitting(true);
    try {
      await mutation.mutateAsync(data);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <ServiceRequestForm
      initialData={serviceRequest}
      onSubmit={handleSubmit}
      isLoading={isSubmitting}
    />
  );
}
```

## Instrucciones de Implementación

1. **Configuración Inicial**
   - Asegúrate de tener instaladas las dependencias necesarias:
     ```bash
     npm install @hookform/resolvers zod react-hook-form @tanstack/react-query sonner
     ```

2. **Implementación de la Estructura**
   - Crea la estructura de archivos como se muestra arriba
   - Implementa los tipos y esquemas según tu modelo de datos
   - Configura las acciones de API para interactuar con el backend

3. **Formulario y Validación**
   - Implementa el componente `ServiceRequestForm` con todos los campos necesarios
   - Asegúrate de manejar correctamente la validación con Zod
   - Implementa el manejo de errores y estados de carga

4. **Integración con el Router**
   - Configura las rutas en Next.js para la edición
   - Implementa la navegación y redirección después de guardar

5. **Estado y Caché**
   - Configura React Query para el manejo de estado y caché
   - Implementa la actualización optimista de la UI

6. **Pruebas**
   - Verifica que los permisos de edición funcionen correctamente
   - Prueba todos los casos de error y éxito
   - Asegúrate de que la validación funcione como se espera

## Consideraciones de Seguridad

1. **Autenticación**
   - Verifica que el usuario esté autenticado antes de permitir la edición
   - Implementa el manejo de tokens JWT en las peticiones

2. **Autorización**
   - Verifica que el usuario sea el propietario de la solicitud
   - Maneja apropiadamente los estados de la solicitud

3. **Validación**
   - Implementa validación tanto en el cliente como en el servidor
   - Sanitiza los datos antes de enviarlos al backend

## Mejores Prácticas

1. **Rendimiento**
   - Implementa carga diferida de componentes pesados
   - Utiliza caché apropiadamente con React Query

2. **UX**
   - Proporciona feedback inmediato al usuario
   - Implementa estados de carga y error apropiados
   - Usa toast notifications para mensajes de éxito/error

3. **Mantenibilidad**
   - Mantén el código modular y reutilizable
   - Documenta componentes y funciones importantes
   - Sigue las convenciones de nombres establecidas

## Ejemplo de Uso

```typescript
// Ejemplo de cómo usar el componente en una página
import { EditServiceRequest } from '@/components/service-requests/EditServiceRequest';

export default function Page({ params }: { params: { id: string } }) {
  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Editar Solicitud de Servicio</h1>
      <EditServiceRequest id={parseInt(params.id)} />
    </div>
  );
}
```

## Recursos Adicionales

- [Documentación de Next.js App Router](https://nextjs.org/docs/app)
- [React Hook Form Documentation](https://react-hook-form.com/)
- [TanStack Query Documentation](https://tanstack.com/query/latest)
- [Zod Documentation](https://zod.dev/) 