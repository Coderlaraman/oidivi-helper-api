<?php

namespace App\Http\Controllers\Api\V1\User\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserContractController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Contract::where('client_id', $user->id)
                ->orWhere('provider_id', $user->id)
                ->with(['serviceOffer.serviceRequest', 'provider', 'client']);

            // Filtering by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $allowedSortFields = ['created_at', 'price', 'status', 'start_date', 'end_date'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $contracts = $query->paginate($perPage);

            return $this->successResponse(
                data: [
                    'items' => $contracts,
                    'meta' => [
                        'pagination' => [
                            'current_page' => $contracts->currentPage(),
                            'last_page' => $contracts->lastPage(),
                            'per_page' => $contracts->perPage(),
                            'total' => $contracts->total()
                        ],
                        'filters' => [
                            'available_statuses' => ['pending', 'in_progress', 'completed', 'cancelled'],
                            'applied_filters' => array_filter([
                                'status' => $request->status,
                                'sort_by' => $sortField,
                                'sort_direction' => $sortDirection
                            ])
                        ]
                    ]
                ],
                message: 'Contracts retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving contracts',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    public function show(Contract $contract): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($contract->client_id !== $user->id && $contract->provider_id !== $user->id) {
                return $this->errorResponse(
                    message: 'Unauthorized to view this contract',
                    statusCode: 403
                );
            }

            $contract->load(['serviceOffer.serviceRequest', 'provider', 'client']);

            return $this->successResponse(
                data: $contract,
                message: 'Contract retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error retrieving contract',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'service_offer_id' => 'required|exists:service_offers,id',
            ]);

            $user = auth()->user();
            $serviceOffer = ServiceOffer::with('serviceRequest')->findOrFail($validatedData['service_offer_id']);

            // Ensure the authenticated user is the owner of the service request
            if ($serviceOffer->serviceRequest->user_id !== $user->id) {
                return $this->errorResponse(
                    message: 'Unauthorized to create a contract for this service offer',
                    statusCode: 403
                );
            }

            // Ensure the service offer is in 'accepted' status
            if ($serviceOffer->status !== 'accepted') {
                return $this->errorResponse(
                    message: 'Only accepted service offers can be converted to contracts',
                    statusCode: 400
                );
            }

            // Check if a contract already exists for this service offer
            if ($serviceOffer->contract()->exists()) {
                return $this->errorResponse(
                    message: 'A contract already exists for this service offer',
                    statusCode: 409
                );
            }

            DB::beginTransaction();

            $contract = Contract::create([
                'service_offer_id' => $serviceOffer->id,
                'service_request_id' => $serviceOffer->service_request_id,
                'provider_id' => $serviceOffer->user_id,
                'client_id' => $serviceOffer->serviceRequest->user_id,
                'price' => $serviceOffer->price_proposed,
                'estimated_time' => $serviceOffer->estimated_time,
                'status' => 'pending' // Initial status for the contract
            ]);

            // Optionally, update service request status to 'in_progress' or similar
            $serviceOffer->serviceRequest->update(['status' => 'in_progress']);

            DB::commit();

            return $this->successResponse(
                data: $contract->load(['serviceOffer.serviceRequest', 'provider', 'client']),
                message: 'Contract created successfully',
                statusCode: 201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                message: 'Validation failed',
                statusCode: 422,
                errors: $e->errors()
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                message: 'Service offer not found',
                statusCode: 404
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                message: 'Error creating contract',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($contract->client_id !== $user->id && $contract->provider_id !== $user->id) {
                return $this->errorResponse(
                    message: 'Unauthorized to update this contract',
                    statusCode: 403
                );
            }

            $validatedData = $request->validate([
                'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            ]);

            // Implement business logic for status transitions if necessary
            // For example, prevent changing from 'completed' to 'pending'
            // if ($contract->status === 'completed' && $validatedData['status'] !== 'completed') {
            //     return $this->errorResponse(
            //         message: 'Cannot change status of a completed contract',
            //         statusCode: 400
            //     );
            // }

            $contract->update($validatedData);

            $contract->load(['serviceOffer.serviceRequest', 'provider', 'client']);

            return $this->successResponse(
                data: $contract,
                message: 'Contract updated successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                message: 'Validation failed',
                statusCode: 422,
                errors: $e->errors()
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error updating contract',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }

    public function destroy(Contract $contract): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($contract->client_id !== $user->id && $contract->provider_id !== $user->id) {
                return $this->errorResponse(
                    message: 'Unauthorized to delete this contract',
                    statusCode: 403
                );
            }

            // Business logic: Allow deletion only within a certain time frame (e.g., 24 hours) after creation
            // This prevents unilateral deletion after resources might have been invested.
            $deletionAllowedHours = 24;
            if ($contract->created_at->addHours($deletionAllowedHours)->isPast()) {
                return $this->errorResponse(
                    message: 'Contract cannot be deleted after ' . $deletionAllowedHours . ' hours from creation.',
                    statusCode: 403
                );
            }

            // Prevent deletion if contract status is not 'pending' or 'cancelled'
            if (!in_array($contract->status, ['pending', 'cancelled'])) {
                return $this->errorResponse(
                    message: 'Only pending or cancelled contracts can be deleted.',
                    statusCode: 403
                );
            }

            $contract->delete();

            return $this->successResponse(
                message: 'Contract deleted successfully',
                statusCode: 204
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Error deleting contract',
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }
}
