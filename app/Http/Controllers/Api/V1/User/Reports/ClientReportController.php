<?php

namespace App\Http\Controllers\Api\V1\User\Reports;

use App\Http\Controllers\Api\V1\Client\Reports\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientReportResource;
use App\Models\Report;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientReportController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reported_user' => 'nullable|exists:users,id',
                'service_request_id' => 'nullable|exists:service_requests,id',
                'transaction_id' => 'nullable|exists:transactions,id',
                'type' => 'required|in:fraud,abuse,payment_issue,other',
                'description' => 'required|string'
            ]);

            $report = Report::create([
                'reported_by' => auth()->id(),
                ...$validated,
                'status' => 'pending'
            ]);

            return $this->successResponse(
                new ClientReportResource($report->load(['reportedUser', 'serviceRequest', 'transaction'])),
                'Report submitted successfully',
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
                'Error submitting report',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function index(): JsonResponse
    {
        try {
            $reports = Report::where('reported_by', auth()->id())
                ->with(['reportedUser', 'serviceRequest', 'transaction'])
                ->latest()
                ->get();

            return $this->successResponse(
                ClientReportResource::collection($reports),
                'Reports retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving reports',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show(Report $report): JsonResponse
    {
        try {
            if ($report->reported_by !== auth()->id()) {
                return $this->errorResponse(
                    'Unauthorized access',
                    403
                );
            }

            return $this->successResponse(
                new ClientReportResource($report->load(['reportedUser', 'serviceRequest', 'transaction'])),
                'Report details retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving report details',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
