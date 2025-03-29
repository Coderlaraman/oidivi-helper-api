<?php

namespace App\Http\Controllers\Api\V1\User\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateClientProfileRequest;
use App\Http\Resources\User\UserAuthResource;
use App\Http\Resources\User\UserStatResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Retrieve the authenticated user's profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->successResponse(new UserAuthResource($user), __('messages.user_data_retrieved'));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateClientProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $user->update($data);
            return $this->successResponse(new UserAuthResource($user), __('messages.profile_updated'));
        } catch (ValidationException $e) {
            return $this->errorResponse(__('messages.validation_error'), 422, $e->errors());
        } catch (Exception $e) {
            return $this->errorResponse(__('messages.general_error'), 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload a new profile photo for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        return $this->handleFileUpload(
            $request,
            'profile_photo_url',
            'profile_photos',
            __('messages.profile_photo_updated')
        );
    }

    /**
     * Delete the authenticated user's profile photo.
     *
     * @return JsonResponse
     */
    public function deleteProfilePhoto(): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->profile_photo_url) {
                Storage::disk('public')->delete($user->profile_photo_url);
                $user->profile_photo_url = null;
                $user->save();
            }
            return $this->successResponse([], __('messages.profile_photo_deleted'));
        } catch (Exception $e) {
            return $this->errorResponse(__('messages.general_error'), 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Upload a new profile video for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadProfileVideo(Request $request): JsonResponse
    {
        return $this->handleFileUpload(
            $request,
            'profile_video_url',
            'profile_videos',
            __('messages.profile_video_updated'),
            ['mimes:mp4,mov,avi,wmv', 'max:10240']
        );
    }

    /**
     * Handle file upload for profile media.
     *
     * @param Request $request
     * @param string $fieldName
     * @param string $directory
     * @param string $successMessage
     * @param array $rules
     * @return JsonResponse
     */
    protected function handleFileUpload(Request $request, string $fieldName, string $directory, string $successMessage, array $rules = ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048']): JsonResponse
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
                // Delete old file if exists
                if ($user->{$fieldName}) {
                    Storage::disk('public')->delete($user->{$fieldName});
                }
                $user->{$fieldName} = $filePath;
                $user->save();
                return $this->successResponse([
                    $fieldName => asset('storage/' . $filePath)
                ], $successMessage);
            }
            return $this->errorResponse(__('messages.invalid_file_provided'), 400);
        } catch (ValidationException $e) {
            return $this->errorResponse(__('messages.validation_error'), 422, $e->errors());
        } catch (Exception $e) {
            return $this->errorResponse(__('messages.general_error'), 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the authenticated user's skills.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSkills(Request $request): JsonResponse
    {
        $request->validate([
            'skills' => 'array',
            'skills.*' => 'exists:skills,id',
        ]);
        $user = $request->user();
        $user->skills()->sync($request->input('skills'));
        return $this->successResponse($user->skills, 'Skills updated successfully!');
    }

    /**
     * Search users based on specific criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $skillIds = $request->input('skills');
        $categoryIds = $request->input('categories');
        $users = User::query()
            ->when($query, function ($q) use ($query) {
                $q
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->when($skillIds, function ($q) use ($skillIds) {
                $q->whereHas('skills', function ($q) use ($skillIds) {
                    $q->whereIn('skills.id', $skillIds);
                });
            })
            ->when($categoryIds, function ($q) use ($categoryIds) {
                $q->whereHas('skills.categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            })
            ->get();
        return $this->successResponse(UserAuthResource::collection($users), 'Users retrieved successfully.');
    }

    /**
     * Retrieve dashboard data including user profile and statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->stats) {
            $user->stats()->create([
                'completed_tasks' => 0,
                'active_services' => 0,
                'total_earnings' => 0.0,
                'rating' => 0.0,
            ]);
        }
        $user->load('stats');
        return $this->successResponse([
            'user' => new UserAuthResource($user),
            'stats' => new UserStatResource($user->stats),
        ], __('messages.dashboard_data_retrieved'));
    }
}
