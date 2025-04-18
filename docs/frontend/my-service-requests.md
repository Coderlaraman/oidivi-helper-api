# Implementación de "Mis Solicitudes de Servicio"

## Descripción General
Esta guía detalla la implementación de la página "Mis Solicitudes de Servicio" donde los usuarios pueden ver y gestionar todas sus solicitudes de servicio publicadas.

## Endpoint API

### Obtener Mis Solicitudes
```http
GET /api/v1/user/service-requests?my_requests=true
Headers:
  Authorization: Bearer {token}
  Accept: application/json
```

### Parámetros de Filtrado Disponibles
- `status`: Filtrar por estado (published, in_progress, completed, canceled)
- `priority`: Filtrar por prioridad (low, medium, high, urgent)
- `service_type`: Filtrar por tipo de servicio (one_time, recurring)
- `category_ids`: Filtrar por categorías específicas
- `sort_by`: Ordenar por campo (created_at, due_date, budget, priority, status)
- `sort_direction`: Dirección del ordenamiento (asc, desc)

## Implementación en React/Next.js

### 1. Tipos TypeScript
```typescript
// types/service-request.ts
interface ServiceRequest {
  id: number;
  title: string;
  description: string;
  status: {
    code: 'published' | 'in_progress' | 'completed' | 'canceled';
    text: string;
  };
  priority: {
    code: 'low' | 'medium' | 'high' | 'urgent';
    text: string;
  };
  flags: {
    is_published: boolean;
    is_in_progress: boolean;
    is_completed: boolean;
    is_canceled: boolean;
    is_urgent: boolean;
    is_owner: boolean;
  };
  relationships: {
    categories: Category[];
    offers_count: number;
    has_contract: boolean;
  };
}

interface ServiceRequestsResponse {
  data: {
    items: ServiceRequest[];
    meta: {
      filters: {
        available_statuses: Record<string, string>;
        available_priorities: Record<string, string>;
        available_payment_methods: Record<string, string>;
        available_service_types: Record<string, string>;
      };
      pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
      };
    };
  };
}
```

### 2. Hook Personalizado para Gestionar Solicitudes
```typescript
// hooks/useServiceRequests.ts
import { useState, useEffect } from 'react';
import { useQuery } from 'react-query';

interface UseServiceRequestsParams {
  status?: string;
  priority?: string;
  serviceType?: string;
  categoryIds?: number[];
  sortBy?: string;
  sortDirection?: 'asc' | 'desc';
}

export function useServiceRequests(params: UseServiceRequestsParams) {
  const fetchServiceRequests = async () => {
    const queryParams = new URLSearchParams({
      my_requests: 'true',
      ...(params.status && { status: params.status }),
      ...(params.priority && { priority: params.priority }),
      ...(params.serviceType && { service_type: params.serviceType }),
      ...(params.categoryIds?.length && { category_ids: params.categoryIds.join(',') }),
      ...(params.sortBy && { sort_by: params.sortBy }),
      ...(params.sortDirection && { sort_direction: params.sortDirection })
    });

    const response = await fetch(`/api/v1/user/service-requests?${queryParams}`);
    if (!response.ok) throw new Error('Error al obtener las solicitudes');
    return response.json();
  };

  return useQuery(['serviceRequests', params], fetchServiceRequests);
}
```

### 3. Componente de Lista de Solicitudes
```typescript
// components/ServiceRequestsList.tsx
import { useServiceRequests } from '../hooks/useServiceRequests';

export function ServiceRequestsList() {
  const [filters, setFilters] = useState({
    status: '',
    priority: '',
    serviceType: '',
    categoryIds: [],
    sortBy: 'created_at',
    sortDirection: 'desc' as const
  });

  const { data, isLoading, error } = useServiceRequests(filters);

  if (isLoading) return <div>Cargando solicitudes...</div>;
  if (error) return <div>Error al cargar las solicitudes</div>;

  return (
    <div className="space-y-4">
      {/* Filtros */}
      <div className="flex gap-4 p-4 bg-white rounded-lg shadow">
        <select
          value={filters.status}
          onChange={(e) => setFilters(prev => ({ ...prev, status: e.target.value }))}
        >
          <option value="">Todos los estados</option>
          <option value="published">Publicadas</option>
          <option value="in_progress">En Progreso</option>
          <option value="completed">Completadas</option>
          <option value="canceled">Canceladas</option>
        </select>
        {/* Otros filtros... */}
      </div>

      {/* Lista de Solicitudes */}
      <div className="grid gap-4">
        {data?.data.items.map((request) => (
          <ServiceRequestCard
            key={request.id}
            request={request}
          />
        ))}
      </div>

      {/* Paginación */}
      <Pagination
        currentPage={data?.data.meta.pagination.current_page}
        totalPages={data?.data.meta.pagination.last_page}
        onPageChange={(page) => {/* Implementar cambio de página */}}
      />
    </div>
  );
}
```

