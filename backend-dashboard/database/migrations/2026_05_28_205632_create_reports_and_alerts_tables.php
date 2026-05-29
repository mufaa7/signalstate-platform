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
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('monitoring_project_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('summary')->nullable();
        $table->string('pdf_file_path');
        $table->timestamps(); // generated_at diwakili oleh created_at
    });

    Schema::create('alerts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('monitoring_project_id')->constrained()->onDelete('cascade');
        $table->enum('alert_type', ['sentiment_spike', 'keyword_spike', 'toxicity_spike']);
        $table->text('message');
        $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
        $table->timestamp('triggered_at')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('alerts');
    Schema::dropIfExists('reports');
}
};
