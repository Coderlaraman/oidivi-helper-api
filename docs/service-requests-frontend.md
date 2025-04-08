# Guía de Desarrollo Frontend - Solicitudes de Servicio

## 1. Estructura de Componentes

### 1.1 Componentes Principales
```
src/
  components/
    service-requests/
      ServiceRequestList.vue
      ServiceRequestForm.vue
      ServiceRequestDetail.vue
      ServiceRequestCard.vue
      ServiceRequestFilters.vue
      ServiceRequestMap.vue
```

### 1.2 Props y Eventos

#### ServiceRequestList.vue
```typescript
interface Props {
  filters: {
    search?: string;
    status?: 'published' | 'in_progress' | 'completed' | 'canceled';
    priority?: 'low' | 'medium' | 'high' | 'urgent';
    service_type?: 'one_time' | 'recurring';
    category_ids?: number[];
    min_budget?: number;
    max_budget?: number;
    due_date_start?: string;
    due_date_end?: string;
    zip_code?: string;
    payment_method?: 'paypal' | 'credit_card' | 'bank_transfer';
  };
  sort: {
    by: 'title' | 'created_at' | 'updated_at' | 'status' | 'priority' | 'due_date' | 'budget';
    direction: 'asc' | 'desc';
  };
  pagination: {
    page: number;
    per_page: number;
  };
}

interface Events {
  'update:filters': (filters: Props['filters']) => void;
  'update:sort': (sort: Props['sort']) => void;
  'update:pagination': (pagination: Props['pagination']) => void;
}
```

#### ServiceRequestForm.vue
```typescript
interface Props {
  initialData?: {
    title: string;
    description: string;
    address: string;
    zip_code: string;
    latitude: number;
    longitude: number;
    budget: number;
    visibility: 'public' | 'private';
    payment_method?: 'paypal' | 'credit_card' | 'bank_transfer';
    service_type: 'one_time' | 'recurring';
    priority: 'low' | 'medium' | 'high' | 'urgent';
    due_date?: string;
    category_ids: number[];
  };
  mode: 'create' | 'edit';
}

interface Events {
  'submit': (data: Props['initialData']) => void;
  'cancel': () => void;
}
```

## 2. Estados y Validaciones

### 2.1 Estados de la Solicitud
```typescript
const STATUSES = {
  published: {
    label: 'Published',
    color: 'blue',
    icon: 'fa-check-circle',
    actions: ['edit', 'delete', 'view_offers']
  },
  in_progress: {
    label: 'In Progress',
    color: 'yellow',
    icon: 'fa-clock',
    actions: ['edit', 'complete', 'cancel']
  },
  completed: {
    label: 'Completed',
    color: 'green',
    icon: 'fa-check',
    actions: ['view', 'review']
  },
  canceled: {
    label: 'Canceled',
    color: 'red',
    icon: 'fa-times',
    actions: ['view']
  }
};
```

### 2.2 Validaciones de Formulario
```typescript
const validationRules = {
  title: {
    required: true,
    maxLength: 255,
    pattern: /^[a-zA-Z0-9\s.,!?-]+$/
  },
  description: {
    required: true,
    minLength: 20
  },
  address: {
    required: true
  },
  zip_code: {
    required: true,
    pattern: /^\d{5}(-\d{4})?$/
  },
  latitude: {
    required: true,
    min: -90,
    max: 90
  },
  longitude: {
    required: true,
    min: -180,
    max: 180
  },
  budget: {
    required: true,
    min: 0,
    max: 999999.99
  },
  due_date: {
    required: false,
    future: true
  },
  category_ids: {
    required: true,
    min: 1
  }
};
```

## 3. Integración con la API

### 3.1 Endpoints
```typescript
const API_ENDPOINTS = {
  list: '/api/v1/service-requests',
  create: '/api/v1/service-requests',
  show: (slug: string) => `/api/v1/service-requests/${slug}`,
  update: (slug: string) => `/api/v1/service-requests/${slug}`,
  delete: (slug: string) => `/api/v1/service-requests/${slug}`,
  categories: '/api/v1/categories'
};
```

### 3.2 Tipos de Respuesta
```typescript
interface ServiceRequestResponse {
  data: {
    id: number;
    title: string;
    slug: string;
    description: string;
    address: string;
    zip_code: string;
    latitude: number;
    longitude: number;
    budget: number;
    visibility: 'public' | 'private';
    status: keyof typeof STATUSES;
    payment_method?: 'paypal' | 'credit_card' | 'bank_transfer';
    service_type: 'one_time' | 'recurring';
    priority: 'low' | 'medium' | 'high' | 'urgent';
    due_date?: string;
    created_at: string;
    updated_at: string;
    user: {
      id: number;
      name: string;
      email: string;
    };
    categories: Array<{
      id: number;
      name: string;
      slug: string;
      icon: string;
    }>;
  };
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}
```

