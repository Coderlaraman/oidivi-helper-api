<?php

namespace App\Http\Controllers\Api\V1\User\Referrals;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserReferralResource;
use App\Models\Referral;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientReferralController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        try {
            $referrals = Referral::where('referrer_id', auth()->id())
                ->with(['referrer', 'referred'])
                ->latest()
                ->get();

            return $this->successResponse(
                UserReferralResource::collection($referrals),
                __('messages.referrals.list_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.referrals.list_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'referred_id' => 'required|exists:users,id'
            ]);

            $referral = Referral::create([
                'referrer_id' => auth()->id(),
                'referred_id' => $validated['referred_id']
            ]);

            return $this->successResponse(
                new UserReferralResource($referral),
                __('messages.referrals.created'),
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.referrals.create_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function accept(Request $request, Referral $referral): JsonResponse
    {
        try {
            if (auth()->id() !== $referral->referred_id) {
                return $this->errorResponse(
                    __('messages.unauthorized'),
                    403
                );
            }

            $referral->update(['accepted_at' => now()]);

            return $this->successResponse(
                new UserReferralResource($referral),
                __('messages.referrals.accepted')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.referrals.accept_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show(Referral $referral): JsonResponse
    {
        try {
            return $this->successResponse(
                new UserReferralResource($referral->load(['referrer', 'referred'])),
                __('messages.referrals.show_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.referrals.show_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function destroy(Referral $referral): JsonResponse
    {
        try {
            $referral->delete();

            return $this->successResponse(
                null,
                __('messages.referrals.deleted')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.referrals.delete_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
