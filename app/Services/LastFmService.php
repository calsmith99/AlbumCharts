<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LastFmService
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;
    private array $rateLimit;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('lastfm.api_key');
        $this->baseUrl = config('lastfm.base_url');
        $this->rateLimit = config('lastfm.rate_limit');
    }

    /**
     * Get user's top albums for a specific time period
     */
    public function getUserTopAlbums(string $username, string $period = '7day', int $limit = 50): array
    {
        $cacheKey = "lastfm:top_albums:{$username}:{$period}:{$limit}";
        
        return Cache::remember($cacheKey, config('lastfm.cache.top_albums'), function () use ($username, $period, $limit) {
            return $this->makeRequest('user.gettopalbums', [
                'user' => $username,
                'period' => $period,
                'limit' => $limit,
            ]);
        });
    }

    /**
     * Get detailed album information
     */
    public function getAlbumInfo(string $artist, string $album, string $username = null): array
    {
        $cacheKey = "lastfm:album_info:" . md5($artist . $album . ($username ?? ''));
        
        return Cache::remember($cacheKey, config('lastfm.cache.album_info'), function () use ($artist, $album, $username) {
            $params = [
                'artist' => $artist,
                'album' => $album,
            ];
            
            if ($username) {
                $params['username'] = $username;
            }
            
            return $this->makeRequest('album.getinfo', $params);
        });
    }

    /**
     * Get artist information
     */
    public function getArtistInfo(string $artist): array
    {
        $cacheKey = "lastfm:artist_info:" . md5($artist);
        
        return Cache::remember($cacheKey, config('lastfm.cache.artist_info'), function () use ($artist) {
            return $this->makeRequest('artist.getinfo', [
                'artist' => $artist,
            ]);
        });
    }

    /**
     * Get user information
     */
    public function getUserInfo(string $username): array
    {
        $cacheKey = "lastfm:user_info:{$username}";
        
        return Cache::remember($cacheKey, config('lastfm.cache.user_info'), function () use ($username) {
            return $this->makeRequest('user.getinfo', [
                'user' => $username,
            ]);
        });
    }

    /**
     * Get user's recent tracks
     */
    public function getUserRecentTracks(string $username, int $limit = 200, int $from = null, int $to = null): array
    {
        $params = [
            'user' => $username,
            'limit' => $limit,
        ];
        
        if ($from) {
            $params['from'] = $from;
        }
        
        if ($to) {
            $params['to'] = $to;
        }
        
        return $this->makeRequest('user.getrecenttracks', $params);
    }

    /**
     * Make a request to the Last.fm API
     */
    private function makeRequest(string $method, array $params = []): array
    {
        try {
            $params = array_merge($params, [
                'method' => $method,
                'api_key' => $this->apiKey,
                'format' => 'json',
            ]);

            $response = $this->client->get($this->baseUrl, [
                'query' => $params,
                'timeout' => 30,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                throw new \Exception("Last.fm API error: " . $data['message']);
            }

            return $data;

        } catch (RequestException $e) {
            Log::error('Last.fm API request failed', [
                'method' => $method,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);
            
            throw new \Exception('Failed to fetch data from Last.fm: ' . $e->getMessage());
        }
    }

    /**
     * Validate if a Last.fm username exists
     */
    public function validateUsername(string $username): bool
    {
        try {
            $userInfo = $this->getUserInfo($username);
            return isset($userInfo['user']);
        } catch (\Exception $e) {
            return false;
        }
    }
}