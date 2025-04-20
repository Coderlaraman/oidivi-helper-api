<?php

namespace App\Http\Controllers\Api\V1\User\ServiceOffers;

use App\Events\NewServiceOfferNotification;
use App\Events\ServiceOfferStatusUpdatedNotification;
use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserServiceOfferController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, $serviceRequest): JsonResponse
    {
        try {
            // Buscar la solicitud de servicio con sus categorías
            $serviceRequest = ServiceRequest::with(['categories', 'offers'])->findOrFail($serviceRequest);

            Log::info('Starting offer creation process', [
                'service_request_id' => $serviceRequest->id,
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $user = auth()->user();

            // 1. Verificar si el usuario ya tiene una oferta para esta solicitud
            $existingOffer = $serviceRequest->offers()
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
            $userSkillCategories = $user->skills()
                ->with('categories')
                ->get()
                ->pluck('categories')
                ->flatten();

            $userSkillCategoryIds = $userSkillCategories->pluck('id')->unique()->toArray();
            $matchingCategories = array_intersect($requestCategoryIds, $userSkillCategoryIds);

            // Log detallado de la verificación de categorías
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
                    message: 'You don\'t have the necessary skills to make an offer for this service request',
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
                // Crear la oferta
                $offer = ServiceOffer::create([
                    'service_request_id' => $serviceRequest->id,
                    'user_id' => $user->id,
                    'price_proposed' => $validatedData['price_proposed'],
                    'estimated_time' => $validatedData['estimated_time'],
                    'message' => $validatedData['message'],
                    'status' => 'pending'
                ]);

                Log::info('Offer created successfully', [
                    'offer_id' => $offer->id,
                    'service_request_id' => $serviceRequest->id,
                    'matching_categories' => array_values($matchingCategories)
                ]);

                // Crear notificación
                PushNotification::create([
                    'user_id' => $serviceRequest->user_id,
                    'service_request_id' => $serviceRequest->id,
                    'title' => __('service_offers.notifications.new_offer_title'),
                    'message' => __('service_offers.notifications.new_offer_message', [
                        'title' => $serviceRequest->title
                    ])
                ]);

                // Emitir evento de notificación
                event(new NewServiceOfferNotification($offer, $serviceRequest->user_id));

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

    public function update(Request $request, ServiceOffer $offer): JsonResponse
    {
        if ($offer->serviceRequest->user_id !== auth()->id()) {
            return $this->errorResponse(
                message: __('service_offers.errors.unauthorized'),
                statusCode: 403
            );
        }

        try {
            DB::beginTransaction();
            try {
                $oldStatus = $offer->status;
                $newStatus = $request->input('status');

                $offer->update([
                    'status' => $newStatus
                ]);

                // Crear notificación en la base de datos
                PushNotification::create([
                    'user_id' => $offer->user_id,
                    'service_request_id' => $offer->service_request_id,
                    'title' => __('service_offers.notifications.status_update_title'),
                    'message' => __('service_offers.notifications.status_update_message', [
                        'title' => $offer->serviceRequest->title,
                        'status' => $offer->status
                    ])
                ]);

                // Emitir evento de notificación en tiempo real
                event(new ServiceOfferStatusUpdatedNotification($offer, $offer->user_id));

                DB::commit();

                Log::info('Offer status updated', [
                    'offer_id' => $offer->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => auth()->id()
                ]);

                return $this->successResponse(
                    data: $offer->load(['user', 'serviceRequest']),
                    message: __('service_offers.success.updated')
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error updating offer', [
                'error' => $e->getMessage(),
                'offer_id' => $offer->id
            ]);
            return $this->errorResponse(
                message: __('service_offers.errors.update_failed'),
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }
}
