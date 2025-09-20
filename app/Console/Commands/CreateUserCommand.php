<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {name} {email} {lastfm_username} {--password=password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user with Last.fm username';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $lastfmUsername = $this->argument('lastfm_username');
        $password = $this->option('password');

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'lastfm_username' => $lastfmUsername,
                'password' => bcrypt($password),
            ]);

            $this->info("✅ User created successfully!");
            $this->info("ID: {$user->id}");
            $this->info("Name: {$user->name}");
            $this->info("Email: {$user->email}");
            $this->info("Last.fm Username: {$user->lastfm_username}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to create user: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
