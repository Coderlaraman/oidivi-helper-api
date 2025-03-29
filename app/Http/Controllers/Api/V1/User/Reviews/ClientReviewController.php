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
                'Reseña creada exitosamente',
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Error de validación',
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al crear reseña',
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
                'Reseñas recuperadas exitosamente'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al obtener reseñas',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
