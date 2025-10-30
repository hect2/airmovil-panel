<template>
    <div class="sticky top-2 z-20 mb-8">
        <div class="bg-white glass-card rounded-2xl border border-[var(--border-color)] card-elevated">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-extrabold text-[var(--ink)] mb-1">
                            Dashboard
                        </h1>
                        <p class="text-sm text-[var(--muted-text)]">
                            Análisis de Transacciones y Reservas
                        </p>
                    </div>
                    <button @click="onExport"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[var(--brand)] text-white rounded-full hover:bg-[var(--brand-700)] transition-all hover:shadow-lg hover:-translate-y-0.5 text-sm font-medium">
                        <Download class="w-4 h-4" />
                        <span class="hidden sm:inline">Exportar CSV</span>
                    </button>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <!-- Date Range -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-[var(--ink)] mb-2 uppercase tracking-wide">
                            Rango de Fechas
                        </label>
                        <div class="flex gap-2">
                            <input type="date" :value="filters.range.start.toISOString().split('T')[0]"
                                @change="e => updateFilters({ range: { ...filters.range, start: new Date(e.target.value) } })"
                                class="flex-1 h-10 px-3 py-2 bg-white border border-[var(--border-color)] rounded-xl focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent text-sm transition-all" />
                            <input type="date" :value="filters.range.end.toISOString().split('T')[0]"
                                @change="e => updateFilters({ range: { ...filters.range, end: new Date(e.target.value) } })"
                                class="flex-1 h-10 px-3 py-2 bg-white border border-[var(--border-color)] rounded-xl focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent text-sm transition-all" />
                        </div>
                    </div>

                    <!-- Bucket -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--ink)] mb-2 uppercase tracking-wide">
                            Agrupar por
                        </label>
                        <select :value="filters.bucket" @change="e => updateFilters({ bucket: e.target.value })"
                            class="w-full h-10 px-3 py-2 bg-white border border-[var(--border-color)] rounded-xl focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent text-sm transition-all">
                            <option value="hour">Hora</option>
                            <option value="day">Día</option>
                            <option value="week">Semana</option>
                            <option value="month">Mes</option>
                        </select>
                    </div>

                    <!-- TX Status -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--ink)] mb-2 uppercase tracking-wide">
                            Estado TX
                        </label>
                        <select multiple :value="filters.txStatuses"
                            @change="e => updateFilters({ txStatuses: Array.from(e.target.selectedOptions, o => o.value) })"
                            class="w-full h-10 px-3 py-2 bg-white border border-[var(--border-color)] rounded-xl focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent text-sm transition-all">
                            <option value="ACCEPT">Aceptada</option>
                            <option value="DECLINE">Rechazada</option>
                            <option value="PENDING">Pendiente</option>
                            <option value="REFUND">Reembolso</option>
                            <option value="CHARGEBACK">Contracargo</option>
                        </select>
                    </div>

                    <!-- Payment Methods -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--ink)] mb-2 uppercase tracking-wide">
                            Método Pago
                        </label>
                        <select multiple :value="filters.paymentMethods"
                            @change="e => updateFilters({ paymentMethods: Array.from(e.target.selectedOptions, o => o.value) })"
                            class="w-full h-10 px-3 py-2 bg-white border border-[var(--border-color)] rounded-xl focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent text-sm transition-all">
                            <option value="Credit Card">Tarjeta</option>
                            <option value="Wallet">Billetera</option>
                            <option value="Cash">Efectivo</option>
                            <option value="Transfer">Transferencia</option>
                        </select>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="mt-4 flex justify-end">
                    <button @click="handleReset"
                        class="inline-flex items-center gap-2 px-4 py-2 text-[var(--muted-text)] hover:text-[var(--ink)] hover:bg-gray-50 rounded-xl transition-all text-sm font-medium">
                        <RotateCcw class="w-4 h-4" />
                        Resetear
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, toRefs } from "vue";
import { RotateCcw, Download } from "lucide-vue-next";

export default {
    name: "DashboardFilters",
    components: { RotateCcw, Download },
    props: {
        filters: {
            type: Object,
            required: true
        },
        onFiltersChange: {
            type: Function,
            required: true
        },
        onExport: {
            type: Function,
            required: true
        }
    },
    setup(props) {
        const { filters, onFiltersChange, onExport } = toRefs(props);

        const handleReset = () => {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - 30);
            onFiltersChange.value({
                range: { start, end },
                bucket: "day",
                txStatuses: [],
                paymentMethods: [],
                resStatuses: []
            });
        };

        const updateFilters = (updates) => {
            onFiltersChange.value({
                ...filters.value,
                ...updates
            });
        };

        return { filters, handleReset, updateFilters, onExport };
    }
};
</script>
