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

    // Task Routes
    Route::prefix('/tasks')
        ->name('tasks.')
        ->controller(TaskController::class)
        ->middleware([EnsureTaskOwnership::class])
        ->group(function () {
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
        });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
