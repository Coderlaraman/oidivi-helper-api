<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\ServiceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    // Obtener todos los servicios
    public function index()
    {
        return Service::all();
    }

    // Obtener un servicio específico
    public function show($id)
    {
        return Service::findOrFail($id);
    }

    // Crear un nuevo servicio
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'nullable|integer',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['user_id'] = Auth::id(); // Asignar el ID del usuario autenticado

        return Service::create($validated);
    }

    // Actualizar un servicio existente
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'duration' => 'nullable|integer',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $service->update(array_filter($validated)); // Actualiza solo los campos que no son nulos

        return $service;
    }

    // Eliminar un servicio
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully.']);
    }

    // Crear una reseña para un servicio
    public function storeReview(Request $request, $serviceId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id(); // Asignar el ID del usuario autenticado
        $validated['service_id'] = $serviceId;

        return ServiceReview::create($validated);
    }

    // Crear una transacción para un servicio
    public function storeTransaction(Request $request, $serviceId)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'payment_method' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id(); // Asignar el ID del usuario autenticado
        $validated['service_id'] = $serviceId;

        return ServiceTransaction::create($validated);
    }
}
