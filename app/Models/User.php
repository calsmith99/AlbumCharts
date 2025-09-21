<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lastfm_username',
        'lastfm_connected_at',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lastfm_connected_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the user's charts
     */
    public function charts()
    {
        return $this->hasMany(Chart::class);
    }

    /**
     * Get the user's latest chart (convenience relation)
     */
    public function latestChart()
    {
        // latestOfMany ensures we get the most recent related Chart per user
        return $this->hasOne(Chart::class)->latestOfMany('week_start_date');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->withTimestamps();
    }

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    /**
     * Get the user's listening sessions
     */
    // ...existing code...
}
