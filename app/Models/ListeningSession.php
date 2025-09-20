<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListeningSession extends Model
{
    protected $fillable = [
        'user_id',
        'album_id',
        'listened_at',
        'completed',
        'format',
        'track_count_played',
        'total_tracks',
    ];

    protected $casts = [
        'listened_at' => 'datetime',
        'completed' => 'boolean',
    ];

    /**
     * Get the user that owns the listening session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the album for this listening session
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
