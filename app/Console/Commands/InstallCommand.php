<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing Task Management Application...');
        $this->newLine();

        // Generate application key
        $this->info('🔑 Generating application key...');
        Artisan::call('key:generate');
        $this->info('✓ Application key generated');

        // Run migrations
        $this->info('🗄️ Running database migrations and seeders...');
        Artisan::call('migrate:fresh --seed');
        $this->info('✓ Database migrations and seeders completed');

        // Create storage link
        $this->info('🔗 Creating storage symbolic link...');
        Artisan::call('storage:link');
        $this->info('✓ Storage link created');

        // Clear cache
        $this->info('🧹 Clearing application cache...');
        Artisan::call('optimize:clear');
        $this->info('✓ Cache cleared');

        $this->newLine();
        $this->info('🎉 Installation completed successfully!');

        return self::SUCCESS;
    }
}
