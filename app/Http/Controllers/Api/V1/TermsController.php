<?php

namespace App\Http\Controllers\Api\V1;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;

class TermsController extends Controller
{
    use ApiResponseTrait;

    public function show(): JsonResponse
    {
        $path = base_path('docs/terms_and_conditions.md');
        if (!file_exists($path)) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }
        $content = file_get_contents($path);
        return $this->successResponse(['terms' => $content], __('messages.success'));
    }
}
