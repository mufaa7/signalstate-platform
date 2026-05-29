<!DOCTYPE html>
<html>
<head>
    <title>SignalState Intelligence Report</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #111827; }
        .subtitle { color: #6b7280; margin-top: 5px; }
        .section-title { font-size: 16px; font-weight: bold; color: #10b981; margin-top: 30px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        th { background-color: #f9fafb; font-weight: bold; }
        .critical-badge { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SIGNALSTATE // INTELLIGENCE SYSTEM REPORT</div>
        <div class="subtitle">Generated Executed at: {{ $date }}</div>
    </div>

    <div class="section-title">1. RINGKASAN VOLUMETRIK DATA</div>
    <table>
        <tr><th>Metrik Analisis</th><th>Jumlah Terdeteksi</th></tr>
        <tr><td>Total Dokumentasi Berita</td><td><strong>{{ $total_posts }} Berita</strong></td></tr>
        <tr><td>Sentimen Positif (Support)</td><td style="color: #10b981;">{{ $positive }}</td></tr>
        <tr><td>Sentimen Negatif (Kritis)</td><td style="color: #ef4444;">{{ $negative }}</td></tr>
        <tr><td>Sentimen Netral</td><td style="color: #3b82f6;">{{ $neutral }}</td></tr>
    </table>

    <div class="section-title">2. DISTRIBUSI KLASTER ISU UTAMA</div>
    <table>
        <tr><th>Nama Klaster Isu</th><th>Volume Data</th><th>Ringkasan Inti Al</th></tr>
        @foreach($clusters as $cluster)
        <tr>
            <td><strong>{{ $cluster->cluster_name }}</strong></td>
            <td>{{ $cluster->total_posts }} Berita</td>
            <td><em>"{{ $cluster->summary }}"</em></td>
        </tr>
        @endforeach
    </table>

    <div class="section-title">3. ANOMALI RADIKAL / DAFTAR ISU TOKSISITAS TINGGI</div>
    <table>
        <tr><th>Konten Publikasi</th><th>Skor Bahaya</th><th>Tanggal Rilis</th></tr>
        @foreach($criticals as $crit)
        <tr>
            <td>{{ Str::limit($crit->content, 120) }}</td>
            <td class="critical-badge">{{ htmlspecialchars(round($crit->toxicity_score * 100)) }}%</td>
            <td>{{ $crit->posted_at }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>