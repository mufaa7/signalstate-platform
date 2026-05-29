<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Fungsi untuk membuat dan mengunduh PDF secara instan
    public function download()
    {
        // 1. Ambal statistik agregat dasar
        $totalPosts = DB::table('crawled_posts')->count();
        $sentiments = DB::table('sentiments')
            ->select('sentiment', DB::raw('count(*) as total'))
            ->groupBy('sentiment')
            ->pluck('total', 'sentiment');

        // 2. Ambil data klaster isu terpopuler
        $clusters = DB::table('issue_clusters')
            ->where('monitoring_project_id', 1)
            ->orderBy('total_posts', 'desc')
            ->get();

        // 3. Ambil 5 berita dengan tingkat toksisitas tertinggi (Isu Kritis)
        $criticalIssues = DB::table('crawled_posts')
            ->join('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
            ->select('crawled_posts.content', 'sentiments.toxicity_score', 'crawled_posts.posted_at')
            ->where('sentiments.toxicity_score', '>', 0.3)
            ->orderBy('sentiments.toxicity_score', 'desc')
            ->take(5)
            ->get();

        $data = [
            'date' => date('Y-m-d H:i:s'),
            'total_posts' => $totalPosts,
            'positive font' => $sentiments['positive'] ?? 0,
            'negative' => $sentiments['negative'] ?? 0,
            'neutral' => $sentiments['neutral'] ?? 0,
            'clusters' => $clusters,
            'criticals' => $criticalIssues
        ];

        // 4. Compile data ke template HTML khusus PDF (Kita buat templatenya di langkah berikutnya)
        $pdf = Pdf::loadView('pdf.intelligence_report', $data);
        
        // Catat riwayat pembuatan laporan ke tabel reports sesuai migration lu
        DB::table('reports')->insert([
            'monitoring_project_id' => 1,
            'title' => 'National Intelligence Report - ' . date('Y-m-d'),
            'summary' => 'Ringkasan otomatis sentimen dan klasterisasi taktis.',
            'pdf_file_path' => 'generated_in_fly',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $pdf->download('SignalState-Intelligence-Report.pdf');
    }

    // Fungsi untuk menarik daftar alert aktif ke front-end
    public function getAlerts()
    {
        $alerts = DB::table('alerts')
            ->where('monitoring_project_id', 1)
            ->orderBy('triggered_at', 'desc')
            ->take(10)
            ->get();

        return response()->json($alerts);
    }
}