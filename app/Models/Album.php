<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    /**
     * Get the track listens for this album
     */
    public function trackListens()
    {
        return $this->hasMany(\App\Models\TrackListen::class);
    }
    protected $fillable = [
        'lastfm_id',
        'artist_id',
        'name',
        'release_date',
        'image_url',
        'metadata',
    ];

    protected $casts = [
        'release_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the album's artist
     */
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * Get the album's chart entries
     */
    public function chartEntries()
    {
        return $this->hasMany(ChartEntry::class);
    }

    /**
     * Get the album's listening sessions
     */
    // ...existing code...
}
