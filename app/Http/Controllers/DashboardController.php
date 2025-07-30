<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    /**
     * Display dashboard.
     */
    public function index(): Response
    {
        $stats = $this->dashboardService->getStats();
        $recentTasks = $this->dashboardService->getRecentTasks();
        $completionRate = $this->dashboardService->getCompletionRate();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_tasks' => $recentTasks,
            'completion_rate' => $completionRate,
        ]);
    }
}
