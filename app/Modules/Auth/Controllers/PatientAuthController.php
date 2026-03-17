<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Modules\Auth\Actions\LogoutAction;
use App\Modules\Auth\Actions\PatientLoginAction;
use App\Modules\Auth\Actions\ChangePasswordAction;
use App\Modules\Auth\Actions\PatientRegisterAction;
use App\Modules\Auth\DTOs\PatientLoginData;
use App\Modules\Auth\DTOs\PatientRegisterData;
use App\Modules\Auth\Requests\ChangePasswordRequest;
use App\Modules\Auth\Requests\PatientLoginRequest;
use App\Modules\Auth\Requests\PatientRegisterRequest;
use App\Modules\Auth\Resources\AuthPatientResource;

class PatientAuthController extends Controller
{
    public function register(
        PatientRegisterRequest $request,
        PatientRegisterAction $action
    ): JsonResponse {
        $result = $action->execute(
            PatientRegisterData::fromRequest($request)
        );

        return ApiResponse::success(
            [
                'token' => $result['token'],
                'firebase_token_registered' => $result['firebase_token_registered'] ?? false,
                'patient' => new AuthPatientResource($result['patient']),
            ],
            'Patient registered successfully.'
        );
    }

    public function login(
        PatientLoginRequest $request,
        PatientLoginAction $action
    ): JsonResponse {
        $result = $action->execute(
            PatientLoginData::fromRequest($request)
        );

        return ApiResponse::success(
            [
                'token' => $result['token'],
                'firebase_token_registered' => $result['firebase_token_registered'] ?? false,
                'patient' => new AuthPatientResource($result['patient']),
            ],
            'Login successful.'
        );
    }

    public function me(): JsonResponse
    {
        $patient = Auth::user();

        return ApiResponse::success(
            new AuthPatientResource($patient),
            'Patient profile fetched successfully.'
        );
    }

    public function logout(
        LogoutAction $action
    ): JsonResponse {
        $action->execute(Auth::user());

        return ApiResponse::success(
            null,
            'Logout successful.'
        );
    }

    public function changePassword(
        ChangePasswordRequest $request,
        ChangePasswordAction $action
    ): JsonResponse {
        $action->execute(Auth::user(), $request->validated());

        return ApiResponse::success(
            null,
            'Password changed successfully.'
        );
    }
}
