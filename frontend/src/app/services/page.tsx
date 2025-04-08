'use client';

import axios from 'axios';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';

// Configuración de axios
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Tipo para la respuesta de la API
interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: {
    filters: {
      available_statuses: string[];
      available_priorities: string[];
      available_payment_methods: string[];
      available_service_types: string[];
      available_visibility: string[];
    };
    pagination: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
      has_more_pages: boolean;
    };
  };
}

interface ServiceRequest {
  id: number;
  title: string;
  slug: string;
  description: string;
  address: string;
  zip_code: string;
  location: {
    latitude: number;
    longitude: number;
    distance?: number;
  };
  budget: {
    amount: number;
    formatted: string;
  };
  visibility: {
    code: string;
    text: string;
  };
  status: {
    code: string;
    text: string;
  };
  priority: {
    code: string;
    text: string;
  };
  payment_method: {
    code: string;
    text: string;
  };
  service_type: {
    code: string;
    text: string;
  };
  dates: {
    due_date: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
  };
  flags: {
    is_overdue: boolean;
    is_published: boolean;
    is_in_progress: boolean;
    is_completed: boolean;
    is_canceled: boolean;
    is_urgent: boolean;
    is_owner: boolean;
  };
  metadata: {
    completion_notes: string | null;
    completion_evidence: string[];
    cancellation_reason: string | null;
    completed_at: string | null;
    additional_data: Record<string, any>;
  };
  relationships: {
    categories: Array<{
      id: number;
      name: string;
    }>;
    user: {
      id: number;
      name: string;
      email: string;
    };
    offers_count: number;
    has_contract: boolean;
  };
  permissions: {
    can_edit: boolean;
    can_delete: boolean;
    can_make_offer: boolean;
    can_cancel: boolean;
  };
}

export default function ServicesPage() {
  const [services, setServices] = useState<ServiceRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  useEffect(() => {
    const fetchServices = async () => {
      try {
        // Obtener el token del localStorage
        const token = localStorage.getItem('token');
        if (!token) {
          throw new Error('No hay token de autenticación');
        }

        // Configurar el header de autorización
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        const response = await api.get<ApiResponse<ServiceRequest[]>>('/api/v1/user/service-requests');
        
        if (response.data.success) {
          setServices(response.data.data);
        } else {
          throw new Error(response.data.message);
        }
      } catch (err) {
        if (axios.isAxiosError(err)) {
          if (err.response?.status === 401) {
            // Redirigir al login si no está autenticado
            router.push('/login');
            return;
          }
          setError(err.response?.data?.message || 'Error al cargar los servicios');
        } else {
          setError('Error desconocido al cargar los servicios');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchServices();
  }, [router]);

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Solicitudes de Servicio</h1>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {services.map((service) => (
          <div
            key={service.id}
            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
            onClick={() => router.push(`/services/${service.slug}`)}
          >
            <div className="flex justify-between items-start mb-4">
              <h2 className="text-xl font-semibold text-indigo-600">
                {service.title}
              </h2>
              <div className="flex space-x-2">
                <span className={`px-2 py-1 rounded text-sm ${
                  service.status.code === 'published' ? 'bg-blue-100 text-blue-800' :
                  service.status.code === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                  service.status.code === 'completed' ? 'bg-green-100 text-green-800' :
                  'bg-gray-100 text-gray-800'
                }`}>
                  {service.status.text}
                </span>
                <span className={`px-2 py-1 rounded text-sm ${
                  service.priority.code === 'low' ? 'bg-green-100 text-green-800' :
                  service.priority.code === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                  service.priority.code === 'high' ? 'bg-orange-100 text-orange-800' :
                  'bg-red-100 text-red-800'
                }`}>
                  {service.priority.text}
                </span>
              </div>
            </div>

            <p className="text-gray-600 mb-4 line-clamp-3">
              {service.description}
            </p>

            <div className="grid grid-cols-2 gap-4 mb-4">
              <div>
                <span className="text-sm font-medium text-gray-500">Presupuesto</span>
                <p className="text-lg font-semibold">
                  {service.budget.formatted}
                </p>
              </div>
              <div>
                <span className="text-sm font-medium text-gray-500">Fecha Límite</span>
                <p className="text-lg font-semibold">
                  {service.dates.due_date
                    ? new Date(service.dates.due_date).toLocaleDateString()
                    : 'No especificada'}
                </p>
              </div>
            </div>

            <div className="flex flex-wrap gap-2 mb-4">
              {service.relationships.categories.map((category) => (
                <span
                  key={category.id}
                  className="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm"
                >
                  {category.name}
                </span>
              ))}
            </div>

            <div className="flex justify-between items-center text-sm text-gray-500">
              <span>Publicado por {service.relationships.user.name}</span>
              <span>
                {new Date(service.dates.created_at).toLocaleDateString()}
              </span>
            </div>

            {service.flags.is_overdue && (
              <div className="mt-4 text-red-600 text-sm">
                ⚠️ Esta solicitud está vencida
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
} 