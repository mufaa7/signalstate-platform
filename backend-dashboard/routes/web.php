<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

// 1. HALAMAN UTAMA (LANDING PAGE)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 2. PROTECTED CORE WEB & API ROUTES
Route::middleware(['auth', 'verified'])->group(function () {
    
    // DASHBOARD UTAMA - REALTIME & HISTORIS INTEGRASI
    Route::get('/dashboard', function (Request $request) {
        $search = $request->input('search');

        // A. Query Core Feed & Search Filtering Logic
        $postsQuery = DB::table('crawled_posts')
            ->leftJoin('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
            ->select(
                'crawled_posts.*',
                'sentiments.sentiment',
                'sentiments.confidence_score',
                'sentiments.toxicity_score',
                'sentiments.emotion'
            );

        if ($search) {
            $postsQuery->where(function ($query) use ($search) {
                $query->where('crawled_posts.content', 'LIKE', '%' . $search . '%')
                      ->orWhere('crawled_posts.username', 'LIKE', '%' . $search . '%');
            });
        }

        // Ambil 20 postingan terbaru untuk ditampilkan di feed dashboard bawah
        $posts = $postsQuery->orderBy('crawled_posts.created_at', 'desc')
            ->take(20)
            ->get();

        // B. Hitung Total Volume Riil Isi Seluruh Tabel Database (Solusi Counter Macet)
        $totalVol = DB::table('crawled_posts')->count();

        // C. Query Dynamic Issue Clusters
        $clusters = DB::table('issue_clusters')
            ->where('monitoring_project_id', 1)
            ->orderBy('total_posts', 'desc')
            ->get();

        // D. Realtime Agregasi Sentimen untuk Chart Polarity Ratio (Donut)
        $distributionRaw = DB::table('sentiments')
            ->select('sentiment', DB::raw('count(*) as total'))
            ->groupBy('sentiment')
            ->get()
            ->pluck('total', 'sentiment');

        $pieData = [
            (int)($distributionRaw['positive'] ?? 0),
            (int)($distributionRaw['negative'] ?? 0),
            (int)($distributionRaw['neutral'] ?? 0)
        ];

        // E. Realtime Agregasi Wilayah untuk Regional Density Map
        $mapData = [
            'DKI Jakarta' => DB::table('crawled_posts')->where('content', 'LIKE', '%jakarta%')->count(),
            'Jawa Barat'  => DB::table('crawled_posts')->where('content', 'LIKE', '%bandung%')->orWhere('content', 'LIKE', '%jabar%')->count(),
            'Jawa Tengah' => DB::table('crawled_posts')->where('content', 'LIKE', '%semarang%')->orWhere('content', 'LIKE', '%jateng%')->count(),
            'Jawa Timur'  => DB::table('crawled_posts')->where('content', 'LIKE', '%surabaya%')->orWhere('content', 'LIKE', '%jatim%')->count(),
            'Sumatera'    => DB::table('crawled_posts')->where('content', 'LIKE', '%sumatera%')->orWhere('content', 'LIKE', '%medan%')->count(),
        ];

        // F. Agregasi Tren Historis Berbasis Waktu Seminggu Terakhir (Line Chart)
        $timelineData = [
            'categories' => [],
            'positive' => [],
            'negative' => [],
            'neutral' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $formattedDate = date('d M', strtotime($date));
            
            $timelineData['categories'][] = $formattedDate;

            $timelineData['positive'][] = DB::table('crawled_posts')
                ->join('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
                ->whereRaw("DATE(crawled_posts.created_at) = ?", [$date])
                ->where('sentiments.sentiment', 'positive')
                ->count();

            $timelineData['negative'][] = DB::table('crawled_posts')
                ->join('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
                ->whereRaw("DATE(crawled_posts.created_at) = ?", [$date])
                ->where('sentiments.sentiment', 'negative')
                ->count();

            $timelineData['neutral'][] = DB::table('crawled_posts')
                ->join('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
                ->whereRaw("DATE(crawled_posts.created_at) = ?", [$date])
                ->where('sentiments.sentiment', 'neutral')
                ->count();
        }

        return Inertia::render('Dashboard', [
            'posts' => $posts,
            'clusters' => $clusters,
            'totalVolumeReal' => $totalVol, // Lempar jumlah asli ke properti Vue
            'chartData' => [
                'pie' => $pieData,
                'map' => $mapData,
                'timeline' => $timelineData
            ],
            'filters' => [
                'search' => $search
            ]
        ]);
    })->name('dashboard');

    // HALAMAN KHUSUS MONITORING FEED (DENGAN FILTER TAB PLATFORM)
    Route::get('/monitoring', function (Request $request) {
        $platform = $request->input('platform');

        $query = DB::table('crawled_posts')
            ->leftJoin('sentiments', 'crawled_posts.id', '=', 'sentiments.post_id')
            ->select('crawled_posts.*', 'sentiments.sentiment', 'sentiments.confidence_score', 'sentiments.toxicity_score', 'sentiments.emotion');

        if ($platform) {
            $query->where('crawled_posts.platform', $platform);
        }

        $posts = $query->orderBy('crawled_posts.created_at', 'desc')->get();

        return Inertia::render('Monitoring', [
            'posts' => $posts,
            'currentPlatform' => $platform
        ]);
    })->name('monitoring');

    // HALAMAN LOG PERINGATAN ANOMALI RADIKAL (ALERTS)
    Route::get('/alerts', function () {
        $alerts = DB::table('alerts')
            ->where('monitoring_project_id', 1)
            ->orderBy('triggered_at', 'desc')
            ->get();

        return Inertia::render('Alerts', [
            'alerts' => $alerts
        ]);
    })->name('alerts');

    // MANAGEMENT PROFIL USER
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API ENDPOINT REPORTING PDF & REALTIME TOPBAR NOTIFICATION
    Route::get('/api/export-pdf', [ReportController::class, 'download'])->name('report.pdf');
    Route::get('/api/live-alerts', [ReportController::class, 'getAlerts']);
});

require __DIR__.'/auth.php';