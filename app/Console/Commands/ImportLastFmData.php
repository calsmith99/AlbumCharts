<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LastFmDataService;
use Illuminate\Console\Command;

class ImportLastFmData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lastfm:import {username} {--period=7day} {--limit=25}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Last.fm data for a user and create charts';

    /**
     * Execute the console command.
     */
    public function handle(LastFmDataService $dataService)
    {
        $lastfmUsername = $this->argument('username');
        $period = $this->option('period');
        $limit = (int) $this->option('limit');

        $this->info("Importing Last.fm data for user: {$lastfmUsername}");

        // Find or create user with Last.fm username
        $user = User::where('lastfm_username', $lastfmUsername)->first();
        
        if (!$user) {
            $this->error("No user found with Last.fm username: {$lastfmUsername}");
            $this->info("Please create a user first and set their lastfm_username field.");
            return Command::FAILURE;
        }

        try {
            $chart = $dataService->importUserTopAlbums($user, $period, $limit);
            
            $this->info("✅ Successfully imported {$period} chart with {$limit} albums");
            $this->info("Chart ID: {$chart->id}");
            $this->info("Week start: {$chart->week_start_date}");
            
            $entryCount = $chart->chartEntries()->count();
            $this->info("Total entries created: {$entryCount}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to import Last.fm data: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
