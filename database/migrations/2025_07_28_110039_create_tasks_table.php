<?php

use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->index()
                ->constrained()
                ->onDelete('cascade');
            $table->string('title', 100);
            $table->text('content');
            $table->enum('status', TaskStatus::values())
                ->default(TaskStatus::TODO->value)
                ->comment(TaskStatus::class);
            $table->boolean('is_published')->default(true);
            $table->string('image_path')->nullable();
            $table->json('subtasks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
