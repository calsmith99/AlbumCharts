<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Services\LastFmService;
use App\Services\LastFmDataService;
use Inertia\Inertia;

class LastFmController extends Controller
{
    private LastFmService $lastFmService;
    private LastFmDataService $dataService;

    public function __construct(LastFmService $lastFmService, LastFmDataService $dataService)
    {
        $this->lastFmService = $lastFmService;
        $this->dataService = $dataService;
    }

    public function connect()
    {
        return Inertia::render('LastFm/Connect');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        $username = $request->input('username');

        try {
            if (!$this->lastFmService->validateUsername($username)) {
                return back()->withErrors([
                    'username' => 'This Last.fm username does not exist or is not accessible.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Last.fm username validation failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'username' => 'Unable to validate Last.fm username. Please try again later.',
            ]);
        }

        $user = Auth::user();
        $user->update([
            'lastfm_username' => $username,
            'lastfm_connected_at' => now(),
        ]);

        // Skip automatic import to avoid rate limiting
        // User can manually import data later from the dashboard
        
        return redirect()->route('dashboard')
            ->with('success', 'Last.fm account connected successfully! You can now import your data from the dashboard.');
    }

    public function disconnect()
    {
        $user = Auth::user();
        $user->update([
            'lastfm_username' => null,
            'lastfm_connected_at' => null,
        ]);

        return back()->with('success', 'Last.fm account disconnected.');
    }

    public function import(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->lastfm_username) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect your Last.fm account first.');
        }

        try {
            Artisan::call('lastfm:import', [
                'username' => $user->lastfm_username,
                '--period' => '7day',
                '--limit' => 25
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Successfully imported your latest Last.fm data!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Failed to import Last.fm data: ' . $e->getMessage());
        }
    }
}
