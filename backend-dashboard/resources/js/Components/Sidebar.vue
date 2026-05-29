<script setup>
import { Link } from "@inertiajs/vue3"; // Perbaikan: Wajib import Link Inertia
import {
    LayoutDashboard,
    Activity,
    ShieldAlert,
    Globe2,
    BarChart3,
    Settings,
    FileText,
    Radio,
} from "lucide-vue-next";
</script>

<template>
    <aside
        class="w-[280px] bg-[#111827] border-r border-white/5 px-6 py-8 flex flex-col fixed h-screen top-0 left-0 z-20"
    >
        <div class="mb-12">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-3xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center"
                >
                    <Radio class="w-7 h-7 text-emerald-400" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">
                        SignalState
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Intelligence Platform
                    </p>
                </div>
            </div>
        </div>

        <nav class="space-y-2">
            <Link href="/dashboard" class="w-full no-underline block">
                <button
                    :class="
                        $page.component === 'Dashboard'
                            ? 'sidebar-active'
                            : 'sidebar-item'
                    "
                >
                    <LayoutDashboard class="w-5 h-5" />
                    Dashboard
                </button>
            </Link>

            <Link href="/monitoring" class="w-full no-underline block">
                <button
                    :class="
                        $page.component === 'Monitoring'
                            ? 'sidebar-active'
                            : 'sidebar-item'
                    "
                >
                    <Activity class="w-5 h-5" />
                    Monitoring
                </button>
            </Link>

            <button class="sidebar-item">
                <BarChart3 class="w-5 h-5" />
                Analytics
            </button>

            <a href="/api/export-pdf" class="w-full no-underline block">
                <button class="sidebar-item">
                    <FileText class="w-5 h-5" />
                    Reports
                </button>
            </a>

            <Link href="/alerts" class="w-full no-underline block">
                <button
                    :class="
                        $page.component === 'Alerts'
                            ? 'sidebar-active'
                            : 'sidebar-item'
                    "
                >
                    <ShieldAlert class="w-5 h-5" />
                    Alerts
                </button>
            </Link>

            <button class="sidebar-item">
                <Globe2 class="w-5 h-5" />
                Platforms
            </button>
            
            <button class="sidebar-item">
                <Settings class="w-5 h-5" />
                Settings
            </button>
        </nav>

        <div
            class="mt-auto rounded-3xl bg-white/[0.03] border border-white/5 p-5"
        >
            <div class="flex items-center gap-3 mb-4">
                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                <span class="text-sm text-emerald-400">Monitoring Active</span>
            </div>
            <p class="text-sm text-gray-500 leading-relaxed">
                Real-time sentiment surveillance is running normally.
            </p>
        </div>
    </aside>
</template>

<style scoped>
.sidebar-item {
    width: 100%;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding-inline: 18px;
    color: #9ca3af;
    transition: 0.2s;
    background: transparent;
    border: none;
    cursor: pointer;
}

.sidebar-item:hover {
    background: rgba(255, 255, 255, 0.04);
    color: white;
}

.sidebar-active {
    width: 100%;
    height: 56px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding-inline: 18px;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.18);
    color: #34d399;
    cursor: pointer;
}
</style>