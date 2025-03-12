<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceRequestController extends Controller
{
    // Listado de ofertas de servicio con filtros avanzados
    public function index(\Illuminate\Http\Request $request)
    {
        $query = ServiceRequest::query();

        // Búsqueda de texto libre en título y descripción
        if ($request->filled('query')) {
            $q = $request->input('query');
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        // Agregar otros filtros (presupuesto, ubicación, etc.) según necesidad

        $services = $query->paginate(10);
        return ServiceRequestResource::collection($services);
    }

    // Publicar una nueva oferta de servicio
    public function store(StoreServiceRequest $request)
    {
        // Los datos validados se obtienen desde el Request personalizado
        $data = $request->validated();

        // Crear la oferta asociada al usuario autenticado
        $service = ServiceRequest::create(array_merge(
            $data,
            ['user_id' => Auth::id(), 'status' => 'published']
        ));

        // Asignar categorías usando la relación polimórfica
        $service->categories()->sync($data['category_ids']);

        return (new ServiceRequestResource($service))
            ->additional(['message' => 'Service published successfully'])
            ->response()
            ->setStatusCode(201);
    }

    // Mostrar detalles de una oferta
    public function show($id)
    {
        $service = ServiceRequest::with(['categories', 'user'])->findOrFail($id);
        return new ServiceRequestResource($service);
    }

    // Actualizar una oferta existente
    public function update(UpdateServiceRequest $request, $id)
    {
        $service = ServiceRequest::findOrFail($id);

        // Verificar que el usuario autenticado es el propietario de la oferta
        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $service->update($data);

        if (isset($data['category_ids'])) {
            $service->categories()->sync($data['category_ids']);
        }

        return (new ServiceRequestResource($service))
            ->additional(['message' => 'Service updated successfully']);
    }

    // Eliminar una oferta (o cancelarla)
    public function destroy($id)
    {
        $service = ServiceRequest::findOrFail($id);

        if ($service->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $service->delete();
        return response()->json(['message' => 'Service deleted successfully']);
    }

    // Almacenar reseña para una oferta
    public function storeReview(StoreReviewRequest $request, $serviceId)
    {
        $service = ServiceRequest::findOrFail($serviceId);
        $data = $request->validated();

        // Se asume que existe la relación 'reviews' en el modelo ServiceRequest
        // y un modelo Review con campos 'user_id', 'rating' y 'comment'
        $review = $service->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        return response()->json([
            'message' => 'Review saved successfully',
            'data' => $review
        ], 201);
    }

    // Registrar la contratación (transacción) de una oferta
    public function storeTransaction(StoreTransactionRequest $request, $serviceId)
    {
        $service = ServiceRequest::findOrFail($serviceId);
        $data = $request->validated();

        // Verificar que el usuario autenticado es un helper (por ejemplo, mediante un método hasRole)
        if (!Auth::user()->hasRole('helper')) {
            return response()->json(['message' => 'Only helpers can register a transaction'], 403);
        }

        // Se puede agregar lógica para validar que el helper cuenta con las habilidades requeridas,
        // por ejemplo, comparando las categorías asociadas al servicio con las habilidades del helper.
        // Esto se deja a implementación según tu lógica de negocio.

        // Actualizar el estado de la oferta a 'in_progress' (o el que corresponda)
        $service->update(['status' => 'in_progress']);

        // Se asume la existencia de una relación 'transactions' en ServiceRequest
        // y un modelo Transaction que almacene los detalles de la contratación.
        $transaction = $service->transactions()->create([
            'helper_id' => Auth::id(),
            'proposed_price' => $data['proposed_price'],  // Ejemplo de campo
            'message' => $data['message'] ?? null,
        ]);

        // Se podría notificar al cliente y al helper a través de eventos o notificaciones

        return response()->json([
            'message' => 'Transaction registered successfully',
            'data' => $transaction
        ], 201);
    }
}
