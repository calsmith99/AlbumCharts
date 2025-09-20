<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'lastfm_id',
        'name',
        'image_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the artist's albums
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
