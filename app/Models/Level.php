<?php

namespace App\Models;

class Level extends FirestoreModel
{
    protected $parentCollection = 'LearningUnits';
    protected $parentId;
    protected $collection = 'Levels';
    protected $fillable = [
        'topic',
        'videos',
    ];
    protected $casts = [
        'videos' => 'array',
    ];

    public function __construct($parentId)
    {
        parent::__construct();
        $this->parentId = $parentId;
        $this->collection = $this->parentCollection . '/' . $parentId . '/' . $this->collection;
    }
}
