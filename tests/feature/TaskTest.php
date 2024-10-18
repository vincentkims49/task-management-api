<?php

namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;

class TaskTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_can_create_task()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ];

        $this->post('/api/tasks', $taskData)
             ->seeStatusCode(201)
             ->seeJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'created_at',
                    'updated_at'
                ]
             ]);
    }

    public function test_cannot_create_task_with_invalid_data()
    {
        $taskData = [
            'title' => '',
            'due_date' => Carbon::yesterday()->format('Y-m-d')
        ];

        $this->post('/api/tasks', $taskData)
             ->seeStatusCode(422)
             ->seeJsonStructure([
                'message',
                'errors' => [
                    'title',
                    'due_date'
                ]
             ]);
    }

    public function test_can_get_all_tasks()
    {
        // Create some test tasks
        $this->post('/api/tasks', [
            'title' => 'Task 1',
            'description' => 'Description 1',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->post('/api/tasks', [
            'title' => 'Task 2',
            'description' => 'Description 2',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->get('/api/tasks')
             ->seeStatusCode(200)
             ->seeJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
             ]);
    }

    public function test_can_filter_tasks_by_status()
    {
        // Create tasks with different statuses
        $this->post('/api/tasks', [
            'title' => 'Pending Task',
            'status' => 'pending',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->post('/api/tasks', [
            'title' => 'Completed Task',
            'status' => 'completed',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->get('/api/tasks?status=pending')
             ->seeStatusCode(200)
             ->seeJson(['status' => 'pending'])
             ->dontSeeJson(['status' => 'completed']);
    }

    public function test_can_search_tasks()
    {
        $this->post('/api/tasks', [
            'title' => 'Documentation Task',
            'description' => 'Write documentation',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->post('/api/tasks', [
            'title' => 'Development Task',
            'description' => 'Write code',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $this->get('/api/tasks?search=documentation')
             ->seeStatusCode(200)
             ->seeJson(['title' => 'Documentation Task'])
             ->dontSeeJson(['title' => 'Development Task']);
    }

    public function test_can_update_task()
    {
        // Create a task
        $response = $this->post('/api/tasks', [
            'title' => 'Original Title',
            'description' => 'Original Description',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);
        
        $taskId = json_decode($response->response->getContent())->data->id;

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ];

        $this->put("/api/tasks/{$taskId}", $updateData)
             ->seeStatusCode(200)
             ->seeJson([
                'title' => 'Updated Title',
                'description' => 'Updated Description'
             ]);
    }

    public function test_can_delete_task()
    {
        // Create a task
        $response = $this->post('/api/tasks', [
            'title' => 'Task to Delete',
            'description' => 'This task will be deleted',
            'due_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);
        
        $taskId = json_decode($response->response->getContent())->data->id;

        $this->delete("/api/tasks/{$taskId}")
             ->seeStatusCode(200);

        // Verify task is deleted
        $this->get("/api/tasks/{$taskId}")
             ->seeStatusCode(404);
    }
}