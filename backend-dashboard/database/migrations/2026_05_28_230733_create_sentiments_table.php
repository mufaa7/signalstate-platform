<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('sentiments', function (Blueprint $table) {
        $table->id();
        // Menghubungkan langsung ke tabel induk crawled_posts
        $table->foreignId('post_id')->constrained('crawled_posts')->onDelete('cascade');
        $table->string('sentiment'); // positive, neutral, negative
        $table->decimal('confidence_score', 3, 2)->default(0.50); // Contoh: 0.94
        $table->decimal('toxicity_score', 3, 2)->default(0.00);   // Contoh: 0.15
        $table->string('emotion')->default('neutral');            // happy, angry, sad, fear
        $table->timestamp('analyzed_at')->useCurrent();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentiments');
    }
};
