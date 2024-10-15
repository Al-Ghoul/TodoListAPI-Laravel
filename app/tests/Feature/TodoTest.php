<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;
    protected $accessToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Register a user for authentication tests
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        // Log in to get an access token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);

        // Store the access token for authorization
        $this->accessToken = $loginResponse['data']['access_token'];
    }

    /**
     * Get all todos.
     */
    public function test_get_all_todos_success(): void
    {
        // Create a couple of todos
        $this->postJson('/api/todos', [
            'title' => 'First Todo',
            'description' => 'Description for first todo',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $this->postJson('/api/todos', [
            'title' => 'Second Todo',
            'description' => 'Description for second todo',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        // Fetch all todos
        $response = $this->getJson('/api/todos?page=1&limit=15', [
            'Authorization' => "Bearer {$this->accessToken}"
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /**
     * Create a new todo.
     */
    public function test_create_new_todo_success(): void
    {
        $response = $this->postJson('/api/todos', [
            'title' => 'New Todo',
            'description' => 'Description for new todo',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'New Todo',
                    'description' => 'Description for new todo',
                ],
            ]);
    }

    /**
     * Create a new todo without required fields.
     */
    public function test_create_new_todo_fail(): void
    {
        $response = $this->postJson('/api/todos', [], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(422);
    }

    /**
     * Update an existing todo.
     */
    public function test_update_todo_success(): void
    {
        // Create a todo first
        $todoResponse = $this->postJson('/api/todos', [
            'title' => 'Old Todo',
            'description' => 'Old description',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $todoId = $todoResponse['data']['id'];

        // Update the todo
        $response = $this->patchJson("/api/todos/{$todoId}", [
            'title' => 'Updated Todo',
            'description' => 'Updated description',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Todo',
                    'description' => 'Updated description',
                ],
            ]);
    }

    /**
     * Update a todo that doesn't exist.
     */
    public function test_update_todo_fail(): void
    {
        $response = $this->patchJson('/api/todos/999', [
            'title' => 'Nonexistent Todo',
            'description' => 'Nonexistent description',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Record not found',
            ]);
    }

    /**
     * Delete an existing todo.
     */
    public function test_delete_todo_success(): void
    {
        // Create a todo first
        $todoResponse = $this->postJson('/api/todos', [
            'title' => 'Todo to Delete',
            'description' => 'Description for deletion',
        ], ['Authorization' => "Bearer {$this->accessToken}"]);

        $todoId = $todoResponse['data']['id'];

        // Delete the todo
        $response = $this->deleteJson("/api/todos/{$todoId}", [], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Todo deleted successfully!',
            ]);
    }


    /*
       * Delete a todo that doesn't exist.
       */
    public function test_delete_todo_fail(): void
    {
        $response = $this->deleteJson('/api/todos/999', [], ['Authorization' => "Bearer {$this->accessToken}"]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Record not found',
            ]);
    }
}
