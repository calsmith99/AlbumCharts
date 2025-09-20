<?php

return [
    'api_key' => env('LASTFM_API_KEY'),
    'secret' => env('LASTFM_SECRET'),
    'base_url' => 'https://ws.audioscrobbler.com/2.0/',
    
    // Rate limiting (Last.fm allows 5 calls per second per API key)
    'rate_limit' => [
        'max_requests' => 5,
        'per_seconds' => 1,
    ],
    
    // Cache settings for API responses
    'cache' => [
        'user_info' => 3600, // 1 hour
        'top_albums' => 1800, // 30 minutes
        'album_info' => 86400, // 24 hours
        'artist_info' => 86400, // 24 hours
    ],
];