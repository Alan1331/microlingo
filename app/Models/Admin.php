<?php

namespace App\Models;

class Admin extends FirestoreModel
{
    use \Illuminate\Auth\Authenticatable;

    protected $collection = 'Admins';

    protected $fillable = [
        'gid',
        'name',
    ];
}