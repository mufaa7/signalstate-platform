<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Sidebar from "@/Components/Sidebar.vue";
import Topbar from "@/Components/Topbar.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    alerts: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="SignalState - System Alerts Log" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#0b1120] text-white flex">
            <Sidebar />
            <div class="flex-1 pl-[280px] flex flex-col">
                <Topbar />
                <main class="p-10 mt-24">
                    <div class="mb-8">
                        <h2
                            class="text-3xl font-bold tracking-tight text-red-400"
                        >
                            Security & Toxicity Logs
                        </h2>
                        <p class="text-gray-500 mt-2">
                            Real-time critical anomaly detections list.
                        </p>
                    </div>

                    <div class="panel">
                        <div class="space-y-4">
                            <div
                                v-for="alert in alerts"
                                :key="alert.id"
                                class="p-5 bg-white/[0.01] border border-white/5 rounded-2xl flex justify-between items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <span
                                            class="text-xs font-mono px-2 py-0.5 rounded border uppercase tracking-wider"
                                            :class="
                                                alert.severity === 'high'
                                                    ? 'bg-red-500/10 text-red-400 border-red-500/20'
                                                    : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                            "
                                        >
                                            CRITICAL: {{ alert.severity }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 font-mono"
                                            >{{ alert.triggered_at }}</span
                                        >
                                    </div>
                                    <p
                                        class="text-sm text-gray-300 font-sans mt-2"
                                    >
                                        {{ alert.message }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="alerts.length === 0"
                                class="text-center py-12 text-gray-600 font-mono text-xs"
                            >
                                [!] Analyst logs clean. No toxicity spike
                                threats recorded.
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
</style>
