<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Album;
use App\Models\Chart;
use App\Models\ChartEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LastFmDataService
{
    private LastFmService $lastFmService;

    public function __construct(LastFmService $lastFmService)
    {
        $this->lastFmService = $lastFmService;
    }

    /**
     * Import user's top albums and create a chart for a specific week
     */
    public function importUserTopAlbums(User $user, string $period = '7day', int $limit = 25): Chart
    {
        $topAlbumsData = $this->lastFmService->getUserTopAlbums(
            $user->lastfm_username, 
            $period, 
            $limit
        );
        // ...existing code...

        \Log::info('Importing chart', [
            'user_id' => $user->id,
            'week_start_date' => $this->getWeekStartDate($period)->toDateString(),
            'period' => $period,
            'limit' => $limit,
        ]);
        DB::beginTransaction();
        
        try {
            // Create chart for this week
            $weekStart = $this->getWeekStartDate($period);
            $chart = Chart::updateOrCreate([
                'user_id' => $user->id,
                'week_start_date' => $weekStart,
                'chart_type' => $period,
            ], [
                'chart_size' => $limit,
            ]);
            \Log::info('Chart created or updated', [
                'chart_id' => $chart->id,
                'user_id' => $user->id,
                'week_start_date' => $weekStart->toDateString(),
                'chart_type' => $period,
                'chart_size' => $limit,
            ]);

            // Clear existing entries for this chart
            $chart->chartEntries()->delete();

            // Process albums
            if (isset($topAlbumsData['topalbums']['album'])) {
                $albums = $topAlbumsData['topalbums']['album'];
                $position = 1;

                // Get timestamps for the past week
                $now = Carbon::now();
                $weekAgo = $now->copy()->subWeek()->timestamp;
                $nowTs = $now->timestamp;

                // Fetch all recent tracks for the user in the past week (cap at 1000)
                $recentTracks = $this->lastFmService->getUserRecentTracks($user->lastfm_username, 1000, $weekAgo, $nowTs);
                $recentTrackList = $recentTracks['recenttracks']['track'] ?? [];

                \Log::info('Importing albums', [
                    'album_count' => count($albums),
                    'albums' => array_map(fn($a) => $a['name'], $albums),
                ]);

                foreach ($albums as $albumData) {
                    try {
                        $artist = $this->createOrUpdateArtist($albumData['artist']);
                        $album = $this->createOrUpdateAlbum($albumData, $artist);

                        // Calculate streak (check previous week's chart)
                        $streakCount = $this->calculateStreak($user, $album, $weekStart, $position);

                        // --- FULL ALBUM LISTEN LOGIC ---
                        $albumInfo = $this->lastFmService->getAlbumInfo($artist->name, $album->name, $user->lastfm_username);
                        $playCount = (int) $albumData['playcount'];

                        $fullAlbumSessions = [];
                        $earliestTrackListen = null;
                        if (isset($albumInfo['album']['tracks']) && isset($albumInfo['album']['tracks']['track'])) {
                            $trackCount = count($albumInfo['album']['tracks']['track']);
                            // Group track listens by session (e.g., by day)
                            $trackListensByDay = [];
                            foreach ($recentTrackList as $track) {
                                if (!isset($track['date']['uts'])) continue;
                                if (
                                    isset($track['artist']['#text']) && strtolower($track['artist']['#text']) === strtolower($artist->name) &&
                                    isset($track['album']['#text']) && strtolower($track['album']['#text']) === strtolower($album->name)
                                ) {
                                    $trackName = $track['name'];
                                    $listenedAt = Carbon::createFromTimestamp($track['date']['uts']);
                                    if (!$earliestTrackListen || $listenedAt->lt($earliestTrackListen)) {
                                        $earliestTrackListen = $listenedAt;
                                    }
                                    $dayKey = $listenedAt->format('Y-m-d');
                                    if (!isset($trackListensByDay[$dayKey])) $trackListensByDay[$dayKey] = [];
                                    $trackListensByDay[$dayKey][$trackName] = $listenedAt;
                                    \App\Models\TrackListen::updateOrCreate(
                                        [
                                            'user_id' => $user->id,
                                            'album_id' => $album->id,
                                            'artist_id' => $artist->id,
                                            'track_name' => $trackName,
                                            'listened_at' => $listenedAt,
                                        ],
                                        [
                                            'source' => 'lastfm',
                                        ]
                                    );
                                }
                            }
                            // For each day, check if all tracks were played
                            foreach ($trackListensByDay as $day => $trackPlays) {
                                $allTracksPlayed = true;
                                foreach ($albumInfo['album']['tracks']['track'] as $track) {
                                    if (!isset($trackPlays[$track['name']])) {
                                        $allTracksPlayed = false;
                                        break;
                                    }
                                }
                                if ($allTracksPlayed && $trackCount > 0) {
                                    $fullAlbumSessions[] = [
                                        'date' => $day,
                                        'first_listen' => min($trackPlays),
                                    ];
                                }
                            }
                        }

                        // Only add one ChartEntry per album per chart (top listen)
                        $topSession = null;
                        if (count($fullAlbumSessions) > 0) {
                            // Use the earliest full listen in the week
                            usort($fullAlbumSessions, function($a, $b) {
                                return strtotime($a['first_listen']) - strtotime($b['first_listen']);
                            });
                            $topSession = $fullAlbumSessions[0];
                        }
                        \Log::info('Chart entry created', [
                            'chart_id' => $chart->id,
                            'album_id' => $album->id,
                            'album_name' => $album->name,
                            'position' => $position,
                            'play_count' => $playCount,
                            'completed_album' => !!$topSession,
                            'streak_count' => $streakCount,
                            'created_at' => $earliestTrackListen ? $earliestTrackListen : ($topSession ? $topSession['first_listen'] : $now),
                        ]);
                        ChartEntry::forceCreate([
                            'chart_id' => $chart->id,
                            'album_id' => $album->id,
                            'position' => $position,
                            'play_count' => $playCount,
                            'format' => 'streaming',
                            'completed_album' => !!$topSession,
                            'streak_count' => $streakCount,
                            'created_at' => $earliestTrackListen ? $earliestTrackListen : ($topSession ? $topSession['first_listen'] : $now),
                        ]);

                        $position++;
                    } catch (\Throwable $e) {
                        \Log::error('Error importing album', [
                            'album_data' => $albumData,
                            'position' => $position,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }

            \Log::info('Import completed', [
                'chart_id' => $chart->id,
                'user_id' => $user->id,
                'week_start_date' => $weekStart->toDateString(),
                'chart_type' => $period,
                'chart_size' => $limit,
            ]);
            DB::commit();
            return $chart;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create or update artist from Last.fm data
     */
    private function createOrUpdateArtist(array $artistData): Artist
    {
        $artistName = is_array($artistData) ? $artistData['name'] : $artistData;
        
        // Get additional artist info from Last.fm
        try {
            $artistInfo = $this->lastFmService->getArtistInfo($artistName);
            $artistDetails = $artistInfo['artist'] ?? [];
        } catch (\Exception $e) {
            $artistDetails = ['name' => $artistName];
        }

        return Artist::updateOrCreate(
            ['name' => $artistName],
            [
                'lastfm_id' => !empty($artistDetails['mbid']) ? $artistDetails['mbid'] : null,
                'image_url' => $this->extractImageUrl($artistDetails['image'] ?? []),
                'metadata' => json_encode($artistDetails),
            ]
        );
    }

    /**
     * Create or update album from Last.fm data
     */
    private function createOrUpdateAlbum(array $albumData, Artist $artist): Album
    {
        $albumName = $albumData['name'];
        
        // Get additional album info from Last.fm
        try {
            $albumInfo = $this->lastFmService->getAlbumInfo($artist->name, $albumName);
            $albumDetails = $albumInfo['album'] ?? [];
        } catch (\Exception $e) {
            $albumDetails = ['name' => $albumName];
        }

        return Album::updateOrCreate(
            [
                'artist_id' => $artist->id,
                'name' => $albumName,
            ],
            [
                'lastfm_id' => !empty($albumDetails['mbid']) ? $albumDetails['mbid'] : null,
                'release_date' => isset($albumDetails['wiki']['published']) 
                    ? Carbon::parse($albumDetails['wiki']['published'])->format('Y-m-d')
                    : null,
                'image_url' => $this->extractImageUrl($albumData['image'] ?? []),
                'metadata' => json_encode($albumDetails),
            ]
        );
    }

    /**
     * Calculate streak count for an album
     */
    private function calculateStreak(User $user, Album $album, Carbon $currentWeekStart, int $position): int
    {
        $streak = 1;
        $checkDate = $currentWeekStart->copy()->subWeek();

        while (true) {
            $previousChart = Chart::where('user_id', $user->id)
                ->where('week_start_date', $checkDate)
                ->first();

            if (!$previousChart) {
                break;
            }

            $previousEntry = ChartEntry::where('chart_id', $previousChart->id)
                ->where('album_id', $album->id)
                ->first();

            if (!$previousEntry) {
                break;
            }

            $streak++;
            $checkDate->subWeek();
        }

        return $streak;
    }

    /**
     * Extract the largest image URL from Last.fm image array
     */
    private function extractImageUrl(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }

        // Look for largest size first, then fallback to any available
        $sizePreference = ['extralarge', 'large', 'medium', 'small'];
        
        foreach ($sizePreference as $size) {
            foreach ($images as $image) {
                if (isset($image['size']) && $image['size'] === $size && !empty($image['#text'])) {
                    return $image['#text'];
                }
            }
        }

        // Fallback to first available image
        return $images[0]['#text'] ?? null;
    }

    /**
     * Get the start date of the week for chart period
     */
    private function getWeekStartDate(string $period): Carbon
    {
        $now = Carbon::now();
        
        return match($period) {
            '7day' => $now->startOfWeek(),
            '1month' => $now->startOfMonth(),
            '3month' => $now->startOfQuarter(),
            '6month' => $now->copy()->subMonths(6)->startOfMonth(),
            '12month' => $now->startOfYear(),
            default => $now->startOfWeek(),
        };
    }
}