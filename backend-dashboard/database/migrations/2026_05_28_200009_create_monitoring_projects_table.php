<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_projects', function (Blueprint $table) {
            $table->id();
            // Menghubungkan project ke user yang membuat
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, paused, archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_projects');
    }
};