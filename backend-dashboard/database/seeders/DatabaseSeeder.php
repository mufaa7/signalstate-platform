<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan sisa data testing lama secara berurutan (mengikuti foreign key)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alerts')->truncate();
        DB::table('reports')->truncate();
        DB::table('cluster_posts')->truncate();
        DB::table('sentiments')->truncate();
        DB::table('crawled_posts')->truncate();
        DB::table('issue_clusters')->truncate();
        DB::table('monitoring_projects')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Suntik User Admin Default
        $userId = DB::table('users')->insertGetId([
            'name' => 'Mufaa ',
            'email' => 'mufaa@signalstate.com',
            'password' => Hash::make('jokowi123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Suntik Project ID: 1 (Sesuai pengunci sistem di Controller & Route)
        DB::table('monitoring_projects')->insert([
            'id' => 1,
            'user_id' => $userId,
            'project_name' => 'National News Surveillance',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}