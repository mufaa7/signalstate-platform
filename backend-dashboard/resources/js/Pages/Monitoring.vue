<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Topbar from "@/Components/Topbar.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    posts: { type: Array, default: () => [] },
    currentPlatform: String,
});

const filterPlatform = (platform) => {
    router.get("/monitoring", { platform: platform }, { preserveScroll: true });
};
</script>

<template>
    <Head title="SignalState - Live Feed Monitoring" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#0b1120] text-white flex">
            <Sidebar />

            <div class="flex-1 pl-[280px] flex flex-col">
                <Topbar />

                <main class="p-10 mt-24">
                    <div class="mb-8 flex justify-between items-center">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight">
                                Live Intelligence Stream
                            </h2>
                            <p class="text-gray-500 mt-2">
                                Comprehensive tracking across connected data
                                pipelines.
                            </p>
                        </div>

                        <!-- FILTER TABS PLATFORM -->
                        <div
                            class="flex gap-2 bg-white/[0.02] border border-white/5 p-1.5 rounded-2xl"
                        >
                            <button
                                @click="filterPlatform('')"
                                :class="
                                    !currentPlatform
                                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                        : 'text-gray-400 hover:text-white'
                                "
                                class="px-4 py-2 rounded-xl text-xs font-mono transition"
                            >
                                ALL
                            </button>
                            <button
                                @click="filterPlatform('News Portal')"
                                :class="
                                    currentPlatform === 'News Portal'
                                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                        : 'text-gray-400 hover:text-white'
                                "
                                class="px-4 py-2 rounded-xl text-xs font-mono transition"
                            >
                                NEWS
                            </button>
                            <button
                                @click="filterPlatform('Twitter')"
                                :class="
                                    currentPlatform === 'Twitter'
                                        ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                        : 'text-gray-400 hover:text-white'
                                "
                                class="px-4 py-2 rounded-xl text-xs font-mono transition"
                            >
                                TWITTER
                            </button>
                        </div>
                    </div>

                    <!-- FULL WIDTH DATA FEED -->
                    <div class="panel">
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
                                            class="flex items-center gap-3 mb-3"
                                        >
                                            <div
                                                class="w-10 h-10 rounded-2xl bg-white/[0.04] border border-white/5 flex items-center justify-center text-xs font-mono text-gray-400"
                                            >
                                                {{
                                                    post.platform
                                                        ? post.platform[0]
                                                        : "N"
                                                }}
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium">
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
                                        <p
                                            class="text-gray-300 leading-7 text-sm font-sans"
                                        >
                                            {{ post.content }}
                                        </p>
                                        <div
                                            class="flex items-center gap-4 mt-4 text-xs font-mono text-gray-500"
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
                                                class="text-red-400 animate-pulse"
                                                >⚠️ Toxicity:
                                                {{
                                                    Math.round(
                                                        post.toxicity_score *
                                                            100,
                                                    )
                                                }}%</span
                                            >
                                        </div>
                                    </div>
                                    <div>
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
                                v-if="posts.length === 0"
                                class="text-center py-12 text-gray-600 font-mono text-xs"
                            >
                                [!] No active intelligence stream found for this
                                platform.
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.panel {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 28px;
    padding: 28px;
    backdrop-filter: blur(20px);
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
