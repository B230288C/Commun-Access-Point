<?php

namespace Tests\Feature;

use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test staff member
        $this->staff = Staff::create([
            'name' => 'Test Staff',
            'email' => 'staff@example.com',
            'phone' => '1234567890',
            'department' => 'IT',
            'position' => 'Developer',
            'password' => Hash::make('password123'),
        ]);
    }

    #[Test]
    public function can_login_with_valid_credentials()
    {
        $response = $this->postJson('/login', [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertAuthenticatedAs($this->staff);
    }

    #[Test]
    public function cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    #[Test]
    public function cannot_login_with_invalid_password()
    {
        $response = $this->postJson('/login', [
            'email' => 'staff@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    #[Test]
    public function cannot_login_without_email()
    {
        $response = $this->postJson('/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function cannot_login_without_password()
    {
        $response = $this->postJson('/login', [
            'email' => 'staff@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function can_logout()
    {
        $this->actingAs($this->staff);

        $response = $this->postJson('/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        $this->assertGuest();
    }

    #[Test]
    public function can_get_authenticated_user()
    {
        $this->actingAs($this->staff);

        $response = $this->getJson('/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
            ])
            ->assertJson([
                'user' => [
                    'id' => $this->staff->id,
                    'name' => $this->staff->name,
                    'email' => $this->staff->email,
                ],
            ]);
    }

    #[Test]
    public function cannot_get_user_when_not_authenticated()
    {
        $response = $this->getJson('/user');

        $response->assertStatus(401);
    }

    #[Test]
    public function cannot_logout_when_not_authenticated()
    {
        $response = $this->postJson('/logout');

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_routes_are_protected()
    {
        $response = $this->getJson('/api/appointments');

        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_users_can_access_protected_routes()
    {
        $this->actingAs($this->staff);

        $response = $this->getJson('/api/availability-frames');

        $response->assertStatus(200);
    }
}
