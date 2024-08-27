<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Level extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'topic',
        'content',
        'videoLink',
        'sortId',
        'unitId',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'topic' => 'string',
        'content' => 'string',
        'videoLink' => 'string',
        'sortId' => 'integer',
        'unitId' => 'integer',
    ];

    /**
     * Get the unit that owns the levels.
     */
    public function learningUnit(): BelongsTo
    {
        return $this->belongsTo(LearningUnit::class, 'unitId');
    }

    /**
     * Get the questions for the level.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'levelId');
    }

    /**
     * The users that belong to the level.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_grade')->as('score');
    }

    public static function findBy($attribute, $value)
    {
        return static::where($attribute, $value)->first();
    }
}