### 4. Componente de Tarjeta de Solicitud
```typescript
// components/ServiceRequestCard.tsx
interface ServiceRequestCardProps {
  request: ServiceRequest;
}

export function ServiceRequestCard({ request }: ServiceRequestCardProps) {
  return (
    <div className="p-4 bg-white rounded-lg shadow">
      <div className="flex justify-between items-start">
        <h3 className="text-lg font-semibold">{request.title}</h3>
        <StatusBadge status={request.status} />
      </div>

      <p className="mt-2 text-gray-600">{request.description}</p>

      <div className="mt-4 flex gap-2">
        {request.relationships.categories.map(category => (
          <span
            key={category.id}
            className="px-2 py-1 bg-gray-100 rounded-full text-sm"
          >
            {category.name}
          </span>
        ))}
      </div>

      <div className="mt-4 flex justify-between items-center">
        <div className="flex items-center gap-2">
          <span className="text-sm text-gray-500">
            {request.relationships.offers_count} ofertas recibidas
          </span>
        </div>

        <div className="flex gap-2">
          {request.flags.is_published && (
            <button className="btn btn-primary">
              Ver Ofertas
            </button>
          )}
          {request.flags.is_in_progress && (
            <button className="btn btn-success">
              Ver Progreso
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
```

### 5. Página Principal
```typescript
// pages/my-service-requests.tsx
import { ServiceRequestsList } from '../components/ServiceRequestsList';

export default function MyServiceRequestsPage() {
  return (
    <div className="container mx-auto py-8">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Mis Solicitudes de Servicio</h1>
        <Link href="/service-requests/create">
          <a className="btn btn-primary">
            Nueva Solicitud
          </a>
        </Link>
      </div>

      <ServiceRequestsList />
    </div>
  );
}
```

## Características Principales

1. **Filtrado y Ordenamiento**:
   - Por estado (publicadas, en progreso, completadas, canceladas)
   - Por prioridad
   - Por tipo de servicio
   - Por categorías
   - Ordenamiento por diferentes campos

2. **Visualización de Datos**:
   - Estado actual de la solicitud
   - Número de ofertas recibidas
   - Categorías asociadas
   - Indicadores de urgencia
   - Fechas relevantes

3. **Acciones Disponibles**:
   - Ver detalles de la solicitud
   - Ver ofertas recibidas
   - Actualizar estado
   - Cancelar solicitud (si está permitido)

4. **Optimizaciones**:
   - Caché de datos con React Query
   - Paginación del lado del servidor
   - Lazy loading de componentes
   - Manejo de estados de carga y error

## Consideraciones de UX/UI

1. **Estados de Carga**:
   - Skeleton loaders durante la carga inicial
   - Indicadores de carga para acciones
   - Mensajes de error amigables

2. **Filtros**:
   - Filtros persistentes en la URL
   - Aplicación inmediata de filtros
   - Reset de filtros

3. **Responsive Design**:
   - Layout adaptativo para móviles
   - Interacciones touch-friendly
   - Menús colapsables en móvil

4. **Feedback Visual**:
   - Códigos de color por estado
   - Badges para prioridad
   - Indicadores de nuevas ofertas

## Próximos Pasos

1. Implementar búsqueda en tiempo real
2. Añadir filtros avanzados
3. Implementar vista detallada de solicitud
4. Añadir sistema de notificaciones
5. Mejorar la gestión de ofertas 