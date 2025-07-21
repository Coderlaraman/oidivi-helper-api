<?php

namespace App\Http\Controllers\Api\V1\User\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\User\UserContractResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceOffer;
use Illuminate\Validation\ValidationException;

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
                $query->where('status', $request->input());
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
                            'available_statuses' => ['pending', 'in_progress', 'completed', 'canceled'],
                            'applied_filters' => array_filter([
                                'status' => $request->input('status'),
                                'sort_by' => $sortField,
                                'sort_direction' => $sortDirection
                            ])
                        ]
                    ]
                ],
                message: __('messages.contracts.success.retrieved')
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.retrieving'),
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
                    message: __('messages.contracts.errors.unauthorized'),
                    statusCode: 403
                );
            }

            $contract->load(['serviceOffer.serviceRequest', 'provider', 'client']);

            return $this->successResponse(
                data: $contract,
                message: __('messages.contracts.success.retrieved')
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.retrieving'),
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
                    message: __('messages.contracts.errors.unauthorized'),
                    statusCode: 403
                );
            }

            // Ensure the service offer is in 'accepted' status
            if ($serviceOffer->status !== 'accepted') {
                return $this->errorResponse(
                    message: __('messages.service_offers.errors.must_be_accepted_for_contract')
                );
            }

            // Check if a contract already exists for this service offer
            if ($serviceOffer->contract()->exists()) {
                return $this->errorResponse(
                    message: __('messages.contracts.errors.already_exists'),
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
                message: __('messages.contracts.success.created'),
                statusCode: 201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.validation_failed'),
                statusCode: 422,
                errors: $e->errors()
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse(
                message: 'Service offer not found' . $e->getMessage(),
                statusCode: 404
            );
        } catch (Exception $e) {
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
                    message: __('messages.contracts.errors.unauthorized'),
                    statusCode: 403
                );
            }

            $validatedData = $request->validate([
                'status' => 'required|string|in:' . implode(',', Contract::STATUSES),
            ]);

            $newStatus = $validatedData['status'];
            if (!in_array($newStatus, Contract::STATUSES)) {
                return $this->errorResponse(
                    message: __('messages.contracts.errors.invalid_status')
                );
            }

            $contract->update(['status' => $newStatus]);
            $contract->load(['serviceOffer.serviceRequest', 'provider', 'client']);

            return $this->successResponse(
                data: new UserContractResource($contract),
                message: __('messages.contracts.success.updated')
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.validation_failed'),
                statusCode: 422,
                errors: $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.update_failed'),
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
                    message: __('messages.contracts.errors.unauthorized'),
                    statusCode: 403
                );
            }

            // Business logic: Allow deletion only within a certain time frame (e.g., 24 hours) after creation
            // This prevents unilateral deletion after resources might have been invested.
            $deletionAllowedHours = 24;
            if ($contract->created_at->addHours($deletionAllowedHours)->isPast()) {
                return $this->errorResponse(
                    message: __('messages.contracts.errors.deletion_time_exceeded', ['hours' => $deletionAllowedHours]),
                    statusCode: 403
                );
            }

            // Prevent deletion if contract status is not 'pending' or 'canceled'
            if (!in_array($contract->status, ['pending', 'canceled'])) {
                return $this->errorResponse(
                    message: __('messages.contracts.errors.invalid_status'),
                    statusCode: 403
                );
            }

            $contract->delete();

            return $this->successResponse(
                message: __('messages.contracts.success.deleted'),
                statusCode: 204
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                message: __('messages.contracts.errors.delete_failed'),
                statusCode: 500,
                errors: ['error' => $e->getMessage()]
            );
        }
    }
}
