<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountVerification extends Model
{
    protected $table = 'account_verification';

     /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'code',
        'expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    // shows the relationship between AccountVerification and User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
