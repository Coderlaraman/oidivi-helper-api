<?php

namespace App\Http\Controllers\Api\V1\Client\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientReviewRequest;
use App\Http\Requests\Client\StoreClientServiceRequest;
use App\Http\Requests\Client\StoreClientTransactionRequest;
use App\Http\Requests\Client\UpdateClientServiceRequest;
use App\Http\Resources\Client\ClientServiceRequestResource;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientServiceRequestController extends Controller
{
    use ApiResponseTrait;

    /**
     * List service requests with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServiceRequest::query();

        if ($request->filled('query')) {
            $q = $request->input('query');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder
                    ->where('title', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $services = $query->paginate(10);
        return $this->successResponse(ClientServiceRequestResource::collection($services), 'Service requests retrieved successfully.');
    }

    /**
     * Store a new service request.
     */
    public function store(StoreClientServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $service = ServiceRequest::create(array_merge(
            $data,
            ['user_id' => Auth::id(), 'status' => 'published']
        ));
        $service->categories()->sync($data['category_ids']);

        return $this->successResponse(new ClientServiceRequestResource($service), 'Service published successfully.', 201);
    }

    /**
     * Show service request details.
     */
    public function show($id): JsonResponse
    {
        $service = ServiceRequest::with(['categories', 'user'])->findOrFail($id);
        return $this->successResponse(new ClientServiceRequestResource($service), 'Service retrieved successfully.');
    }

    /**
     * Update an existing service request.
     */
    public function update(UpdateClientServiceRequest $request, $id): JsonResponse
    {
        $service = ServiceRequest::findOrFail($id);

        if ($service->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $data = $request->validated();
        $service->update($data);

        if (isset($data['category_ids'])) {
            $service->categories()->sync($data['category_ids']);
        }

        return $this->successResponse(new ClientServiceRequestResource($service), 'Service updated successfully.');
    }

    /**
     * Delete (or cancel) a service request.
     */
    public function destroy($id): JsonResponse
    {
        $service = ServiceRequest::findOrFail($id);

        if ($service->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $service->delete();
        return $this->successResponse([], 'Service deleted successfully.');
    }

    /**
     * Store a review for a service.
     */
    public function storeReview(StoreClientReviewRequest $request, $serviceId): JsonResponse
    {
        $service = ServiceRequest::findOrFail($serviceId);
        $data = $request->validated();

        $review = $service->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        return $this->successResponse($review, 'Review saved successfully.', 201);
    }

    /**
     * Register a transaction for a service request.
     */
    public function storeTransaction(StoreClientTransactionRequest $request, $serviceId): JsonResponse
    {
        $service = ServiceRequest::findOrFail($serviceId);
        $data = $request->validated();

        if (!Auth::user()->hasRole('helper')) {
            return $this->errorResponse('Only helpers can register a transaction', 403);
        }

        $service->update(['status' => 'in_progress']);

        $transaction = $service->transactions()->create([
            'helper_id' => Auth::id(),
            'proposed_price' => $data['proposed_price'],
            'message' => $data['message'] ?? null,
        ]);

        return $this->successResponse($transaction, 'Transaction registered successfully.', 201);
    }
}
