<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Topbar from "@/Components/Topbar.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
    MessageSquare,
    ShieldAlert,
    TrendingUp,
    Globe2,
} from "lucide-vue-next";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    posts: { type: Array, default: () => [] },
    clusters: { type: Array, default: () => [] },
    totalVolumeReal: { type: Number, default: 0 }, // Mengamankan tangkapan jumlah total sejati dari MySQL
    chartData: {
        type: Object,
        default: () => ({
            pie: [0, 0, 0],
            map: {},
            timeline: {
                categories: [],
                positive: [],
                negative: [],
                neutral: [],
            },
        }),
    },
    filters: { type: Object, default: () => ({ search: "" }) },
});

// LOGIKA INPUT PENCARIAN GLOBAL
const searchQuery = ref(props.filters.search || "");

watch(searchQuery, (newValue) => {
    router.get(
        "/dashboard",
        { search: newValue },
        {
            preserveState: true,
            preserveScroll: true,
            only: ["posts", "chartData", "totalVolumeReal"],
        },
    );
});

// AUTO POLLING DATA INTEGRATION (Menarik pembaruan volume dan chart dari MySQL setiap 5 detik)
let refreshInterval = null;
onMounted(() => {
    refreshInterval = setInterval(() => {
        if (!searchQuery.value) {
            router.reload({
                only: ["posts", "clusters", "chartData", "totalVolumeReal"],
                preserveScroll: true,
            });
        }
    }, 5000);
});
onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

// KALKULASI STATISTIK INTEGRASI RIIL DATABASE
const totalBerita = computed(() => props.totalVolumeReal); // Mengambil langsung jumlah riil dari database melewati batasan limit feed

const stats = computed(() => {
    let positive = 0;
    let negative = 0;
    let neutral = 0;
    let highToxic = 0;
    props.posts.forEach((post) => {
        if (post.sentiment === "positive") positive++;
        else if (post.sentiment === "negative") negative++;
        else neutral++;
        if (parseFloat(post.toxicity_score) > 0.3) highToxic++;
    });
    return { positive, negative, neutral, highToxic };
});

// SINKRONISASI TREN HISTORIS 7 HARI DARI DATABASE
const sentimentSeries = computed(() => [
    {
        name: "Positive",
        data: props.chartData.timeline?.positive || [0, 0, 0, 0, 0, 0, 0],
    },
    {
        name: "Negative",
        data: props.chartData.timeline?.negative || [0, 0, 0, 0, 0, 0, 0],
    },
    {
        name: "Neutral",
        data: props.chartData.timeline?.neutral || [0, 0, 0, 0, 0, 0, 0],
    },
]);

const sentimentChart = computed(() => ({
    chart: { toolbar: { show: false }, background: "transparent" },
    theme: { mode: "dark" },
    stroke: { curve: "smooth", width: 3 },
    grid: { borderColor: "rgba(255,255,255,0.05)" },
    xaxis: {
        categories: props.chartData.timeline?.categories || [
            "Mon",
            "Tue",
            "Wed",
            "Thu",
            "Fri",
            "Sat",
            "Sun",
        ],
        labels: { style: { colors: "#6b7280" } },
    },
    yaxis: { labels: { style: { colors: "#6b7280" } } },
    colors: ["#34d399", "#f87171", "#60a5fa"],
    legend: { labels: { colors: "#9ca3af" } },
}));

// DONUT CHART SEKARANG MEMBACA DATA RIIL DARI DATABASE
const pieSeries = computed(() => props.chartData.pie);
const pieChart = {
    labels: ["Positive", "Negative", "Neutral"],
    theme: { mode: "dark" },
    colors: ["#34d399", "#f87171", "#60a5fa"],
    legend: { labels: { colors: "#9ca3af" }, position: "bottom" },
    chart: { background: "transparent" },
    stroke: { colors: ["#111827"] },
};
</script>

