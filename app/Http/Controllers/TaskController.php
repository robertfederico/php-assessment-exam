<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): Response
    {
        // create filter validation
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'is_published' => $request->get('is_published'),
        ];

        $perPage = min($request->get('per_page', 10), 100);
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');

        $tasks = $this->taskService->getAllForUser(
            auth()->id(),
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
        $this->taskService->create(auth()->id(), $request->validated());

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
            'status' => 'required|in:'.implode(',', TaskStatus::values()),
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

    /**
     * Update task subtasks.
     */
    public function updateSubtasks(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'subtasks' => 'required|array',
            'subtasks.*.id' => 'sometimes|string',
            'subtasks.*.title' => 'required|string|max:255',
            'subtasks.*.completed' => 'sometimes|boolean',
        ]);

        $this->taskService->updateSubtasks($task, $validated['subtasks']);

        return back()->with('success', 'Subtasks updated successfully');
    }

    /**
     * Toggle subtask completion status.
     */
    public function toggleSubtask(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'subtask_id' => 'required|string',
        ]);

        $this->taskService->toggleSubtask($task, $validated['subtask_id']);

        return back()->with('success', 'Subtask status updated successfully');
    }

    /**
     * Display trashed tasks.
     */
    public function trash(Request $request): Response
    {
        $perPage = min($request->get('per_page', 10), 100);

        $tasks = $this->taskService->getTrashedForUser(auth()->id(), $perPage);

        return Inertia::render('Tasks/Trash', [
            'tasks' => $tasks,
            'perPageOptions' => [10, 20, 50, 100],
        ]);
    }

    /**
     * Restore a trashed task.
     */
    public function restore(Task $task): RedirectResponse
    {
        $restoredTask = $this->taskService->restore($task->id, auth()->id());

        if (! $restoredTask) {
            return back()->with('error', 'Task not found in trash');
        }

        return back()->with('success', 'Task restored successfully');
    }

    /**
     * Permanently delete a task.
     */
    public function forceDelete(Task $task): RedirectResponse
    {
        $deleted = $this->taskService->forceDelete($task->id, auth()->id());

        if (! $deleted) {
            return back()->with('error', 'Task not found in trash');
        }

        return back()->with('success', 'Task permanently deleted');
    }
}
