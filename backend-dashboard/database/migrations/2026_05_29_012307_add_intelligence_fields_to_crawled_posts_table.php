<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crawled_posts', function (Blueprint $col) {
            $col->string('issue_category')->nullable()->after('content');
            $col->text('matched_keywords')->nullable()->after('issue_category');
            $col->string('priority_level')->default('low')->after('matched_keywords');
            $col->integer('match_score')->default(0)->after('priority_level');
        });
    }

    public function down(): void
    {
        Schema::table('crawled_posts', function (Blueprint $col) {
            $col->dropColumn(['issue_category', 'matched_keywords', 'priority_level', 'match_score']);
        });
    }
};