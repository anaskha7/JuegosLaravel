<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FacialRecognitionService;
use App\Support\Base64Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FaceLoginController extends Controller
{
    public function store(Request $request, FacialRecognitionService $facialRecognition): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'capture_image' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No existe ninguna cuenta con ese correo.',
            ]);
        }

        if (! $user->face_reference_path || ! Storage::disk('local')->exists($user->face_reference_path)) {
            throw ValidationException::withMessages([
                'capture_image' => 'Este usuario no tiene Face ID configurado. Entra con contraseña y actívalo desde tu cuenta.',
            ]);
        }

        $capturePath = Base64Image::temporaryPath($validated['capture_image'], 'face-login');

        try {
            $result = $facialRecognition->verify(
                Storage::disk('local')->path($user->face_reference_path),
                $capturePath
            );
        } catch (RuntimeException $exception) {
            @unlink($capturePath);

            throw ValidationException::withMessages([
                'capture_image' => $exception->getMessage(),
            ]);
        }

        @unlink($capturePath);

        if (! $result['verified']) {
            throw ValidationException::withMessages([
                'capture_image' => 'La cara no coincide con la foto guardada.',
            ]);
        }

        return $this->loginUser($request, $user, 'Acceso facial correcto.');
    }

    private function loginUser(Request $request, User $user, string $message): JsonResponse
    {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $target = $user->hasAnyRole(UserRole::Admin, UserRole::Manager)
            ? route('dashboard')
            : route('catalog.index');

        return response()->json([
            'message' => $message,
            'redirect' => $target,
        ]);
    }
}
