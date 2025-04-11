<?php

namespace App\Http\Controllers\Api\V1\User\ServiceOffers;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserServiceOfferController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->hasCompatibleSkills($serviceRequest)) {
                return $this->errorResponse(
                    message: 'No puedes realizar una oferta sin definir habilidades relacionadas a esta solicitud.',
                    statusCode: 403
                );
            }

            DB::beginTransaction();

            // Crear la oferta
            $offer = ServiceOffer::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $user->id,
                'price_proposed' => $request->input('price_proposed'),
                'estimated_time' => $request->input('estimated_time'),
                'message' => $request->input('message'),
                'status' => 'pending'
            ]);

            // Crear notificación para el dueño de la solicitud
            PushNotification::create([
                'user_id' => $serviceRequest->user_id,
                'service_request_id' => $serviceRequest->id,
                'title' => 'Nueva oferta recibida',
                'message' => "Has recibido una nueva oferta para tu solicitud: {$serviceRequest->title}"
            ]);

            DB::commit();

            return $this->successResponse($offer, 'Oferta enviada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al enviar la oferta', 500);
        }
    }

    public function update(Request $request, ServiceOffer $offer): JsonResponse
    {
        if ($offer->serviceRequest->user_id !== auth()->id()) {
            return $this->errorResponse('No autorizado', 403);
        }

        try {
            DB::beginTransaction();

            $offer->update([
                'status' => $request->input('status')
            ]);

            // Crear notificación para el usuario que hizo la oferta
            PushNotification::create([
                'user_id' => $offer->user_id,
                'service_request_id' => $offer->service_request_id,
                'title' => 'Actualización de oferta',
                'message' => "Tu oferta para la solicitud {$offer->serviceRequest->title} ha sido {$offer->status}"
            ]);

            DB::commit();

            return $this->successResponse($offer, 'Estado de la oferta actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al actualizar la oferta', 500);
        }
    }
} 