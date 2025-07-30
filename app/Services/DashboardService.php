<?php

namespace App\Services;

use App\Enums\TaskStatus;

class DashboardService
{
    /**
     * Get dashboard statistics for a user.
     */
    public function getStats(): array
    {
        $tasks = auth()->user()->tasks();

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->byStatus(TaskStatus::DONE)->count(),
            'in_progress_tasks' => $tasks->byStatus(TaskStatus::IN_PROGRESS)->count(),
            'pending_tasks' => $tasks->byStatus(TaskStatus::TODO)->count(),
        ];
    }

    /**
     * Get recent tasks for a user.
     */
    public function getRecentTasks(int $limit = 5): array
    {
        return auth()->user()->tasks()
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get task completion rate for the current month.
     */
    public function getCompletionRate(): float
    {
        $totalThisMonth = auth()->user()->tasks()
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($totalThisMonth === 0) {
            return 0;
        }

        $completedThisMonth = auth()->user()->tasks()
            ->byStatus(TaskStatus::DONE)
            ->whereMonth('created_at', now()->month)
            ->count();

        return round(($completedThisMonth / $totalThisMonth) * 100, 1);
    }
}
