<?php

namespace App\Http\Controllers\Api\V1\User\ServiceRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ServiceRequest\StoreUserServiceRequest;
use App\Http\Requests\User\ServiceRequest\UpdateUserServiceRequest;
use App\Http\Requests\User\ServiceRequest\UpdateUserServiceStatusRequest;
use App\Http\Resources\User\UserServiceRequestResource;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @property int $user_id
 * @property string $status
 * @property ?array $metadata
 * @property Collection $categories
 * @property Collection $offers
 * @method void attachCategories(array $categoryIds)
 * @method void syncCategories(array $categoryIds)
 * @method void notifyMatchingUsers()
 * @method bool canTransitionTo(string $newStatus)
 */
class UserServiceRequestController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lista las solicitudes de servicio públicas que no pertenecen al usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /**
         * @var \Illuminate\Database\Eloquent\Builder $query
         */
        try {
            $query = ServiceRequest::query();

            // Relaciones base que siempre se cargarán
            $query->with(['categories', 'user', 'offers']);

            // Filtrar solicitudes que NO pertenecen al usuario autenticado
            $query->where('user_id', '!=', auth()->id())
                ->where('visibility', 'public')
                ->where('status', '!=', 'canceled'); // Excluir canceladas

            // Filtros básicos
            if ($request->filled('status')) {
                $query->whereIn('status', explode(',', $request->input('status')));
            }

            if ($request->filled('priority')) {
                $query->whereIn('priority', explode(',', $request->input('priority')));
            }

            if ($request->filled('service_type')) {
                $query->whereIn('service_type', explode(',', $request->input('service_type')));
            }

            // Filtro por categorías
            if ($request->filled('category_ids')) {
                $categoryIds = explode(',', $request->input('category_ids'));
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }

            // Filtro por rango de presupuesto
            if ($request->filled('min_budget')) {
                $query->where('budget', '>=', $request->input('min_budget'));
            }
            if ($request->filled('max_budget')) {
                $query->where('budget', '<=', $request->input('max_budget'));
            }

            // Filtro por fecha de vencimiento
            if ($request->filled('due_date_start')) {
                $query->where('due_date', '>=', $request->input('due_date_start') . ' 00:00:00');
            }
            if ($request->filled('due_date_end')) {
                $query->where('due_date', '<=', $request->input('due_date_end') . ' 23:59:59');
            }

            // Filtro por ubicación (radio de búsqueda)
            if ($request->filled(['latitude', 'longitude', 'radius'])) {
                $lat = $request->input('latitude');
                $lng = $request->input('longitude');
                $radius = $request->input('radius'); // en kilómetros

                // Aquí deberías agregar la lógica para calcular la distancia y filtrar por radio
                // Ejemplo: $query->selectRaw('...');
            }

            // Búsqueda por texto en título y descripción
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filtro por código postal
            if ($request->filled('zip_code')) {
                $query->where('zip_code', $request->input('zip_code'));
            }

            // Filtro por método de pago
            if ($request->filled('payment_method')) {
                $query->whereIn('payment_method', explode(',', $request->input('payment_method')));
            }

            // Filtro de solicitudes vencidas/no vencidas
            if ($request->boolean('overdue')) {
                $query->where('due_date', '<', now());
            }

            // Ordenación
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = [
                'created_at',
                'due_date',
                'budget',
                'priority',
                'status'
            ];

            if (in_array($sortField, $allowedSortFields, true)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Si se está filtrando por distancia, mantener ese orden como prioridad
            if ($request->filled(['latitude', 'longitude', 'radius'])) {
                $query->orderBy('distance');
            }

            // Paginación
            $perPage = (int) $request->input('per_page', 10);
            /** @var \Illuminate\Pagination\LengthAwarePaginator $serviceRequests */
            $serviceRequests = $query->paginate($perPage);

            // Metadatos para la respuesta
            $metadata = [
                'filters' => [
                    'available_visibility' => ServiceRequest::VISIBILITY,
                ],
                'pagination' => [
                    'has_more_pages' => $serviceRequests->hasMorePages(),
                ],
                'applied_filters' => array_filter([
                    'sort_direction' => $request->input('sort_direction', 'desc'),
                ]),
            ];

            $data = [
                'items' => UserServiceRequestResource::collection($serviceRequests),
                'meta' => $metadata,
            ];

            return $this->successResponse(
                data: $data,
                message: 'Service requests retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving service requests',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista las solicitudes de servicio propias del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myServiceRequests(Request $request): JsonResponse
    {
        try {
            $query = ServiceRequest::query();

            // Relaciones base que siempre se cargarán
            $query->with(['categories', 'user', 'offers']);

            // Filtrar solicitudes que pertenecen al usuario autenticado
            $query->where('user_id', auth()->id());

            // Filtros básicos
            if ($request->filled('status')) {
                $query->whereIn('status', explode(',', $request->input('status')));
            }

            if ($request->filled('priority')) {
                $query->whereIn('priority', explode(',', $request->input('priority')));
            }

            if ($request->filled('service_type')) {
                $query->whereIn('service_type', explode(',', $request->input('service_type')));
            }

            // Filtro por categorías
            if ($request->filled('category_ids')) {
                $categoryIds = explode(',', $request->input('category_ids'));
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }

            // Filtro por rango de presupuesto
            if ($request->filled('min_budget')) {
                $query->where('budget', '>=', $request->input('min_budget'));
            }
            if ($request->filled('max_budget')) {
                $query->where('budget', '<=', $request->input('max_budget'));
            }

            // Filtro por fecha de vencimiento
            if ($request->filled('due_date_start')) {
                $query->where('due_date', '>=', $request->input('due_date_start'));
            }
            if ($request->filled('due_date_end')) {
                $query->where('due_date', '<=', $request->input('due_date_end'));
            }

            // Búsqueda por texto en título y descripción
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filtro por código postal
            if ($request->filled('zip_code')) {
                $query->where('zip_code', $request->input('zip_code'));
            }

            // Filtro por método de pago
            if ($request->filled('payment_method')) {
                $query->whereIn('payment_method', explode(',', $request->input('payment_method')));
            }

            // Filtro de solicitudes vencidas/no vencidas
            if ($request->boolean('overdue')) {
                $query->where('due_date', '<', now());
            }

            // Ordenación
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = [
                'created_at',
            ];

            if (in_array($sortField, $allowedSortFields, true)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginación
            $perPage = (int) $request->input('per_page', 10);
            /** @var \Illuminate\Pagination\LengthAwarePaginator $serviceRequests */
            $serviceRequests = $query->paginate($perPage);

            // Metadatos para la respuesta
            $metadata = [
                'pagination' => [
                    'has_more_pages' => $serviceRequests->hasMorePages(),
                ],
            ];

            $data = [
                'items' => UserServiceRequestResource::collection($serviceRequests),
                'meta' => $metadata,
            ];

            return $this->successResponse(
                data: $data,
                message: 'My service requests retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving my service requests',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Store a new service request.
     *
     * @param StoreUserServiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserServiceRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            DB::beginTransaction();
            try {
                $validated = $request->validated();
                $validated['user_id'] = $user->id;

                $serviceRequest = ServiceRequest::create($validated);

                if (isset($validated['category_ids'])) {
                    $serviceRequest->attachCategories($validated['category_ids']);
                }

                // Notificar a usuarios coincidentes
                $serviceRequest->notifyMatchingUsers();

                DB::commit();

                return $this->successResponse(
                    data: $serviceRequest->load(['categories', 'user']),
                    message: 'Service request created successfully',
                    statusCode: 201
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error creating service request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                message: 'Error creating service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Show service request details.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        try {
            /** @var ServiceRequest|null $serviceRequest */
            $serviceRequest = ServiceRequest::with(['categories', 'user', 'offers'])->find($id);

            if (!$serviceRequest) {
                return $this->errorResponse(
                    message: 'Service request not found',
                    statusCode: 404
                );
            }

            // Verificar si el usuario puede ver esta solicitud
            $isOwner = $serviceRequest->user_id === auth()->id();
            if (!$isOwner && $serviceRequest->visibility === 'private') {
                return $this->errorResponse(
                    message: 'Unauthorized to view this service request',
                    statusCode: 403
                );
            }

            // Si el usuario es el propietario, cargar offers.user
            if ($isOwner) {
                $serviceRequest->load('offers.user');
            }

            return $this->successResponse(
                data: new UserServiceRequestResource($serviceRequest),
                message: 'Service request details retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving service request details',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update an existing service request that belongs to the authenticated user.
     *
     * @param UpdateUserServiceRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function update(UpdateUserServiceRequest $request, int|string $id): JsonResponse
    {
        try {
            /** @var ServiceRequest|null $serviceRequest */
            $serviceRequest = ServiceRequest::where('user_id', auth()->id())->find($id);

            if (!$serviceRequest) {
                return $this->errorResponse(
                    message: 'Service request not found',
                    statusCode: 404
                );
            }

            if ($serviceRequest->status !== 'published') {
                return $this->errorResponse(
                    message: 'Cannot update service request in its current status',
                    statusCode: 400
                );
            }

            DB::beginTransaction();
            try {
                // Si tiene ofertas asociadas, eliminarlas y notificar a los ofertantes
                if ($serviceRequest->offers()->exists()) {
                    $offers = $serviceRequest->offers;
                    foreach ($offers as $offer) {
                        // Notificar al ofertante
                        event(new \App\Events\ServiceOfferStatusUpdatedNotification($offer, $offer->user_id));
                    }
                    $serviceRequest->offers()->delete();
                }

                // Actualizar la solicitud
                $serviceRequest->update($request->validated());

                DB::commit();

                return $this->successResponse(
                    data: new UserServiceRequestResource($serviceRequest),
                    message: 'Service request updated successfully'
                );
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error updating service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update the status of an existing service request.
     *
     * Se usa un Custom Request (UpdateClientServiceStatusRequest) que se encarga de
     * validar el estado y los campos adicionales requeridos según el nuevo estado.
     *
     * @param UpdateUserServiceStatusRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateUserServiceStatusRequest $request, int|string $id): JsonResponse
    {
        try {
            /** @var ServiceRequest|null $serviceRequest */
            $serviceRequest = ServiceRequest::where('user_id', auth()->id())->find($id);

            if (!$serviceRequest) {
                return $this->errorResponse(
                    message: 'Service request not found',
                    statusCode: 404
                );
            }

            $newStatus = $request->input('status');

            // Solo permitir cancelar si está publicada o en progreso
            if ($newStatus === 'canceled' && $serviceRequest->status !== 'published') {
                return $this->errorResponse(
                    message: 'Only published service requests can be canceled',
                    statusCode: 400
                );
            }

            if (!$serviceRequest->canTransitionTo($newStatus)) {
                return $this->errorResponse(
                    message: 'Invalid status transition',
                    statusCode: 400
                );
            }

            DB::beginTransaction();
            try {
                // Lógica de actualización de estado aquí...
                // $serviceRequest->status = $newStatus;
                // $serviceRequest->save();

                DB::commit();

                return $this->successResponse(
                    data: new UserServiceRequestResource($serviceRequest),
                    message: 'Service request status updated successfully'
                );
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error updating service request status',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete (or cancel) a service request.
     *
     * Regla de negocio: Sólo se pueden eliminar aquellas solicitudes que aún están en estado "published".
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function destroy(int|string $id): JsonResponse
    {
        try {
            /** @var ServiceRequest|null $serviceRequest */
            $serviceRequest = ServiceRequest::with(['categories', 'offers'])->find($id);

            if (!$serviceRequest) {
                return $this->errorResponse(
                    message: 'Service request not found',
                    statusCode: 404
                );
            }

            if ($serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'Unauthorized to delete this service request',
                    statusCode: 403
                );
            }

            if (!$serviceRequest->isPublished() && !$serviceRequest->isCanceled()) {
                return $this->errorResponse(
                    message: 'Only published or canceled service requests can be deleted',
                    statusCode: 400
                );
            }

            DB::beginTransaction();
            try {
                // Si tiene ofertas, marcarlas como rejected y notificar
                if ($serviceRequest->offers()->exists()) {
                    foreach ($serviceRequest->offers as $offer) {
                        $offer->update(['status' => \App\Models\ServiceOffer::STATUS_REJECTED]);
                        event(new \App\Events\ServiceOfferStatusUpdatedNotification($offer, $offer->user_id));
                    }
                }

                $serviceRequest->delete();

                DB::commit();

                return $this->successResponse(
                    message: 'Service request deleted successfully'
                );
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error deleting service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Restaura una solicitud de servicio previamente eliminada.
     * Solo el propietario puede restaurar sus solicitudes eliminadas.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function restore(int|string $id): JsonResponse
    {
        try {
            // Buscar la solicitud eliminada que pertenezca al usuario autenticado
            /** @var ServiceRequest|null $serviceRequest */
            $serviceRequest = ServiceRequest::onlyTrashed()->where('user_id', auth()->id())->find($id);

            if (!$serviceRequest) {
                return $this->errorResponse(
                    message: 'Service request not found or not owned by user',
                    statusCode: 404
                );
            }

            // Verificar si ya existe una solicitud activa con el mismo título
            if (
                ServiceRequest::where('title', $serviceRequest->title)
                    ->where('user_id', auth()->id())
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                return $this->errorResponse(
                    message: 'An active service request with the same title already exists',
                    statusCode: 400
                );
            }

            DB::beginTransaction();
            try {
                // Lógica de restauración aquí...
                // $serviceRequest->restore();

                DB::commit();

                return $this->successResponse(
                    data: new UserServiceRequestResource($serviceRequest),
                    message: 'Service request restored successfully'
                );
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error restoring service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista las solicitudes de servicio eliminadas del usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trashedRequests(Request $request): JsonResponse
    {
        try {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = ServiceRequest::onlyTrashed()->where('user_id', auth()->id());

            // Filtros básicos
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filtro por fecha de eliminación
            if ($request->filled('deleted_from')) {
                $query->where('deleted_at', '>=', $request->input('deleted_from'));
            }
            if ($request->filled('deleted_to')) {
                $query->where('deleted_at', '<=', $request->input('deleted_to'));
            }

            // Ordenación
            $sortField = $request->input('sort_by', 'deleted_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = [
                'deleted_at',
            ];

            if (in_array($sortField, $allowedSortFields, true)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginación
            $perPage = (int) $request->input('per_page', 10);
            /** @var \Illuminate\Pagination\LengthAwarePaginator $trashedRequests */
            $trashedRequests = $query->paginate($perPage);

            // Metadatos para la respuesta
            $metadata = [
                'pagination' => [
                    'has_more_pages' => $trashedRequests->hasMorePages(),
                ],
            ];

            $data = [
                'items' => UserServiceRequestResource::collection($trashedRequests),
                'meta' => $metadata,
            ];

            return $this->successResponse(
                data: $data,
                message: 'Trashed service requests retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving trashed service requests',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene el resumen de solicitudes de servicio para gráficos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceRequestSummary(): JsonResponse
    {
        try {
            Log::info('getServiceRequestSummary called');
            $totalPublished = ServiceRequest::totalPublished();
            $totalAttended = ServiceRequest::totalAttended();
            $attendedPercentage = ServiceRequest::attendedPercentage();

            $data = [
                'total_published' => $totalPublished,
                'total_attended' => $totalAttended,
                'attended_percentage' => $attendedPercentage,
            ];

            Log::info('getServiceRequestSummary data', $data);

            return $this->successResponse(
                data: $data,
                message: 'Service request summary data retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error in getServiceRequestSummary', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get summary', 500);
        }
    }

    /**
     * Obtiene tendencias de solicitudes de servicio publicadas y atendidas por mes en el último año.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceRequestTrends(): JsonResponse
    {
        try {
            Log::info('getServiceRequestTrends called');
            $startDate = now()->subYear()->startOfMonth();
            $endDate = now()->endOfMonth();

            // Publicadas por mes
            $published = ServiceRequest::where('status', ServiceRequest::STATUS_PUBLISHED)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            // Atendidas por mes (en progreso o completadas)
            $attended = ServiceRequest::whereIn('status', [ServiceRequest::STATUS_IN_PROGRESS, ServiceRequest::STATUS_COMPLETED])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            // Generar arreglo de meses para el último año
            $months = [];
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }

            // Preparar datos para gráfico (rellenar ceros si no hay datos)
            $publishedData = [];
            $attendedData = [];
            foreach ($months as $month) {
                $publishedData[] = [
                    'month' => $month,
                    'count' => (int)($published[$month] ?? 0)
                ];
                $attendedData[] = [
                    'month' => $month,
                    'count' => (int)($attended[$month] ?? 0)
                ];
            }

            $data = [
                'published' => $publishedData,
                'attended' => $attendedData,
            ];

            Log::info('getServiceRequestTrends data', $data);

            return $this->successResponse(
                data: $data,
                message: 'Service request trends data retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Error in getServiceRequestTrends', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to get trends', 500);
        }
    }
}
