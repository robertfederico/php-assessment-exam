<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTaskOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $task = $request->route('task');

        // If no task in route, continue (for routes like index, create)
        if (! $task) {
            return $next($request);
        }

        // Check if the authenticated user owns the task
        if ($task->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to access this task.');
        }

        return $next($request);
    }
}
