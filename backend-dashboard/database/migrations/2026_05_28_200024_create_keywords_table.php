<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel monitoring_projects di atas
            $table->foreignId('monitoring_project_id')->constrained()->onDelete('cascade');
            $table->string('keyword');
            $table->enum('type', ['hashtag', 'mention', 'normal'])->default('normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};