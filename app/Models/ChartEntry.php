<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartEntry extends Model
{
    protected $fillable = [
        'chart_id',
        'album_id',
        'position',
        'play_count',
        'format',
        'completed_album',
        'streak_count',
    ];

    protected $casts = [
        'completed_album' => 'boolean',
    ];

    /**
     * Get the chart that owns the entry
     */
    public function chart()
    {
        return $this->belongsTo(Chart::class);
    }

    /**
     * Get the album for this entry
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
