<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LastFmController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $user = auth()->user();

    $chart = null;
    // Only show the most recent chart to guests (not logged in users)
    if (!$user) {
        $chart = \App\Models\Chart::with(['chartEntries.album.artist'])
            ->latest()
            ->first();
    }

    return Inertia::render('Welcome', [
        'auth' => ['user' => $user],
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'chart' => $chart,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $charts = \App\Models\Chart::whereHas('user', function ($query) use ($user) {
        $query->where('lastfm_username', $user->lastfm_username);
    })
        ->withCount('chartEntries')
        ->with(['chartEntries' => function ($query) {
            $query->with('album.artist')->orderBy('position');
        }])
        ->latest()
        ->take(6)
        ->get();

    return Inertia::render('Dashboard', [
        'auth' => ['user' => $user],
        'charts' => $charts,
        'debug' => [
            'user_id' => $user->id,
            'charts_count' => $charts->count(),
            'user_lastfm' => $user->lastfm_username,
        ],
        'flash' => [
            'success' => session('success'),
            'error' => session('error'),
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Groups
    Route::post('/groups', [\App\Http\Controllers\GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups', [\App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/{group}', [\App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{group}/grid', [\App\Http\Controllers\GroupController::class, 'grid'])->name('groups.grid');

    // Invite links
    Route::post('/groups/{group}/invites', [\App\Http\Controllers\GroupInviteController::class, 'create'])->name('groups.invites.create');
    Route::get('/invites/{token}/accept', [\App\Http\Controllers\GroupInviteController::class, 'accept'])->name('groups.invites.accept');

    // Album Timeline page
    Route::get('/album-timeline', function () {
        return Inertia::render('AlbumTimeline');
    })->name('album-timeline');
    // API: Paginated full album listens
    Route::get('/api/full-album-listens', [\App\Http\Controllers\AlbumTimelineController::class, 'paginatedFullListens'])
        ->name('api.fullAlbumListens');
    // Track listens for album
    Route::get('/albums/{album}/track-listens', [\App\Http\Controllers\TrackListenController::class, 'recentForAlbum'])
        ->name('albums.trackListens');
    // Chart routes
    Route::get('/charts/{id}', [\App\Http\Controllers\ChartController::class, 'show'])->name('charts.show');
    Route::delete('/charts/{id}', [\App\Http\Controllers\ChartController::class, 'destroy'])->name('charts.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
    
    // Last.fm routes
    Route::get('/lastfm/connect', [LastFmController::class, 'connect'])
        ->name('lastfm.connect');
    Route::post('/lastfm/store', [LastFmController::class, 'store'])
        ->name('lastfm.store');
    Route::post('/lastfm/disconnect', [LastFmController::class, 'disconnect'])
        ->name('lastfm.disconnect');
    Route::post('/lastfm/import', [LastFmController::class, 'import'])
        ->name('lastfm.import');
});

require __DIR__.'/auth.php';

// Dev/debug route: view members' latest charts and the aggregated chart for group 1
if (app()->environment('local')) {
    Route::get('/debug/group-charts/{groupId}', function ($groupId) {
        $group = \App\Models\Group::with('users')->findOrFail($groupId);
    // ensure we load real User models by id (avoid pivot/id confusion)
    $userIds = $group->users()->pluck('id')->toArray();
        $members = \App\Models\User::whereIn('id', $userIds)->with(['latestChart.chartEntries.album.artist'])->get();

        // reuse the GroupController grid logic by resolving the controller
        $controller = app(\App\Http\Controllers\GroupController::class);
        $gridResp = $controller->grid($group);
        $gridPayload = $gridResp->getData(true);

        return Inertia::render('Debug/GroupCharts', [
            'group' => $group,
            'members' => $members,
            'aggregated' => $gridPayload['aggregated_chart'] ?? null,
            'grid' => $gridPayload['grid'] ?? null,
        ]);
    });
}
