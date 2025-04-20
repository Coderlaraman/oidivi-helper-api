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

class UserServiceOfferController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, $id): JsonResponse
    {
        // Log inicial de la solicitud
        Log::info('Iniciando proceso de creación de oferta', [
            'service_request_id' => $id,
            'user_id' => auth()->id(),
            'request_data' => $request->all()
        ]);

        try {
            // Buscar la solicitud de servicio
            $serviceRequest = ServiceRequest::with(['categories'])->find($id);

            if (!$serviceRequest) {
                Log::warning('Solicitud de servicio no encontrada', ['id' => $id]);
                return $this->errorResponse(
                    message: 'La solicitud de servicio no existe',
                    statusCode: 404
                );
            }

            $user = auth()->user();

            // Verificar que el usuario no esté haciendo una oferta a su propia solicitud
            if ($serviceRequest->user_id === $user->id) {
                Log::warning('Usuario intentando ofertar en su propia solicitud', [
                    'user_id' => $user->id,
                    'service_request_id' => $id
                ]);
                return $this->errorResponse(
                    message: 'No puedes hacer una oferta a tu propia solicitud de servicio',
                    statusCode: 403
                );
            }

            // Obtener las categorías de la solicitud
            $requestCategoryIds = $serviceRequest->categories->pluck('id')->toArray();

            // Obtener las categorías de las habilidades del usuario
            $userSkillCategoryIds = $user->skills()
                ->with('categories')
                ->get()
                ->pluck('categories')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();

            // Verificar si hay categorías en común
            $sharedCategories = array_intersect($requestCategoryIds, $userSkillCategoryIds);

            Log::info('Verificación de categorías compartidas', [
                'user_id' => $user->id,
                'request_categories' => $requestCategoryIds,
                'user_skill_categories' => $userSkillCategoryIds,
                'shared_categories' => $sharedCategories
            ]);

            if (empty($sharedCategories)) {
                return $this->errorResponse(
                    message: 'No tienes las habilidades necesarias para esta solicitud de servicio',
                    statusCode: 403
                );
            }

            // Validar el estado de la solicitud
            if ($serviceRequest->status !== 'published') {
                return $this->errorResponse(
                    message: 'Esta solicitud de servicio no está disponible para ofertas',
                    statusCode: 403
                );
            }

            DB::beginTransaction();
            try {
                // Crear la oferta
                $offer = ServiceOffer::create([
                    'service_request_id' => $serviceRequest->id,
                    'user_id' => $user->id,
                    'price_proposed' => $request->input('price_proposed'),
                    'estimated_time' => $request->input('estimated_time'),
                    'message' => $request->input('message'),
                    'status' => 'pending'
                ]);

                Log::info('Oferta creada exitosamente', [
                    'offer_id' => $offer->id,
                    'service_request_id' => $serviceRequest->id
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
                    message: __('service_offers.success.created'),
                    statusCode: 201
                );

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en transacción de creación de oferta', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error general en creación de oferta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                message: 'Error al crear la oferta de servicio',
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
