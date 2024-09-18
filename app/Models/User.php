<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    use HasFactory;

    protected $primaryKey = 'phoneNumber';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phoneNumber',
        'name',
        'occupation',
        'menuLocation',
        'progress',
        'progressPercentage',
        'currentGrade',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'phoneNumber' => 'string',
        'name' => 'string',
        'occupation' => 'string',
        'menuLocation' => 'string',
        'progress' => 'string',
        'progressPercentage' => 'integer',
        'currentGrade' => 'string',
    ];

    /**
     * Get the userGrades for the user.
     */
    public function userGrades(): HasMany
    {
        return $this->hasMany(UserGrade::class, 'user_phoneNumber');
    }
}
