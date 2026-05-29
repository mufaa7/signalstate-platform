<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $jsonContent = $request->json()->all();

        if (empty($jsonContent)) {
            return response()->json(['status' => 'error', 'message' => 'Payload kosong'], 400);
        }

        $posts = isset($jsonContent[0]) ? $jsonContent : [$jsonContent];
        
        // 1. AMANKAN USER ID & PROJECT ID (MENGGUNAKAN EMAIL BARU LU)
        $firstUser = DB::table('users')->where('email', 'mufaa@signalstate.com')->first();
        $userId = $firstUser ? $firstUser->id : DB::table('users')->insertGetId([
            'name' => 'Mufaa Admin',
            'email' => 'mufaa@signalstate.com',
            'password' => bcrypt('jokowi123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $projectExists = DB::table('monitoring_projects')->where('id', 1)->exists();
        if (!$projectExists) {
            DB::table('monitoring_projects')->insert([
                'id' => 1,
                'user_id' => $userId,
                'project_name' => 'Default News Monitoring',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $successCount = 0;
        $errors = [];

        // 2. PROSES DISTRIBUSI DATA MULTI-TABEL SINKRON
        foreach ($posts as $index => $post) {
            DB::beginTransaction();
            try {
                $clusterName = $post['cluster_name'] ?? 'Umum / Ragam Isu';
                $summary = $post['summary'] ?? 'Ringkasan otomatis belum digenerate.';

                // SINKRONISASI 1: Cari atau buat cluster baru
                $cluster = DB::table('issue_clusters')
                    ->where('monitoring_project_id', 1)
                    ->where('cluster_name', $clusterName)
                    ->first();

                if ($cluster) {
                    $clusterId = $cluster->id;
                    DB::table('issue_clusters')->where('id', $clusterId)->increment('total_posts');
                } else {
                    $clusterId = DB::table('issue_clusters')->insertGetId([
                        'monitoring_project_id' => 1,
                        'cluster_name'          => $clusterName,
                        'summary'               => $summary,
                        'total_posts'           => 1,
                        'created_at'            => now(),
                        'updated_at'            => now()
                    ]);
                }

                // Cek duplikasi data berita berdasarkan external_post_id
                $existingPost = DB::table('crawled_posts')
                    ->where('external_post_id', $post['external_post_id'])
                    ->first();

                $toxicityScore = floatval($post['toxicity_score'] ?? 0);

                if ($existingPost) {
                    $postId = $existingPost->id;
                    
                    // UPDATE DATA LAMA + SINKRONISASI KOLOM INTELIJEN BARU LU
                    DB::table('crawled_posts')->where('id', $postId)->update([
                        'content'          => $post['content'] ?? '',
                        'issue_category'   => $post['issue_category'] ?? null,
                        'matched_keywords' => $post['matched_keywords'] ?? null,
                        'priority_level'   => $post['priority_level'] ?? 'low',
                        'match_score'      => $post['match_score'] ?? 0,
                        'updated_at'       => now(),
                    ]);

                    DB::table('sentiments')->updateOrInsert(
                        ['post_id' => $postId],
                        [
                            'sentiment'        => $post['sentiment'] ?? 'neutral',
                            'confidence_score' => $post['confidence_score'] ?? 0.50,
                            'toxicity_score'   => $post['toxicity_score'] ?? 0.00,
                            'emotion'          => $post['emotion'] ?? 'neutral',
                            'analyzed_at'      => now()
                        ]
                    );

                    // SUNTIKAN ALERT UNTUK UPDATE DATA LAMA
                    if ($toxicityScore > 0.3) {
                        DB::table('alerts')->insert([
                            'monitoring_project_id' => 1,
                            'alert_type' => 'toxicity_spike',
                            'message' => '[' . strtoupper($post['priority_level'] ?? 'LOW') . '] Pembaruan narasi kritis: "' . Str::limit($post['content'] ?? '', 60) . '"',
                            'severity' => $toxicityScore > 0.6 ? 'high' : 'medium',
                            'triggered_at' => now()
                        ]);
                    }

                } else {
                    // INSERT DATA BARU BERIKUT 4 PARAMETER ADVANCED DARI PIPELINE SCRAPER LU
                    $postId = DB::table('crawled_posts')->insertGetId([
                        'monitoring_project_id' => 1,
                        'platform'              => $post['platform'] ?? 'News Portal',
                        'external_post_id'      => $post['external_post_id'] ?? uniqid('news_'),
                        'username'              => $post['username'] ?? 'Anonymous',
                        'display_name'          => $post['display_name'] ?? 'Media Online',
                        'content'               => $post['content'] ?? '',
                        'issue_category'        => $post['issue_category'] ?? null,
                        'matched_keywords'      => $post['matched_keywords'] ?? null,
                        'priority_level'        => $post['priority_level'] ?? 'low',
                        'match_score'           => $post['match_score'] ?? 0,
                        'post_url'              => $post['post_url'] ?? null,
                        'posted_at'             => isset($post['posted_at']) ? date('Y-m-d H:i:s', strtotime($post['posted_at'])) : now(),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ]);

                    DB::table('sentiments')->insert([
                        'post_id'          => $postId,
                        'sentiment'        => $post['sentiment'] ?? 'neutral',
                        'confidence_score' => $post['confidence_score'] ?? 0.50,
                        'toxicity_score'   => $post['toxicity_score'] ?? 0.00,
                        'emotion'          => $post['emotion'] ?? 'neutral',
                        'analyzed_at'      => now()
                    ]);

                    // SUNTIKAN ALERT UNTUK DATA BARU DATANG
                    if ($toxicityScore > 0.3) {
                        DB::table('alerts')->insert([
                            'monitoring_project_id' => 1,
                            'alert_type' => 'toxicity_spike',
                            'message' => '[' . strtoupper($post['priority_level'] ?? 'LOW') . '] Sistem mendeteksi narasi radikal pada berita: "' . Str::limit($post['content'] ?? '', 60) . '"',
                            'severity' => $toxicityScore > 0.6 ? 'high' : 'medium',
                            'triggered_at' => now()
                        ]);
                    }

                    // SINKRONISASI 2: Isi tabel pivot 'cluster_posts'
                    DB::table('cluster_posts')->insert([
                        'issue_cluster_id' => $clusterId,
                        'crawled_post_id'  => $postId,
                        'created_at'       => now(),
                        'updated_at'       => now()
                    ]);
                }

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Indeks {$index}: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => $successCount > 0 ? 'success' : 'error',
            'inserted_or_updated' => $successCount,
            'errors' => $errors
        ], $successCount > 0 ? 201 : 500);
    }
}