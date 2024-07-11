<?php

namespace App\Models;

class Admin extends FirestoreModel
{
    protected $collection = 'Admins';

    protected $fillable = [
        'password',
    ];
}
