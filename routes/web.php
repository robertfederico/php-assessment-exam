<?php

use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureTaskOwnership;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Task Management Routes
    Route::prefix('/tasks')
        ->name('tasks.')
        ->controller(TaskController::class)
        ->middleware([EnsureTaskOwnership::class])
        ->group(function () {
            // Main CRUD routes
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{task}', 'show')->name('show');
            Route::get('/{task}/edit', 'edit')->name('edit');
            Route::put('/{task}', 'update')->name('update');
            Route::delete('/{task}', 'destroy')->name('destroy');
            // Task status and publishing routes
            Route::patch('/{task}/status', 'updateStatus')->name('update-status');
            Route::patch('/{task}/toggle-published', 'togglePublished')->name('toggle-published');
            // Subtask management routes
            Route::patch('/{task}/subtasks', 'updateSubtasks')->name('update-subtasks');
            Route::patch('/{task}/subtask/toggle', 'toggleSubtask')->name('toggle-subtask');
            // Trash management routes
            Route::get('/trash/list', 'trash')->name('trash');
            Route::patch('/{task}/restore', 'restore')->name('restore')->withTrashed();
            Route::delete('/{task}/force-delete', 'forceDelete')->name('force-delete')->withTrashed();
        });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
