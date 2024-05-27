<?php

namespace App\Models;

class User extends FirestoreModel
{
    protected $collection = 'Users';

    protected $fillable = [
        'nama',
        'pekerjaan',
        'progress',
        'lokasiMenu',
    ];
}
