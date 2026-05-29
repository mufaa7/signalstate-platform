<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawledPost extends Model
{
    // Kosongkan guarded agar data JSON dari Python bebas masuk tanpa di-block mass assignment
    protected $guarded = [];

    public function monitoringProject(): BelongsTo
    {
        return $this->belongsTo(MonitoringProject::class);
    }
}