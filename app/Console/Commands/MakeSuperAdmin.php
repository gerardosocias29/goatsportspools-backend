<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-superadmin {email? : The email of the user to promote}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a user a superadmin (role_id = 1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Enter the user email');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $this->info("User found: {$user->name} ({$user->email})");
        $this->info("Current role_id: {$user->role_id}");

        if ($user->role_id == 1) {
            $this->warn('This user is already a superadmin.');
            return 0;
        }

        if (!$this->confirm("Are you sure you want to make '{$user->name}' a superadmin?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $user->role_id = 1;
        $user->save();

        $this->info("Success! '{$user->name}' is now a superadmin (role_id = 1).");

        return 0;
    }
}
