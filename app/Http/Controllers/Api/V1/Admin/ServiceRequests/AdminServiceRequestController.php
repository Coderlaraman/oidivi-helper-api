<?php

namespace App\Http\Controllers\Api\V1\Admin\ServiceRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest\AdminListServiceRequestRequest;
use App\Http\Requests\Admin\ServiceRequest\AdminUpdateServiceRequestRequest;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminServiceRequestController extends Controller
{
    /**
     * Display a listing of the service requests.
     */
    public function index(AdminListServiceRequestRequest $request): JsonResponse
    {
        $query = ServiceRequest::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->boolean('show_deleted')) {
            $query->withTrashed();
        }

        // Cargar relaciones
        if ($request->boolean('with_category')) {
            $query->with('category');
        }

        if ($request->boolean('with_user')) {
            $query->with('user');
        }

        // Ordenar
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginar
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // Cache
        $cacheKey = "admin:service_requests:list:{$request->fullUrl()}";
        $serviceRequests = Cache::remember($cacheKey, 300, function () use ($query, $perPage, $page) {
            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json($serviceRequests);
    }

    /**
     * Display the specified service request.
     */
    public function show(ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->load(['category', 'user']);

        return response()->json($serviceRequest);
    }

    /**
     * Update the specified service request in storage.
     */
    public function update(AdminUpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'due_date' => $request->input('due_date'),
            'metadata' => $request->input('metadata'),
        ]);

        Cache::tags(['service_requests'])->flush();

        return response()->json($serviceRequest);
    }

    /**
     * Remove the specified service request from storage.
     */
    public function destroy(ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->delete();

        Cache::tags(['service_requests'])->flush();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified service request from storage.
     */
    public function restore(ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->restore();

        Cache::tags(['service_requests'])->flush();

        return response()->json($serviceRequest);
    }
}
