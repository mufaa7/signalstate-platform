<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawled_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_project_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // Twitter/X, TikTok, YouTube, News Portal
            $table->string('external_post_id'); // ID asli kiriman dari platform terkait
            $table->string('username');
            $table->string('display_name')->nullable();
            $table->text('content');
            $table->text('post_url')->nullable();
            $table->timestamp('posted_at');
            
            // Kolom Metrik Keterlibatan (Engagement)
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('share_count')->default(0);

            // Kolom Hasil Olahan Mesin NLP Python
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->float('confidence_score')->default(0.0);
            $table->float('toxicity_score')->default(0.0);
            $table->enum('emotion', ['angry', 'happy', 'sad', 'fear', 'neutral'])->default('neutral');

            $table->timestamps();

            // Indexing agar pencarian topik dan rendering grafik chart realtime tidak lemot
            $table->index(['monitoring_project_id', 'sentiment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawled_posts');
    }
};