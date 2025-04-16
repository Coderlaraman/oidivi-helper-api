<?php

namespace App\Http\Controllers\Api\V1\User\Reviews;

use App\Http\Controllers\Api\V1\Client\Reviews\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserReviewResource;
use App\Models\Review;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewed_id' => 'required|exists:users,id',
                'service_request_id' => 'required|exists:service_requests,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string'
            ]);

            $review = Review::create([
                'reviewer_id' => auth()->id(),
                ...$validated
            ]);

            return $this->successResponse(
                new UserReviewResource($review),
                __('messages.reviews.created'),
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
                __('messages.reviews.create_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function index($userId): JsonResponse
    {
        try {
            $reviews = Review::where('reviewed_id', $userId)
                ->with(['reviewer', 'serviceRequest'])
                ->latest()
                ->get();

            return $this->successResponse(
                UserReviewResource::collection($reviews),
                __('messages.reviews.list_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.reviews.list_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
