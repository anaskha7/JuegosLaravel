<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reset_password_after_validating_code(): void
    {
        $user = $this->createUser('recovery@example.com');

        $response = $this->post('/recuperar-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.verify', ['email' => $user->email]))
            ->assertSessionHas('reset_code_hint');

        $codeHint = (string) session('reset_code_hint');
        preg_match('/(\d{6})/', $codeHint, $matches);
        $code = $matches[1] ?? null;

        $this->assertNotNull($code);

        $this->post('/recuperar-password/codigo', [
            'email' => $user->email,
            'code' => $code,
        ])->assertRedirect(route('password.reset'));

        $this->post('/recuperar-password/nueva', [
            'password' => 'nueva-password',
            'password_confirmation' => 'nueva-password',
        ])->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue(Hash::check('nueva-password', $user->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_invalid_code_does_not_unlock_password_change(): void
    {
        $user = $this->createUser('invalid-code@example.com');

        $this->post('/recuperar-password', [
            'email' => $user->email,
        ]);

        $response = $this->post('/recuperar-password/codigo', [
            'email' => $user->email,
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull(session('password_reset_verified_email'));

        $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($tokenRow);
    }

    private function createUser(string $email): User
    {
        Role::query()->firstOrCreate([
            'name' => UserRole::Player->value,
        ], [
            'label' => UserRole::Player->label(),
        ]);

        $user = User::query()->create([
            'name' => 'Recovery User',
            'email' => $email,
            'password' => 'password',
            'api_token' => bin2hex(random_bytes(24)),
        ]);

        $user->syncRoles([UserRole::Player]);

        return $user;
    }
}
