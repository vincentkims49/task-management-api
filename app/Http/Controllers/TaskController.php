<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Get all tasks with filtering, pagination and search
     */
    public function index(Request $request)
    {
        try {
            $query = Task::query();

            // Apply filters
            if ($request->has('status')) {
                $validator = Validator::make($request->all(), [
                    'status' => 'in:pending,in_progress,completed'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Invalid status value',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $query->where('status', $request->status);
            }

            if ($request->has('due_date')) {
                $validator = Validator::make($request->all(), [
                    'due_date' => 'date'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Invalid date format',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $query->whereDate('due_date', $request->due_date);
            }

            // Apply search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortField, ['title', 'status', 'due_date', 'created_at'])) {
                $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $tasks = $query->paginate($perPage);

            return response()->json([
                'data' => $tasks->items(),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific task
     */
    public function show($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            return response()->json($task, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new task
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|unique:tasks|max:255',
                'description' => 'nullable|string',
                'status' => 'in:pending,in_progress,completed',
                'due_date' => 'required|date|after:today'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task = Task::create($request->all());

            return response()->json([
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing task
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'unique:tasks,title,' . $id . '|max:255',
                'description' => 'nullable|string',
                'status' => 'in:pending,in_progress,completed',
                'due_date' => 'date|after:today'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task->update($request->all());

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $task
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a task
     */
    public function destroy($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }

            $task->delete();

            return response()->json([
                'message' => 'Task deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}