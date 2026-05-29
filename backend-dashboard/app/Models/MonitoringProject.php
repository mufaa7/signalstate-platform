<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringProject extends Model
{
    protected $fillable = ['id', 'user_id', 'project_name', 'description', 'status'];

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function crawledPosts(): HasMany
    {
        return $table->hasMany(CrawledPost::class);
    }
}