<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'score',
        'user_phoneNumber',
        'level_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
        'user_phoneNumber' => 'string',
        'level_id' => 'integer',
    ];

    /**
     * Get the user that owns the user_grades.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_phoneNumber');
    }

    /**
     * Get the level that owns the user_grades.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
}
