<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAdvisory extends Model
{
    use HasFactory;

    protected $fillable = [
        'framework_name',
        'cve_id',
        'severity',
        'title',
        'description',
        'reference_url',
        'hash',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    //
}
