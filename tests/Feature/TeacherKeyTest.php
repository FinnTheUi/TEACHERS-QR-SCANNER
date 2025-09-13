<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TeacherKey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeacherKeyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_their_keys(): void
    {
        $this->actingAs($this->user);
        
        TeacherKey::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->get('/teacher-keys');
        
        $response->assertStatus(200)
                ->assertViewIs('teacher-keys.index')
                ->assertViewHas('keys');
    }

    public function test_user_can_create_key(): void
    {
        $this->actingAs($this->user);

        $keyData = [
            'key_code' => 'TEST-' . uniqid(),
            'description' => 'Test Key',
            'is_active' => true,
            'expires_at' => now()->addDays(30)->format('Y-m-d')
        ];

        $response = $this->post('/teacher-keys', $keyData);
        
        $response->assertStatus(201)
                ->assertJson(['message' => 'Key created successfully']);

        $this->assertDatabaseHas('teacher_keys', [
            'user_id' => $this->user->id,
            'description' => 'Test Key'
        ]);
    }

    public function test_user_can_update_their_key(): void
    {
        $this->actingAs($this->user);
        
        $key = TeacherKey::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'description' => 'Updated Description',
            'is_active' => false
        ];

        $response = $this->put("/teacher-keys/{$key->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJson(['message' => 'Key updated successfully']);

        $this->assertDatabaseHas('teacher_keys', [
            'id' => $key->id,
            'description' => 'Updated Description',
            'is_active' => false
        ]);
    }

    public function test_user_cannot_update_others_key(): void
    {
        $this->actingAs($this->user);
        
        $otherUser = User::factory()->create();
        $key = TeacherKey::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->put("/teacher-keys/{$key->id}", [
            'description' => 'Updated Description'
        ]);
        
        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_key(): void
    {
        $this->actingAs($this->user);
        
        $key = TeacherKey::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->delete("/teacher-keys/{$key->id}");
        
        $response->assertStatus(200)
                ->assertJson(['message' => 'Key deleted successfully']);

        $this->assertSoftDeleted('teacher_keys', [
            'id' => $key->id
        ]);
    }
}