<template>
    <Head title="SignalState Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#0b1120] text-white flex">
            <Sidebar />

            <div class="flex-1 pl-[280px] flex flex-col">
                <Topbar v-model="searchQuery" />

                <main class="p-10 mt-24">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold tracking-tight">
                            National Sentiment Monitoring
                        </h2>
                        <p class="text-gray-500 mt-2">
                            Real-time intelligence analysis across digital
                            platforms.
                        </p>
                    </div>

                    <!-- METRIC CARDS OVERVIEW -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8"
                    >
                        <div class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <p class="metric-label">Total Volume</p>
                                    <h3 class="metric-number">
                                        {{ totalBerita }}
                                    </h3>
                                </div>
                                <div class="metric-icon blue">
                                    <MessageSquare class="w-5 h-5" />
                                </div>
                            </div>
                            <div class="metric-bottom">
                                Total database ingestion record count
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <p class="metric-label">Positive Support</p>
                                    <h3 class="metric-number">
                                        {{ stats.positive }}
                                    </h3>
                                </div>
                                <div class="metric-icon emerald">
                                    <TrendingUp class="w-5 h-5" />
                                </div>
                            </div>
                            <div class="metric-bottom text-emerald-400">
                                Favorable mentions track
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <p class="metric-label">Negative Spike</p>
                                    <h3 class="metric-number">
                                        {{ stats.negative }}
                                    </h3>
                                </div>
                                <div class="metric-icon red">
                                    <ShieldAlert class="w-5 h-5" />
                                </div>
                            </div>
                            <div class="metric-bottom text-red-400">
                                Critical issues detected
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <p class="metric-label">
                                        High Toxic Alerts
                                    </p>
                                    <h3 class="metric-number text-red-500">
                                        {{ stats.highToxic }}
                                    </h3>
                                </div>
                                <div class="metric-icon red animate-pulse">
                                    <ShieldAlert class="w-5 h-5" />
                                </div>
                            </div>
                            <div class="metric-bottom text-gray-500">
                                Toxicity threshold > 30%
                            </div>
                        </div>
                    </div>

                    <!-- AI CLUSTERING WIDGET -->
                    <div class="panel mb-8">
                        <div class="panel-header mb-6">
                            <div>
                                <h3 class="panel-title">
                                    AI Issue Intelligence Clustering
                                </h3>
                                <p class="panel-subtitle">
                                    Dynamic topics categorized by NLP Engine
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                v-for="cluster in clusters"
                                :key="cluster.id"
                                class="bg-white/[0.01] border border-white/5 rounded-2xl p-5 hover:border-emerald-500/20 transition"
                            >
                                <div
                                    class="flex justify-between items-center mb-3"
                                >
                                    <span
                                        class="text-sm font-semibold text-gray-200"
                                        >📦 {{ cluster.cluster_name }}</span
                                    >
                                    <span
                                        class="text-xs font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded-lg"
                                    >
                                        {{ cluster.total_posts }} Docs
                                    </span>
                                </div>
                                <p
                                    class="text-xs text-gray-400 leading-relaxed italic"
                                >
                                    "{{ cluster.summary }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CHARTS -->
                    <div class="grid grid-cols-12 gap-6 mb-8">
                        <div class="col-span-12 xl:col-span-8 panel">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">
                                        Sentiment Timeline
                                    </h3>
                                    <p class="panel-subtitle">
                                        Real-time opinion movement
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6">
                                <VueApexCharts
                                    type="line"
                                    height="340"
                                    :options="sentimentChart"
                                    :series="sentimentSeries"
                                />
                            </div>
                        </div>

                        <div class="col-span-12 xl:col-span-4 panel">
                            <div class="panel-header">
                                <div>
                                    <h3 class="panel-title">
                                        Sentiment Distribution
                                    </h3>
                                    <p class="panel-subtitle">
                                        Real ratio from database
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6">
                                <VueApexCharts
                                    type="donut"
                                    height="340"
                                    :options="pieChart"
                                    :series="pieSeries"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- GEOGRAPHICAL SPREAD -->
                    <div class="panel mb-8">
                        <div class="panel-header mb-6">
                            <div>
                                <h3 class="panel-title">
                                    Geographical Sentiment Spread
                                </h3>
                                <p class="panel-subtitle">
                                    Regional data density tracking from live
                                    mentions
                                </p>
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
                        >
                            <div
                                v-for="(count, region) in chartData.map"
                                :key="region"
                                class="bg-white/[0.02] border border-white/5 p-4 rounded-2xl"
                            >
                                <div
                                    class="text-xs text-gray-400 font-mono uppercase tracking-wider"
                                >
                                    // {{ region }}
                                </div>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="text-2xl font-bold">{{
                                        count
                                    }}</span>
                                    <span class="text-xs text-gray-500"
                                        >mentions</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-800 h-1.5 rounded-full mt-3 overflow-hidden"
                                >
                                    <div
                                        class="bg-emerald-400 h-full transition-all duration-500"
                                        :style="{
                                            width:
                                                Math.min(count * 10, 100) + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LIVE STREAM INTERNET DATA FEED (INTELLIGENCE EXPANSION) -->
                    <div class="panel">
                        <div class="panel-header mb-6">
                            <div>
                                <h3 class="panel-title">
                                    Live Intelligence Data Feed
                                </h3>
                                <p class="panel-subtitle">
                                    Processed records stream (Latest 20)
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="post in posts"
                                :key="post.id"
                                class="feed-item"
                            >
                                <div
                                    class="flex items-start justify-between gap-6"
                                >
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center justify-between mb-3"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <!-- INSIDEN AVATAR DINAMIS MEMBACA PORTAL BERITA ASLI -->
                                                <div
                                                    class="w-10 h-10 rounded-2xl bg-white/[0.04] border border-white/5 flex items-center justify-center text-sm font-mono font-bold text-emerald-400"
                                                >
                                                    {{
                                                        post.username
                                                            ? post.username[0]
                                                            : "N"
                                                    }}
                                                </div>
                                                <div>
                                                    <h4
                                                        class="text-sm font-medium"
                                                    >
                                                        @{{
                                                            post.username ||
                                                            "anonymous"
                                                        }}
                                                    </h4>
                                                    <p
                                                        class="text-xs text-gray-500 font-mono"
                                                    >
                                                        {{ post.platform }} //
                                                        {{ post.posted_at }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- BADGE PRIORITAS & KATEGORI DATA PYTHON -->
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    v-if="post.issue_category"
                                                    class="text-[10px] font-mono px-2.5 py-1 rounded-lg bg-white/[0.04] border border-white/10 uppercase tracking-wider text-gray-400"
                                                >
                                                    📁 {{ post.issue_category }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-lg border uppercase tracking-wider"
                                                    :class="{
                                                        'bg-red-500/10 text-red-400 border-red-500/20 animate-pulse':
                                                            post.priority_level ===
                                                            'high',
                                                        'bg-amber-500/10 text-amber-400 border-amber-500/20':
                                                            post.priority_level ===
                                                            'medium',
                                                        'bg-slate-500/10 text-slate-400 border-slate-500/20':
                                                            post.priority_level ===
                                                                'low' ||
                                                            !post.priority_level,
                                                    }"
                                                >
                                                    ⚡
                                                    {{
                                                        post.priority_level ||
                                                        "LOW"
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- ISI BERITA -->
                                        <p
                                            class="text-gray-300 leading-7 text-sm font-sans"
                                        >
                                            {{ post.content }}
                                        </p>

                                        <!-- METADATA INTELIJEN BAWAAN -->
                                        <div
                                            class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-4 text-xs font-mono text-gray-500 border-t border-white/[0.02] pt-3"
                                        >
                                            <span
                                                >🎯 Confidence:
                                                {{
                                                    Math.round(
                                                        (post.confidence_score ??
                                                            0.5) * 100,
                                                    )
                                                }}%</span
                                            >
                                            <span v-if="post.emotion"
                                                >🎭 Emotion:
                                                <span
                                                    class="text-gray-300 uppercase"
                                                    >{{ post.emotion }}</span
                                                ></span
                                            >
                                            <span
                                                v-if="
                                                    parseFloat(
                                                        post.toxicity_score,
                                                    ) > 0
                                                "
                                                class="text-red-400"
                                                >⚠️ Toxicity:
                                                {{
                                                    Math.round(
                                                        post.toxicity_score *
                                                            100,
                                                    )
                                                }}%</span
                                            >

                                            <!-- KATA KUNCI YANG COCOK (MATCHED KEYWORDS) -->
                                            <span
                                                v-if="post.matched_keywords"
                                                class="text-emerald-500/80 truncate max-w-[300px]"
                                            >
                                                🔑 Keywords:
                                                {{ post.matched_keywords }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- LABEL SENTIMEN KANAN -->
                                    <div class="self-start">
                                        <div
                                            class="px-4 py-1.5 rounded-xl text-xs font-mono border uppercase font-bold tracking-wider"
                                            :class="{
                                                'bg-red-500/10 text-red-400 border-red-500/20':
                                                    post.sentiment ===
                                                    'negative',
                                                'bg-emerald-500/10 text-emerald-400 border-emerald-500/20':
                                                    post.sentiment ===
                                                    'positive',
                                                'bg-sky-500/10 text-sky-400 border-sky-500/20':
                                                    post.sentiment ===
                                                    'neutral',
                                            }"
                                        >
                                            {{ post.sentiment ?? "neutral" }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="!posts || posts.length === 0"
                                class="text-center py-8 text-gray-600 font-mono text-xs"
                            >
                                [!] No operational database records found
                                matching query.
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.metric-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 28px;
    padding: 24px;
    backdrop-filter: blur(20px);
    transition: 0.25s;
}
.metric-card:hover {
    transform: translateY(-2px);
    border-color: rgba(16, 185, 129, 0.15);
}
.metric-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}
.metric-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}
.metric-number {
    font-size: 32px;
    font-weight: 700;
    margin-top: 8px;
}
.metric-bottom {
    margin-top: 14px;
    font-size: 13px;
    color: #4b5563;
}
.metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.panel {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 28px;
    padding: 28px;
    backdrop-filter: blur(20px);
}
.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.panel-title {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: -0.025em;
}
.panel-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #52525b;
}

.feed-item {
    background: rgba(255, 255, 255, 0.01);
    border: 1px solid rgba(255, 255, 255, 0.04);
    border-radius: 20px;
    padding: 20px;
    transition: 0.22s;
}
.feed-item:hover {
    background: rgba(255, 255, 255, 0.02);
    border-color: rgba(255, 255, 255, 0.07);
}
</style>
