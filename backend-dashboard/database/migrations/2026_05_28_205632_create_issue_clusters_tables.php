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
    Schema::create('issue_clusters', function (Blueprint $table) {
        $table->id();
        $table->foreignId('monitoring_project_id')->constrained()->onDelete('cascade');
        $table->string('cluster_name');
        $table->text('summary')->nullable();
        $table->integer('total_posts')->default(0);
        $table->timestamps();
    });

    Schema::create('cluster_posts', function (Blueprint $table) {
        $table->id();
        // Memasukkan post ke dalam cluster AI tertentu
        $table->foreignId('issue_cluster_id')->constrained()->onDelete('cascade');
        $table->foreignId('crawled_post_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('cluster_posts');
    Schema::dropIfExists('issue_clusters');
}
};
