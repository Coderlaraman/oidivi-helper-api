<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientAuthResource;
use App\Http\Resources\UserStatResource;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Exception;

class ClientUserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => __('messages.user_data_retrieved'),
            'data' => new ClientAuthResource($user),
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|min:7|max:15',
                'address' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            $user->update(array_filter($validatedData));

            return response()->json([
                'success' => true,
                'message' => __('messages.profile_updated'),
                'data' => new ClientAuthResource($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.general_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadProfilePhoto(Request $request)
    {
        return $this->handleFileUpload(
            $request,
            'profile_photo_url',
            'profile_photos',
            __('messages.profile_photo_updated')
        );
    }

    public function deleteProfilePhoto()
    {
        try {
            $user = Auth::user();

            if ($user->profile_photo_url) {
                Storage::disk('public')->delete($user->profile_photo_url);
                $user->profile_photo_url = null;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.profile_photo_deleted')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.general_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadProfileVideo(Request $request)
    {
        return $this->handleFileUpload(
            $request,
            'profile_video_url',
            'profile_videos',
            __('messages.profile_video_updated'),
            ['mimes:mp4,mov,avi,wmv', 'max:10240']  // Reglas específicas para videos
        );
    }

    private function handleFileUpload(Request $request, $fieldName, $directory, $successMessage, $rules = ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'])
    {
        try {
            $request->validate([
                $fieldName => 'required|' . implode('|', $rules),
            ]);

            $user = Auth::user();

            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs($directory, $filename, 'public');

                // Eliminar el archivo anterior si existe
                $oldFile = $user->{$fieldName};
                if ($oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }

                $user->{$fieldName} = $filePath;
                $user->save();

                return response()->json([
                    'message' => $successMessage,
                    $fieldName => asset('storage/' . $filePath),
                ]);
            }

            return response()->json([
                'message' => __('messages.invalid_file_provided'),
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => __('messages.general_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar las habilidades del usuario.
     */
    public function updateSkills(Request $request): JsonResponse
    {
        $request->validate([
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);

        $user = $request->user();
        $user->skills()->sync($request->skills);

        return response()->json(['message' => 'Skills successfully updated!', 'skills' => $user->skills]);
    }

    /**
     * Buscar usuarios por criterios específicos.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $skillIds = $request->input('skills');
        $categoryIds = $request->input('categories');

        $users = User::query()
            ->when($query, function ($q) use ($query) {
                return $q
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->when($skillIds, function ($q) use ($skillIds) {
                return $q->whereHas('skills', function ($q) use ($skillIds) {
                    $q->whereIn('skills.id', $skillIds);
                });
            })
            ->when($categoryIds, function ($q) use ($categoryIds) {
                return $q->whereHas('skills.categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => ClientAuthResource::collection($users),
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Si no existen estadísticas asociadas, crearlas con valores por defecto
        if (!$user->stats) {
            $user->stats()->create([
                'completed_tasks' => 0,
                'active_services' => 0,
                'total_earnings' => 0.0,
                'rating' => 0.0,
            ]);
        }

        // Cargar la relación para evitar consultas adicionales
        $user->load('stats');

        return response()->json([
            'success' => true,
            'message' => __('messages.dashboard_data_retrieved'),
            'data' => [
                'user' => new ClientAuthResource($user),
                'stats' => new UserStatResource($user->stats),
            ],
        ]);
    }
}
