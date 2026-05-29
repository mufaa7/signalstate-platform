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
    Schema::create('scraping_logs', function (Blueprint $table) {
        $table->id();
        $table->string('platform'); // Twitter, TikTok, News, etc
        $table->string('status'); // success, failed
        $table->integer('total_data')->default(0);
        $table->text('error_message')->nullable();
        $table->timestamp('scraped_at')->useCurrent();
    });

    Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
        $table->string('activity');
        $table->string('ip_address', 45)->nullable();
        $table->timestamps(); // created_at ada di sini
    });
}

public function down(): void
{
    Schema::dropIfExists('activity_logs');
    Schema::dropIfExists('scraping_logs');
}
};
