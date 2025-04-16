<?php

namespace App\Http\Controllers\Api\V1\User\ServiceOffers;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PushNotification;

class UserServiceOfferController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->hasCompatibleSkills($serviceRequest)) {
                return $this->errorResponse(
                    message: __('service_offers.errors.skills_required'),
                    statusCode: 403
                );
            }

            DB::beginTransaction();

            $offer = ServiceOffer::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $user->id,
                'price_proposed' => $request->input('price_proposed'),
                'estimated_time' => $request->input('estimated_time'),
                'message' => $request->input('message'),
                'status' => 'pending'
            ]);

            PushNotification::create([
                'user_id' => $serviceRequest->user_id,
                'service_request_id' => $serviceRequest->id,
                'title' => __('service_offers.notifications.new_offer_title'),
                'message' => __('service_offers.notifications.new_offer_message', [
                    'title' => $serviceRequest->title
                ])
            ]);

            DB::commit();

            return $this->successResponse($offer, __('service_offers.success.created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(__('service_offers.errors.creation_failed'), 500);
        }
    }

    public function update(Request $request, ServiceOffer $offer): JsonResponse
    {
        if ($offer->serviceRequest->user_id !== auth()->id()) {
            return $this->errorResponse(__('service_offers.errors.unauthorized'), 403);
        }

        try {
            DB::beginTransaction();

            $offer->update([
                'status' => $request->input('status')
            ]);

            PushNotification::create([
                'user_id' => $offer->user_id,
                'service_request_id' => $offer->service_request_id,
                'title' => __('service_offers.notifications.status_update_title'),
                'message' => __('service_offers.notifications.status_update_message', [
                    'title' => $offer->serviceRequest->title,
                    'status' => $offer->status
                ])
            ]);

            DB::commit();

            return $this->successResponse($offer, __('service_offers.success.updated'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(__('service_offers.errors.update_failed'), 500);
        }
    }
}