## 4. Consideraciones de UI/UX

### 4.1 Diseño Responsivo
- Móvil: Lista vertical con tarjetas
- Tablet: Grid 2 columnas
- Desktop: Grid 3 columnas con sidebar de filtros

### 4.2 Estados de Carga
```typescript
const loadingStates = {
  list: 'Cargando solicitudes...',
  form: 'Guardando solicitud...',
  detail: 'Cargando detalles...',
  delete: 'Eliminando solicitud...'
};
```

### 4.3 Mensajes de Error
```typescript
const errorMessages = {
  create: 'Error al crear la solicitud',
  update: 'Error al actualizar la solicitud',
  delete: 'Error al eliminar la solicitud',
  load: 'Error al cargar las solicitudes',
  validation: {
    title: 'El título es requerido y debe tener máximo 255 caracteres',
    description: 'La descripción debe tener al menos 20 caracteres',
    address: 'La dirección es requerida',
    zip_code: 'El código postal es inválido',
    budget: 'El presupuesto debe estar entre 0 y 999,999.99',
    categories: 'Debe seleccionar al menos una categoría'
  }
};
```

## 5. Funcionalidades Especiales

### 5.1 Mapa de Ubicación
- Integración con Google Maps o similar
- Marcador arrastrable para selección de ubicación
- Geocodificación inversa para obtener dirección
- Validación de coordenadas válidas

### 5.2 Selector de Categorías
- Búsqueda en tiempo real
- Selección múltiple
- Visualización de iconos
- Agrupación por tipo de servicio

### 5.3 Filtros Avanzados
- Búsqueda por texto
- Filtros por estado y prioridad
- Rango de presupuesto
- Rango de fechas
- Filtros por categoría
- Filtros por ubicación

## 6. Optimizaciones

### 6.1 Caché
- Implementar caché para listados
- Caché para categorías
- Invalidación de caché en actualizaciones

### 6.2 Lazy Loading
- Carga perezosa de imágenes
- Paginación infinita
- Carga diferida de componentes pesados

### 6.3 Performance
- Debounce en búsquedas
- Throttle en scroll
- Optimización de imágenes
- Minimización de re-renders

## 7. Testing

### 7.1 Pruebas Unitarias
```typescript
describe('ServiceRequestForm', () => {
  it('valida campos requeridos', () => {});
  it('genera slug automáticamente', () => {});
  it('maneja selección de categorías', () => {});
  it('valida ubicación', () => {});
});
```

### 7.2 Pruebas de Integración
```typescript
describe('ServiceRequestList', () => {
  it('carga lista inicial', () => {});
  it('aplica filtros', () => {});
  it('maneja paginación', () => {});
  it('ordena resultados', () => {});
});
```

## 8. Consideraciones de Accesibilidad

### 8.1 ARIA Labels
```html
<div 
  role="list" 
  aria-label="Lista de solicitudes de servicio"
>
  <div 
    role="listitem" 
    aria-label="Solicitud: {title}"
  >
    <!-- contenido -->
  </div>
</div>
```

### 8.2 Navegación por Teclado
- Soporte para tabulación
- Atajos de teclado
- Focus management
- Skip links

## 9. Internacionalización

### 9.1 Traducciones
```typescript
const translations = {
  en: {
    title: 'Service Requests',
    create: 'Create Request',
    edit: 'Edit Request',
    delete: 'Delete Request',
    // ... más traducciones
  },
  es: {
    title: 'Solicitudes de Servicio',
    create: 'Crear Solicitud',
    edit: 'Editar Solicitud',
    delete: 'Eliminar Solicitud',
    // ... más traducciones
  }
};
```

### 9.2 Formato de Fechas y Números
```typescript
const dateFormats = {
  short: 'MM/DD/YYYY',
  long: 'MMMM D, YYYY',
  time: 'h:mm A'
};

const numberFormats = {
  currency: {
    style: 'currency',
    currency: 'USD'
  },
  decimal: {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }
};
```

## 10. Seguridad

### 10.1 Sanitización
- Limpieza de inputs
- Escape de HTML
- Validación de URLs
- Protección contra XSS

### 10.2 Autenticación
- Manejo de tokens
- Refresh tokens
- Redirección a login
- Protección de rutas

## 11. Monitoreo y Analytics

### 11.1 Eventos a Rastrear
```typescript
const analyticsEvents = {
  view_list: 'service_requests_list_view',
  view_detail: 'service_request_detail_view',
  create: 'service_request_create',
  update: 'service_request_update',
  delete: 'service_request_delete',
  filter: 'service_requests_filter',
  search: 'service_requests_search'
};
```

### 11.2 Métricas de Performance
- Tiempo de carga inicial
- Tiempo de respuesta de API
- Tasa de error
- Tasa de conversión 