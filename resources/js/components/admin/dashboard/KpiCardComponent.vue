<template>
    <div
        class="group relative bg-white rounded-2xl overflow-hidden border border-[var(--border-color)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_40px_rgba(0,0,0,0.08)]">
        <!-- Accent stripe -->
        <div class="h-1 kpi-stripe" />

        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-[var(--muted-text)] mb-1">{{ title }}</p>
                    <p class="text-3xl lg:text-4xl font-extrabold text-[var(--ink)] tabular-nums tracking-tight">
                        {{ formattedValue }}
                    </p>
                </div>

                <div v-if="$slots.icon"
                    class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-[var(--brand)]/10 to-[var(--brand)]/5 flex items-center justify-center text-[var(--brand)] shadow-inner transition-transform group-hover:scale-110">
                    <slot name="icon" />
                </div>
            </div>

            <div v-if="delta !== undefined"
                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', deltaColor]">
                <component :is="deltaIcon" class="w-3.5 h-3.5" />
                <span>{{ deltaText }}</span>
                <span class="text-[10px] opacity-75">vs período anterior</span>
            </div>
        </div>
    </div>
</template>

<script>
import { TrendingUp, TrendingDown, Minus } from "lucide-vue-next"; // versión Vue de Lucide

export default {
    name: "KapiCardComponent",
    props: {
        title: { type: String, required: true },
        value: { type: [String, Number], required: true },
        delta: { type: Number, default: undefined },
        format: {
            type: String,
            default: "number",
            validator: (val) => ["number", "currency", "percentage"].includes(val),
        },
    },
    computed: {
        formattedValue() {
            if (typeof this.value === "string") return this.value;
            if (this.format === "currency")
                return `Q ${this.value.toLocaleString("es-GT", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })}`;
            if (this.format === "percentage") return `${this.value.toFixed(1)}%`;
            return this.value.toLocaleString("es-GT");
        },
        deltaColor() {
            if (this.delta === undefined || this.delta === 0)
                return "bg-gray-100 text-gray-600";
            return this.delta > 0 ? "bg-green-50 text-green-700" : "bg-red-50 text-red-700";
        },
        deltaIcon() {
            if (this.delta === undefined || this.delta === 0) return Minus;
            return this.delta > 0 ? TrendingUp : TrendingDown;
        },
        deltaText() {
            if (this.delta === 0) return "Sin cambio";
            return `${this.delta > 0 ? "+" : ""}${this.delta.toFixed(1)}%`;
        },
    },
};
</script>
