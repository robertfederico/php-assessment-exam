<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTrashedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cleanup-trash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete tasks that have been trashed for more than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = now()->subDays(30);

        $tasks = Task::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->get();

        foreach ($tasks as $task) {
            if ($task->image_path) {
                Storage::disk('public')->delete($task->image_path);
            }
            // Permanently delete task
            $task->forceDelete();
        }

        $this->info("Cleaned up {$tasks->count()} trashed tasks.");
    }
}
