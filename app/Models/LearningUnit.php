<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningUnit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'topic',
        'sortId',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'topic' => 'string',
        'sortId' => 'integer',
    ];

    /**
     * Get the levels for the unit.
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class, 'unitId');
    }

    public static function findBy($attribute, $value)
    {
        return static::where($attribute, $value)->first();
    }
}
