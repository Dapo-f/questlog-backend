<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryEntry extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'rawg_id',
        'hours_played',
        'started_at',
        'completed_at',
        'status',
    ];

     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
