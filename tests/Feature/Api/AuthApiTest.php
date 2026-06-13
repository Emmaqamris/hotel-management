<?php
namespace Tests\Feature\Api;
use App\Models\Employee;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AuthApiTest extends TestCase
{
    use RefreshDatabase;
    private Hotel    $hotel;
    private Employee $employee;
    protected function setUp(): void
    {
        parent::setUp();
        $this->hotel = Hotel::create([
            'name' => 'Test Hotel', 'address' => '1 Test St',
            'city' => 'Mbeya', 'country' => 'TZ',
            'phone' => '+255700000000', 'email' => 'test@hotel.com',
        ]);
        $this->employee = Employee::create([
            'hotel_id' => $this->hotel->id,
            'name'     => 'Jane',
            'email'    => 'jane@test.com',
            'password' => bcrypt('password'),
            'role'     => 'receptionist',
            'is_active'=> true,
        ]);
    }
    public function test_login_returns_token(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'jane@test.com',
            'password' => 'password',
        ]);
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'token_type', 'employee'],
            ]);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));
        $this->assertNotEmpty($response->json('data.token'));
    }
    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'jane@test.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(422);
        $this->assertFalse((bool) ($response->json('success') ?? false));
    }
    public function test_login_fails_for_inactive_employee(): void
    {
        $this->employee->update(['is_active' => false]);
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'jane@test.com',
            'password' => 'password',
        ]);
        $response->assertStatus(422);
    }
    public function test_me_returns_current_user(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/auth/me');
        $response->assertOk()
            ->assertJsonPath('data.email', 'jane@test.com')
            ->assertJsonPath('data.role', 'receptionist');
    }
    public function test_logout_revokes_token(): void
{
    $token = $this->employee->createToken('test-device');

    $plainTextToken = $token->plainTextToken;

    $this->withToken($plainTextToken)
        ->postJson('/api/auth/logout')
        ->assertOk();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
}
    public function test_protected_endpoints_require_token(): void
    {
        $this->getJson('/api/rooms')->assertStatus(401);
        $this->getJson('/api/bookings')->assertStatus(401);
        $this->getJson('/api/dashboard')->assertStatus(401);
    }
}
