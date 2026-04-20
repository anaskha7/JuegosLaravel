<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PasswordRecoveryController extends Controller
{
    private const CODE_EXPIRATION_MINUTES = 15;

    public function createRequest(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($code),
                    'created_at' => now(),
                ]
            );

            Mail::raw(
                "Tu código para cambiar la contraseña es: {$code}",
                static function ($message) use ($email): void {
                    $message->to($email)->subject('Código para cambiar tu contraseña');
                }
            );

            $request->session()->flash(
                'reset_code_hint',
                app()->environment(['local', 'testing']) ? "Código de prueba: {$code}" : null
            );
        }

        return redirect()->route('password.verify', ['email' => $email])->with(
            'status',
            'Si la cuenta existe, hemos preparado un código para cambiar la contraseña.'
        );
    }

    public function createVerify(Request $request): Response|RedirectResponse
    {
        $email = (string) $request->query('email', '');

        if ($email === '') {
            return redirect()->route('password.request');
        }

        return Inertia::render('Auth/VerifyPasswordCode', [
            'email' => $email,
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $reset = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $reset || ! Hash::check($validated['code'], $reset->token)) {
            return back()
                ->withErrors(['code' => 'El código no es correcto.'])
                ->onlyInput('email');
        }

        if ($this->isCodeExpired($reset->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect()->route('password.request')->with(
                'status',
                'El código ha caducado. Pide uno nuevo.'
            );
        }

        $request->session()->put('password_reset_verified_email', $email);
        $request->session()->put('password_reset_verified_at', now()->toIso8601String());

        return redirect()->route('password.reset');
    }

    public function createReset(Request $request): Response|RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset_verified_email', '');
        $verifiedAt = $request->session()->get('password_reset_verified_at');

        if ($email === '' || ! $verifiedAt || $this->isCodeExpired($verifiedAt)) {
            $request->session()->forget([
                'password_reset_verified_email',
                'password_reset_verified_at',
            ]);

            return redirect()->route('password.request')->with(
                'status',
                'Primero tienes que validar el código.'
            );
        }

        return Inertia::render('Auth/ResetPassword', [
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $email = (string) $request->session()->get('password_reset_verified_email', '');
        $verifiedAt = $request->session()->get('password_reset_verified_at');

        if ($email === '' || ! $verifiedAt || $this->isCodeExpired($verifiedAt)) {
            $request->session()->forget([
                'password_reset_verified_email',
                'password_reset_verified_at',
            ]);

            return redirect()->route('password.request')->with(
                'status',
                'El paso de verificación ya no es válido. Pide un código nuevo.'
            );
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::query()->where('email', $email)->firstOrFail();
        $user->update([
            'password' => $validated['password'],
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget([
            'password_reset_verified_email',
            'password_reset_verified_at',
        ]);

        return redirect()->route('login')->with(
            'status',
            'Contraseña cambiada correctamente. Ya puedes entrar.'
        );
    }

    private function isCodeExpired(?string $createdAt): bool
    {
        if (! $createdAt) {
            return true;
        }

        return Carbon::parse($createdAt)->addMinutes(self::CODE_EXPIRATION_MINUTES)->isPast();
    }
}
