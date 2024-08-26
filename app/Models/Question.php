<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'answer',
        'type',
        'optionA',
        'optionB',
        'optionC',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'question' => 'string',
        'answer' => 'string',
        'type' => 'string',
        'optionA' => 'string',
        'optionB' => 'string',
        'optionC' => 'string',
    ];

    /**
     * Get the unit that owns the levels.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
