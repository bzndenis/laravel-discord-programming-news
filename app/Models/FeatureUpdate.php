<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureUpdate extends Model
{
    protected $fillable = [
        'source_name',
        'version',
        'title',
        'description',
        'url',
        'hash',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
    //
}
