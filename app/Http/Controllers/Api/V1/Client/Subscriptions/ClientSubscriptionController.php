<?php

namespace App\Http\Controllers\Api\V1\Client\Subscriptions;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientSubscriptionResource;
use App\Models\Subscription;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

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
                ClientSubscriptionResource::collection($subscriptions),
                'Subscriptions retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving subscriptions',
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
                new ClientSubscriptionResource($subscription),
                'Subscription created successfully',
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Validation error',
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error creating subscription',
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
                    'Unauthorized',
                    403
                );
            }

            $subscription->update(['status' => 'canceled']);

            return $this->successResponse(
                new ClientSubscriptionResource($subscription),
                'Subscription canceled successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error canceling subscription',
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
                    'Unauthorized',
                    403
                );
            }

            return $this->successResponse(
                new ClientSubscriptionResource($subscription->load('user')),
                'Subscription retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving subscription',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
