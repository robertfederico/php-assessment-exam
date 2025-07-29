<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    /**
     * Display a listing of tasks.
     */
    public function index(TaskFilterRequest $request): Response
    {
        $filters = [
            'search' => $request->validated('search'),
            'status' => $request->validated('status'),
            'is_published' => $request->validated('is_published'),
        ];

        $perPage = $request->validated('per_page', 10);
        $orderBy = $request->validated('order_by', 'created_at');
        $orderDirection = $request->validated('order_direction', 'desc');

        $tasks = $this->taskService->getAllForUser(
            array_filter($filters),
            $perPage,
            $orderBy,
            $orderDirection
        );

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'statusOptions' => TaskStatus::options(),
            'perPageOptions' => [10, 20, 50, 100],
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): Response
    {
        return Inertia::render('Tasks/Create', [
            'statusOptions' => TaskStatus::options(),
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $this->taskService->create($request->validated());

        return to_route('tasks.index')
            ->with('success', 'Task created successfully');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): Response
    {
        return Inertia::render('Tasks/Show', [
            'task' => TaskResource::make($task),
        ]);
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task): Response
    {
        return Inertia::render('Tasks/Edit', [
            'task' => TaskResource::make($task),
            'statusOptions' => TaskStatus::options(),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->update($task, $request->validated());

        return to_route('tasks.index')
            ->with('success', 'Task updated successfully');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->taskService->delete($task);

        return to_route('tasks.index')
            ->with('success', 'Task moved to trash successfully');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(TaskStatus::values())],
        ]);

        $this->taskService->updateStatus($task, TaskStatus::from($validated['status']));

        return back()->with('success', 'Task status updated successfully');
    }

    /**
     * Toggle task published status.
     */
    public function togglePublished(Task $task): RedirectResponse
    {
        $this->taskService->togglePublished($task);

        return back()->with('success', 'Task publication status updated successfully');
    }
}
