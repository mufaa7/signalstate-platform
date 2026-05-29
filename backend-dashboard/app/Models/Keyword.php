<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keyword extends Model
{
    protected $fillable = ['monitoring_project_id', 'keyword', 'type'];

    public function monitoringProject(): BelongsTo
    {
        return $this->belongsTo(MonitoringProject::class);
    }
}