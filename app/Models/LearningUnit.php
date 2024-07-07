<?php

namespace App\Models;

class LearningUnit extends FirestoreModel
{
    protected $collection = 'LearningUnits';

    protected $fillable = [
        'topic',
    ];

    protected $subcollection = 'Levels';
}
