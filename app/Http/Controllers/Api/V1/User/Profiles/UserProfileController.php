<?php

namespace App\Http\Controllers\Api\V1\User\Profiles;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UpdateUserProfileRequest;
use App\Http\Resources\User\PublicUserResource;
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
        $response = new UserAuthResource($user);

        $response->additional([
            'needs_skill_setup' => $user->needsSkillSetup(),
            'message' => $user->needsSkillSetup()
                ? __('messages.profile.skill_setup_required')
                : null
        ]);

        return $this->successResponse($response, __('messages.profile.data_retrieved'));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateUserProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $user->update($data);
            return $this->successResponse(new UserAuthResource($user), __('messages.profile.updated'));
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
            __('messages.profile.photo_updated')
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
            return $this->successResponse([], __('messages.profile.photo_deleted'));
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
            __('messages.profile.video_updated'),
            ['mimes:mp4,mov,avi,wmv', 'max:10240']
        );
    }

    /**
 * Delete the authenticated user's profile video.
 *
 * @return JsonResponse
 */
public function deleteProfileVideo(): JsonResponse
{
    try {
        $user = Auth::user();
        if ($user->profile_video_url) {
            Storage::disk('public')->delete($user->profile_video_url);
            $user->profile_video_url = null;
            $user->save();
        }
        return $this->successResponse([], __('messages.profile.video_deleted'));
    } catch (Exception $e) {
        return $this->errorResponse(__('messages.general_error'), 500, ['error' => $e->getMessage()]);
    }
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
    protected function handleFileUpload(Request $request, string $fieldName, string $directory, string $successMessage, array $rules = [
        'image',
        'mimes:jpeg,png,jpg,gif',
        'max:2048',
        'dimensions:min_width=200,min_height=200,max_width=2000,max_height=2000'
    ]): JsonResponse
    {
        try {
            $request->validate([
                $fieldName => 'required|' . implode('|', $rules),
            ]);
            $user = Auth::user();
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                // Validación MIME real
                $mime = mime_content_type($file->getPathname());
                $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif'];
                $allowedVideoMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];

                $isImage = in_array('image', $rules);
                $isVideo = in_array('mimes:mp4,mov,avi,wmv', $rules); // o define por contexto

                if (($isImage && !in_array($mime, $allowedImageMimes)) || ($isVideo && !in_array($mime, $allowedVideoMimes))) {
                    return $this->errorResponse(__('messages.invalid_file_type'), 422);
}

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
            return $this->errorResponse(__('messages.profile_photo.invalid'), 400);
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
        return $this->successResponse($user->skills, __('messages.profile.skills_updated'));
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
        return $this->successResponse(UserAuthResource::collection($users), __('messages.profile.users_retrieved'));
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
        ], __('messages.dashboard.data_retrieved'));
    }

    /**
     * Retrieve the public profile information for a given user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function showPublicProfile(User $user): JsonResponse
    {
        $user->load(['skills', 'stats', 'reviewsReceived']);

        return $this->successResponse(PublicUserResource::make($user), 'Public user profile retrieved successfully.');
    }
}
