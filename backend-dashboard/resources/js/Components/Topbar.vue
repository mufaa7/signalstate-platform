<script setup>
import { Search, Bell } from "lucide-vue-next";
import { ref, watch, onMounted, onUnmounted } from "vue";

const props = defineProps({ modelValue: String });
const emit = defineEmits(["update:modelValue"]);
const search = ref(props.modelValue || "");

const alertCount = ref(0);
let alertInterval = null;

// Ambil jumlah alert aktif dari database secara realtime
const fetchAlerts = async () => {
    try {
        const response = await fetch("/api/live-alerts");
        if (response.ok) {
            const data = await response.json();
            alertCount.value = data.length;
        }
    } catch (e) {
        console.error("Gagal sinkronisasi alert engine: ", e);
    }
};

watch(search, (newValue) => {
    emit("update:modelValue", newValue);
});

onMounted(() => {
    fetchAlerts();
    alertInterval = setInterval(fetchAlerts, 5000); // Polling alert per 5 detik
});
onUnmounted(() => {
    if (alertInterval) clearInterval(alertInterval);
});
</script>

<template>
    <header
        class="h-24 border-b border-white/5 bg-[#0b1120]/90 backdrop-blur-xl px-10 flex items-center justify-between fixed top-0 right-0 left-[280px] z-10"
    >
        <div class="relative w-[420px]">
            <Search
                class="w-5 h-5 text-gray-500 absolute left-5 top-1/2 -translate-y-1/2"
            />
            <input
                v-model="search"
                type="text"
                placeholder="Search issue, keyword, topic..."
                class="w-full h-14 bg-white/[0.03] border border-white/5 rounded-2xl pl-14 pr-5 text-sm text-white placeholder:text-gray-500 outline-none focus:border-emerald-500/20"
            />
        </div>

        <div class="flex items-center gap-5">
            <div
                class="flex items-center gap-3 px-5 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20"
            >
                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                <span class="text-sm text-emerald-400 font-mono tracking-wider"
                    >LIVE</span
                >
            </div>

            <button
                class="w-12 h-12 rounded-2xl bg-white/[0.03] border border-white/5 flex items-center justify-center hover:bg-white/[0.06] transition relative"
            >
                <Bell class="w-5 h-5 text-gray-400" />
                <span
                    v-if="alertCount > 0"
                    class="absolute -top-1 -right-1 bg-red-500 text-white font-mono text-[10px] w-5 h-5 rounded-full flex items-center justify-center animate-bounce"
                >
                    {{ alertCount }}
                </span>
            </button>

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20"></div>
                <div>
                    <p class="text-sm font-medium">Admin</p>
                    <p class="text-xs text-gray-500">Intelligence Analyst</p>
                </div>
            </div>
        </div>
    </header>
</template>
