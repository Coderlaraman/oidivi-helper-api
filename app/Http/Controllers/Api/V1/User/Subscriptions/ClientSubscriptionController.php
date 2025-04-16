<?php

namespace App\Http\Controllers\Api\V1\User\Subscriptions;

use App\Http\Controllers\Api\V1\Client\Subscriptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserSubscriptionResource;
use App\Models\Subscription;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientSubscriptionController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        try {
            $subscriptions = Subscription::where('user_id', auth()->id())
                ->with('user')
                ->latest()
                ->get();

            return $this->successResponse(
                UserSubscriptionResource::collection($subscriptions),
                __('messages.subscriptions.list_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.subscriptions.list_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan_name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'details' => 'nullable|json'
            ]);

            $subscription = Subscription::create([
                'user_id' => auth()->id(),
                ...$validated,
                'status' => 'active'
            ]);

            return $this->successResponse(
                new UserSubscriptionResource($subscription),
                __('messages.subscriptions.created'),
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.subscriptions.create_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function cancel(Subscription $subscription): JsonResponse
    {
        try {
            if ($subscription->user_id !== auth()->id()) {
                return $this->errorResponse(
                    __('messages.unauthorized'),
                    403
                );
            }

            $subscription->update(['status' => 'canceled']);

            return $this->successResponse(
                new UserSubscriptionResource($subscription),
                __('messages.subscriptions.canceled')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.subscriptions.cancel_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show(Subscription $subscription): JsonResponse
    {
        try {
            if ($subscription->user_id !== auth()->id()) {
                return $this->errorResponse(
                    __('messages.unauthorized'),
                    403
                );
            }

            return $this->successResponse(
                new UserSubscriptionResource($subscription->load('user')),
                __('messages.subscriptions.show_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.subscriptions.show_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
