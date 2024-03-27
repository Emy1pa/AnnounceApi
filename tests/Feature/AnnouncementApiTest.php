<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class AnnouncementApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // This will disable middleware, including 'web'
    }
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_can_create_user()
    {
        // create a new user
        // $user = User::factory()->create();
        // login the created user
        // $this->actingAs($user, 'api');
        $userData = [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'organizer'
        ];
        //$response = $this->postJson('/api/register', $userData);
        $response = $this->json('POST', route('api.register'), $userData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User created successfully'
            ]);

        // Assert that the user was actually created in the database
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ]);

        // Assert that the password was hashed
        $this->assertTrue(Hash::check($userData['password'], User::where('email', $userData['email'])->first()->password));
    }

    public function test_can_login_user()
{
    // Create a user without specifying the email_verified_at column
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'), // Ensure the password is hashed
    ]);

    // Make a request to the login endpoint
    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    // Assert that the response has a successful status code
    $response->assertStatus(200);

    // Assert that the response contains the expected JSON structure
    $response->assertJson([
        'status' => true,
        'message' => 'User logged in successfully',
    ]);

    // Assert that the response contains the token key
    $response->assertJsonStructure(['token']);
}

public function test_can_logout_user()
{
    // Create a user
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'), // Ensure the password is hashed
    ]);

    // Authenticate the user
    $token = $user->createToken('test_token')->plainTextToken;

    // Make a request to the logout endpoint with the authentication token
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/logout'); // Use GET request instead of POST

    // Assert that the response has a successful status code
    $response->assertStatus(200);

    // Assert that the response contains the expected JSON structure
    $response->assertJson([
        'status' => true,
        'message' => 'user logged out successfully'
    ]);
}



}