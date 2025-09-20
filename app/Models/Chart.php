<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chart extends Model
{
    protected $fillable = [
        'user_id',
        'week_start_date',
        'chart_type',
        'chart_size',
    ];

    protected $casts = [
        'week_start_date' => 'date',
    ];

    /**
     * Get the user that owns the chart
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chart's entries
     */
    public function chartEntries()
    {
        return $this->hasMany(ChartEntry::class);
    }
}
