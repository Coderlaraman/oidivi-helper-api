<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ClientAuthResource;

class ClientUserController extends Controller
{
    /**
     * Retorna los datos del usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Devuelve los datos del usuario como recurso de cliente
        return response()->json([
            'success' => true,
            'message' => 'User data retrieved successfully.',
            'data' => new ClientAuthResource($user),
        ]);
    }

    /**
     * Actualizar el perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Filtrar los campos proporcionados en la solicitud (no vacíos)
        $dataToUpdate = array_filter($validatedData, function ($value) {
            return !is_null($value);
        });

        $user->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ]);
    }


    public function updateName(Request $request)
    {
        $user = $request->user();

        // Validar el nombre
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Actualizar el nombre del usuario
        $user->update([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Name updated successfully.',
            'data' => $user,
        ]);
    }

}
