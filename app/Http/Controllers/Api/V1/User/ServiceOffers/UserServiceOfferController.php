<?php

namespace App\Http\Controllers\Api\V1\User\ServiceOffers;

use App\Constants\NotificationType;
use App\Events\NewServiceOfferNotification;
use App\Events\ServiceOfferStatusUpdatedNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserOfferResource;
use App\Http\Resources\User\UserContractResource;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @method \Illuminate\Database\Eloquent\Relations\HasOne contract()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany offers()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo serviceRequest()
 */
class UserServiceOfferController extends Controller
{
    use ApiResponseTrait;

    /**
     * Crea una nueva oferta para una solicitud de servicio.
     *
     * @param Request $request
     * @param int|string $serviceRequest
     * @return JsonResponse
     */
    public function store(Request $request, int|string $serviceRequest): JsonResponse
    {
        try {
            /** @var ServiceRequest $serviceRequest */
            $serviceRequest = ServiceRequest::with(['categories', 'offers'])->findOrFail($serviceRequest);

            Log::info('Starting offer creation process', [
                'service_request_id' => $serviceRequest->id,
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $user = auth()->user();

            // 1. Verificar si el usuario ya tiene una oferta para esta solicitud
            $existingOffer = $serviceRequest
                ->offers()
                ->where('user_id', $user->id)
                ->exists();

            if ($existingOffer) {
                Log::warning('User trying to create multiple offers', [
                    'user_id' => $user->id,
                    'service_request_id' => $serviceRequest->id
                ]);
                return $this->errorResponse(
                    message: 'You have already made an offer for this service request. You cannot create multiple offers.',
                    statusCode: 403
                );
            }

            // 2. Verificar que el usuario no esté haciendo una oferta a su propia solicitud
            if ($serviceRequest->user_id === $user->id) {
                Log::warning('User trying to offer on their own request', [
                    'user_id' => $user->id,
                    'service_request_id' => $serviceRequest->id
                ]);
                return $this->errorResponse(
                    message: 'You cannot make an offer on your own service request',
                    statusCode: 403
                );
            }

            // 3. Obtener las categorías de la solicitud
            $requestCategoryIds = $serviceRequest->categories->pluck('id')->toArray();

            // 4. Obtener las categorías de las habilidades del usuario
            $userSkillCategories = $user
                ->skills()
                ->with('categories')
                ->get()
                ->pluck('categories')
                ->flatten();

            $userSkillCategoryIds = $userSkillCategories->pluck('id')->unique()->toArray();
            $matchingCategories = array_intersect($requestCategoryIds, $userSkillCategoryIds);

            Log::info('Shared categories verification', [
                'user_id' => $user->id,
                'user_skills' => $user->skills->pluck('name'),
                'request_categories' => $serviceRequest->categories->pluck('name'),
                'request_category_ids' => $requestCategoryIds,
                'user_skill_category_ids' => $userSkillCategoryIds,
                'matching_categories' => $matchingCategories
            ]);

            if (empty($matchingCategories)) {
                $requestCategories = $serviceRequest->categories->pluck('name')->join(', ');
                $userCategories = $userSkillCategories->pluck('name')->unique()->join(', ');

                return $this->errorResponse(
                    message: "You don't have the necessary skills to make an offer for this service request",
                    statusCode: 403,
                    errors: [
                        'categories' => [
                            'request_categories' => "This request requires skills in: {$requestCategories}",
                            'your_categories' => "Your skills are in: {$userCategories}"
                        ]
                    ]
                );
            }

            // 5. Validar el estado de la solicitud
            if ($serviceRequest->status !== 'published') {
                return $this->errorResponse(
                    message: 'This service request is not available for offers',
                    statusCode: 403
                );
            }

            // 6. Validar los datos de la oferta
            $validatedData = $request->validate([
                'price_proposed' => 'required|numeric|min:0',
                'estimated_time' => 'required|integer|min:1',
                'message' => 'required|string|min:10|max:500'
            ]);

            DB::beginTransaction();
            try {
                /** @var ServiceOffer $offer */
                $offer = ServiceOffer::create([
                    'service_request_id' => $serviceRequest->id,
                    'user_id' => $user->id,
                    'price_proposed' => $validatedData['price_proposed'],
                    'estimated_time' => $validatedData['estimated_time'],
                    'message' => $validatedData['message'],
                    'status' => ServiceOffer::STATUS_PENDING
                ]);

                Log::info('Offer created successfully', [
                    'offer_id' => $offer->id,
                    'service_request_id' => $serviceRequest->id,
                    'matching_categories' => array_values($matchingCategories)
                ]);

                $offer->notifyRequestOwner();

                DB::commit();

                return $this->successResponse(
                    data: $offer->load(['user', 'serviceRequest']),
                    message: 'Offer created successfully. The request creator will be notified.',
                    statusCode: 201
                );
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in offer creation transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (ModelNotFoundException $e) {
            Log::warning('Service request not found', ['id' => $serviceRequest]);
            return $this->errorResponse(
                message: 'Service request not found',
                statusCode: 404
            );
        } catch (\Exception $e) {
            Log::error('General error in offer creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                message: 'Error creating service offer',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Actualiza el estado de una oferta.
     *
     * @param Request $request
     * @param ServiceOffer $offer
     * @return JsonResponse
     */
    public function update(Request $request, ServiceOffer $offer): JsonResponse
    {
        if (!$offer->serviceRequest || $offer->serviceRequest->user_id !== auth()->id()) {
            return $this->errorResponse(
                message: __('messages.service_offers.errors.unauthorized'),
                statusCode: 403
            );
        }

        try {
            DB::beginTransaction();

            $oldStatus = $offer->status;
            $newStatus = $request->input('status');

            if (!in_array($newStatus, ServiceOffer::STATUSES, true)) {
                return $this->errorResponse(
                    message: __('messages.service_offers.errors.invalid_status'),
                    statusCode: 400
                );
            }

            if ($newStatus === ServiceOffer::STATUS_ACCEPTED && $offer->status === ServiceOffer::STATUS_PENDING) {
                // Crear el contrato si no existe
                if (!$offer->contract()->exists()) {
                    /** @var Contract $contract */
                    $contract = Contract::create([
                        'service_offer_id' => $offer->id,
                        'service_request_id' => $offer->service_request_id,
                        'provider_id' => $offer->user_id,
                        'client_id' => $offer->serviceRequest?->user_id,
                        'price' => $offer->price_proposed,
                        'estimated_time' => $offer->estimated_time,
                        'status' => Contract::STATUS_IN_PROGRESS,
                    ]);
                } else {
                    $contract = $offer->contract;
                }

                $offer->update(['status' => $newStatus]);
                $offer->serviceRequest?->markInProgress();
                $contract?->update(['status' => Contract::STATUS_IN_PROGRESS]);

                $offer->serviceRequest?->offers()
                    ->where('id', '!=', $offer->id)
                    ->update(['status' => ServiceOffer::STATUS_REJECTED]);

                foreach ($offer->serviceRequest?->offers()->where('id', '!=', $offer->id)->get() ?? [] as $declinedOffer) {
                    if ($declinedOffer->user) {
                        $declinedOffer->notifyStatusUpdate();
                    }
                }

                $offer->notifyOfferAccepted();
            } else {
                $offer->update(['status' => $newStatus]);
                $offer->notifyStatusUpdate();
            }

            DB::commit();

            $offer->load(['user', 'serviceRequest', 'contract']);

            return $this->successResponse(
                data: $offer,
                message: __('messages.service_offers.success.updated')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offer->id
            ]);

            return $this->errorResponse(
                message: __('messages.service_offers.errors.update_failed'),
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista todas las ofertas recibidas en las solicitudes del usuario autenticado (hechas por otros usuarios).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function receivedOffers(Request $request): JsonResponse
    {
        try {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $offers */
            $offers = ServiceOffer::whereHas('serviceRequest', function ($query) {
                $query->where('user_id', auth()->id());
            })
                ->where('user_id', '!=', auth()->id())
                ->with(['user', 'serviceRequest'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 10));

            return $this->successResponse([
                'items' => UserOfferResource::collection($offers),
                'meta' => [
                    'pagination' => [
                        'current_page' => $offers->currentPage(),
                        'last_page' => $offers->lastPage(),
                        'per_page' => $offers->perPage(),
                        'total' => $offers->total()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving received offers',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista todas las ofertas enviadas por el usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sentOffers(Request $request): JsonResponse
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $offers */
        $offers = ServiceOffer::where('user_id', auth()->id())
            ->with(['serviceRequest', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        Log::info('DEBUG - Ofertas enviadas encontradas', [
            'user_id' => auth()->id(),
            'count' => $offers->total(),
            'ids' => $offers->pluck('id'),
            'data' => $offers->toArray(),
        ]);

        return $this->successResponse([
            'items' => UserOfferResource::collection($offers),
            'meta' => [
                'pagination' => [
                    'current_page' => $offers->currentPage(),
                    'last_page' => $offers->lastPage(),
                    'per_page' => $offers->perPage(),
                    'total' => $offers->total()
                ]
            ]
        ]);
    }

    /**
     * Muestra el detalle de una oferta específica.
     *
     * @param ServiceOffer $offer
     * @return JsonResponse
     */
    public function showOffer(ServiceOffer $offer): JsonResponse
    {
        try {
            if (!$offer->serviceRequest || $offer->serviceRequest->user_id !== auth()->id()) {
                return $this->errorResponse(
                    message: 'You do not have permission to view this offer',
                    statusCode: 403
                );
            }

            $offer->load(['user', 'serviceRequest.categories']);

            return $this->successResponse(
                data: $offer,
                message: 'Offer details retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving offer details',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista las ofertas de una solicitud específica.
     *
     * @param Request $request
     * @param int|string $id ID de la solicitud
     * @return JsonResponse
     */
    public function requestOffers(Request $request, int|string $id): JsonResponse
    {
        try {
            /** @var ServiceRequest $serviceRequest */
            $serviceRequest = ServiceRequest::where('user_id', auth()->id())
                ->findOrFail($id);

            $query = $serviceRequest->offers()->with(['user']);

            // Filtros
            if ($request->filled('status')) {
                $query->whereIn('status', explode(',', $request->input('status')));
            }

            // Ordenamiento
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = ['created_at', 'price_proposed', 'status'];

            if (in_array($sortField, $allowedSortFields, true)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginación
            $perPage = $request->input('per_page', 10);
            /** @var \Illuminate\Pagination\LengthAwarePaginator $offers */
            $offers = $query->paginate($perPage);

            return $this->successResponse(
                data: [
                    'service_request' => $serviceRequest,
                    'offers' => $offers,
                    'meta' => [
                        'pagination' => [
                            'current_page' => $offers->currentPage(),
                            'last_page' => $offers->lastPage(),
                            'per_page' => $offers->perPage(),
                            'total' => $offers->total()
                        ]
                    ]
                ],
                message: 'Request offers retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving request offers',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista todas las ofertas realizadas por el usuario autenticado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myOffers(Request $request): JsonResponse
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $offers */
        $offers = ServiceOffer::where('user_id', auth()->id())
            ->with(['serviceRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return $this->successResponse([
            'items' => $offers,
            'meta' => [
                'pagination' => [
                    'current_page' => $offers->currentPage(),
                    'last_page' => $offers->lastPage(),
                    'per_page' => $offers->perPage(),
                    'total' => $offers->total()
                ]
            ]
        ]);
    }
}
