<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaceAuthTest extends TestCase
{
    use RefreshDatabase;

    private const SAMPLE_IMAGE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sZ8SQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_authenticated_user_can_store_face_reference(): void
    {
        $user = $this->createUserWithRole(UserRole::Player, 'Cara Test', 'cara-test@example.com', 'cara-token');

        $response = $this->actingAs($user)->post(route('face-security.store'), [
            'reference_image' => self::SAMPLE_IMAGE,
        ]);

        $response->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->face_reference_path);
        Storage::disk('local')->assertExists($user->face_reference_path);
    }

    public function test_register_creates_account_without_storing_face_reference(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nuevo Jugador',
            'email' => 'nuevo-jugador@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/catalogo');

        $user = User::query()->where('email', 'nuevo-jugador@example.com')->firstOrFail();

        $this->assertFalse($user->hasFaceReference());
    }

    public function test_face_login_authenticates_user_when_service_verifies_match(): void
    {
        Http::fake([
            'http://facial-service:8181/verify' => Http::response([
                'verified' => true,
                'distance' => 0.19,
                'threshold' => 0.4,
                'model' => 'Facenet512',
            ]),
        ]);

        $user = $this->createUserWithRole(UserRole::Player, 'Login Cara', 'login-cara@example.com', 'login-cara-token');

        $path = 'face-references/user-'.$user->id.'.png';
        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9sZ8SQAAAABJRU5ErkJggg==', true));
        $user->update(['face_reference_path' => $path]);

        $response = $this->postJson(route('login.face'), [
            'email' => $user->email,
            'capture_image' => self::SAMPLE_IMAGE,
            'remember' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('redirect', route('catalog.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_face_login_requires_existing_reference_when_account_has_no_face_id(): void
    {
        $user = $this->createUserWithRole(UserRole::Player, 'Sin Cara', 'sin-cara@example.com', 'sin-cara-token');

        $response = $this->postJson(route('login.face'), [
            'email' => $user->email,
            'capture_image' => self::SAMPLE_IMAGE,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['capture_image'])
            ->assertJsonPath(
                'errors.capture_image.0',
                'Este usuario no tiene Face ID configurado. Entra con contraseña y actívalo desde tu cuenta.'
            );

        $user->refresh();

        $this->assertGuest();
        $this->assertNull($user->face_reference_path);
    }

    private function createUserWithRole(
        UserRole $role,
        string $name,
        string $email,
        string $apiToken
    ): User {
        Role::query()->firstOrCreate([
            'name' => $role->value,
        ], [
            'label' => $role->label(),
        ]);

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'api_token' => $apiToken,
        ]);

        $user->syncRoles([$role]);

        return $user;
    }
}
