<?php

namespace App\Http\Controllers\Api\V1\User\ServiceRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ServiceRequest\StoreUserServiceRequest;
use App\Http\Requests\User\ServiceRequest\UpdateUserServiceRequest;
use App\Http\Requests\User\ServiceRequest\UpdateUserServiceStatusRequest;
use App\Http\Resources\User\UserServiceRequestResource;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserServiceRequestController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lista las solicitudes de servicio con filtros avanzados.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ServiceRequest::query();

            // Relaciones base que siempre se cargarán
            $query->with(['categories', 'user', 'offers']);

            // Filtro de visibilidad y propiedad
            if ($request->boolean('my_requests')) {
                $query->where('user_id', auth()->id());
            } else {
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                      ->orWhere('user_id', auth()->id());
                });
            }

            // Filtros básicos
            if ($request->filled('status')) {
                $query->whereIn('status', explode(',', $request->status));
            }

            if ($request->filled('priority')) {
                $query->whereIn('priority', explode(',', $request->priority));
            }

            if ($request->filled('service_type')) {
                $query->whereIn('service_type', explode(',', $request->service_type));
            }

            // Filtro por categorías
            if ($request->filled('category_ids')) {
                $categoryIds = explode(',', $request->category_ids);
                $query->whereHas('categories', function($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }

            // Filtro por rango de presupuesto
            if ($request->filled('min_budget')) {
                $query->where('budget', '>=', $request->min_budget);
            }
            if ($request->filled('max_budget')) {
                $query->where('budget', '<=', $request->max_budget);
            }

            // Filtro por fecha de vencimiento
            if ($request->filled('due_date_start')) {
                $query->where('due_date', '>=', $request->due_date_start);
            }
            if ($request->filled('due_date_end')) {
                $query->where('due_date', '<=', $request->due_date_end);
            }

            // Filtro por ubicación (radio de búsqueda)
            if ($request->filled(['latitude', 'longitude', 'radius'])) {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $radius = $request->radius; // en kilómetros

                $query->selectRaw("
                    *,
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude)))) AS distance", 
                    [$lat, $lng, $lat]
                )
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
            }

            // Búsqueda por texto en título y descripción
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filtro por código postal
            if ($request->filled('zip_code')) {
                $query->where('zip_code', $request->zip_code);
            }

            // Filtro por método de pago
            if ($request->filled('payment_method')) {
                $query->whereIn('payment_method', explode(',', $request->payment_method));
            }

            // Filtro de solicitudes vencidas/no vencidas
            if ($request->boolean('overdue')) {
                $query->where('due_date', '<', now())
                      ->whereNotIn('status', ['completed', 'canceled']);
            }

            // Ordenación
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = [
                'created_at', 'due_date', 'budget', 'priority', 'status'
            ];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Si se está filtrando por distancia, mantener ese orden como prioridad
            if ($request->filled(['latitude', 'longitude', 'radius'])) {
                $query->orderBy('distance');
            }

            // Paginación
            $perPage = $request->input('per_page', 10);
            $serviceRequests = $query->paginate($perPage);

            // Metadatos para la respuesta
            $metadata = [
                'filters' => [
                    'available_statuses' => ServiceRequest::STATUSES,
                    'available_priorities' => ServiceRequest::PRIORITIES,
                    'available_payment_methods' => ServiceRequest::PAYMENT_METHODS,
                    'available_service_types' => ServiceRequest::SERVICE_TYPES,
                    'available_visibility' => ServiceRequest::VISIBILITY,
                ],
                'pagination' => [
                    'current_page' => $serviceRequests->currentPage(),
                    'last_page' => $serviceRequests->lastPage(),
                    'per_page' => $serviceRequests->perPage(),
                    'total' => $serviceRequests->total(),
                    'has_more_pages' => $serviceRequests->hasMorePages(),
                ],
                'applied_filters' => array_filter([
                    'status' => $request->status,
                    'priority' => $request->priority,
                    'service_type' => $request->service_type,
                    'category_ids' => $request->category_ids,
                    'min_budget' => $request->min_budget,
                    'max_budget' => $request->max_budget,
                    'due_date_start' => $request->due_date_start,
                    'due_date_end' => $request->due_date_end,
                    'search' => $request->search,
                    'zip_code' => $request->zip_code,
                    'payment_method' => $request->payment_method,
                    'sort_by' => $request->input('sort_by', 'created_at'),
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

        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving service requests',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Store a new service request.
     */
    public function store(StoreUserServiceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = auth()->id();

            $serviceRequest = ServiceRequest::create($validated);

            if (isset($validated['category_ids'])) {
                $serviceRequest->attachCategories($validated['category_ids']);
            }

            // Cargar relaciones necesarias
            $serviceRequest->load(['categories', 'user']);

            return $this->successResponse(
                data: new UserServiceRequestResource($serviceRequest),
                message: 'Service request created successfully',
                statusCode: 201
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error creating service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Show service request details.
     */
    public function show($id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with(['categories', 'user', 'offers', 'contract'])
                ->find($id);

            if (!$serviceRequest) {
                return $this->notFoundResponse('Service request not found');
            }

            // Verificar si el usuario puede ver esta solicitud
            $isOwner = $serviceRequest->user_id === auth()->id();
            if (!$isOwner && $serviceRequest->visibility === 'private') {
                return $this->errorResponse(
                    message: 'You do not have permission to view this service request',
                    statusCode: 403
                );
            }

            return $this->successResponse(
                data: new UserServiceRequestResource($serviceRequest),
                message: 'Service request details retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving service request details',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update an existing service request.
     */
    public function update(UpdateUserServiceRequest $request, $id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with(['categories', 'user'])
                ->find($id);

            if (!$serviceRequest) {
                return $this->notFoundResponse('Service request not found');
            }

            if ($serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'You do not have permission to update this service request',
                    statusCode: 403
                );
            }

            if (!in_array($serviceRequest->status, ['published', 'in_progress'])) {
                return $this->errorResponse(
                    message: 'You cannot update a request that is already completed or canceled',
                    statusCode: 403
                );
            }

            DB::beginTransaction();
            try {
                $validated = $request->validated();

                if (isset($validated['category_ids'])) {
                    $categoryIds = $validated['category_ids'];
                    unset($validated['category_ids']);
                    $serviceRequest->syncCategories($categoryIds);
                }

                $serviceRequest->update($validated);
                $serviceRequest->load(['categories', 'user', 'offers', 'contract']);

                DB::commit();

                return $this->successResponse(
                    data: new UserServiceRequestResource($serviceRequest),
                    message: 'Service request updated successfully'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
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
     */
    public function updateStatus(UpdateUserServiceStatusRequest $request, $id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with(['categories', 'user'])
                ->find($id);

            if (!$serviceRequest) {
                return $this->notFoundResponse('Service request not found');
            }

            if ($serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'You do not have permission to update the status of this service request',
                    statusCode: 403
                );
            }

            $newStatus = $request->input('status');

            if (!$serviceRequest->canTransitionTo($newStatus)) {
                return $this->errorResponse(
                    message: "You cannot change the status of '{$serviceRequest->status}' to '{$newStatus}'",
                    statusCode: 400
                );
            }

            DB::beginTransaction();
            try {
                $serviceRequest->status = $newStatus;

                // Actualizar metadatos según el estado
                if ($newStatus === 'canceled') {
                    $serviceRequest->metadata = array_merge(
                        $serviceRequest->metadata ?? [],
                        ['cancellation_reason' => $request->input('cancellation_reason')]
                    );
                } elseif ($newStatus === 'completed') {
                    $serviceRequest->metadata = array_merge(
                        $serviceRequest->metadata ?? [],
                        [
                            'completion_notes' => $request->input('completion_notes'),
                            'completion_evidence' => $request->input('completion_evidence'),
                            'completed_at' => now()
                        ]
                    );
                }

                $serviceRequest->save();
                $serviceRequest->load(['categories', 'user', 'offers', 'contract']);

                DB::commit();

                return $this->successResponse(
                    data: new UserServiceRequestResource($serviceRequest),
                    message: 'Service request status updated successfully'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
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
     */
    public function destroy($id): JsonResponse
    {
        try {
            $serviceRequest = ServiceRequest::with(['categories', 'offers'])
                ->find($id);

            if (!$serviceRequest) {
                return $this->notFoundResponse('Service request not found');
            }

            if ($serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'You do not have permission to delete this service request',
                    statusCode: 403
                );
            }

            if (!$serviceRequest->isPublished()) {
                return $this->errorResponse(
                    message: 'Only published requests can be deleted',
                    statusCode: 403
                );
            }

            if ($serviceRequest->offers->isNotEmpty()) {
                return $this->errorResponse(
                    message: 'You cannot delete a request that has offers',
                    statusCode: 403
                );
            }

            DB::beginTransaction();
            try {
                $serviceRequest->categories()->detach();
                $serviceRequest->delete();
                DB::commit();

                return $this->successResponse(
                    message: 'Service request deleted successfully'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error deleting service request',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }
}