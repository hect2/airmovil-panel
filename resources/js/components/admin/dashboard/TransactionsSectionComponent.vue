<template>
    <div class="space-y-6">
        <!-- Línea -->
        <div class="bg-white rounded-2xl p-6 lg:p-8 card-elevated border border-[var(--border-color)]">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-[var(--ink)] mb-1">Ventas y Órdenes en el Tiempo</h3>
                <p class="text-sm text-[var(--muted-text)]">Evolución de montos y cantidad de transacciones</p>
            </div>
            <Line :data="lineData" :options="lineOptions" />
        </div>

        <!-- Fila 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Barras -->
            <div class="bg-white rounded-2xl p-6 lg:p-8 card-elevated border border-[var(--border-color)]">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[var(--ink)] mb-1">Transacciones por Estado</h3>
                    <p class="text-sm text-[var(--muted-text)]">Distribución de estados en el tiempo</p>
                </div>
                <Bar :data="barData" :options="barOptions" />
            </div>

            <!-- Pie -->
            <div class="bg-white rounded-2xl p-6 lg:p-8 card-elevated border border-[var(--border-color)]">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[var(--ink)] mb-1">Métodos de Pago</h3>
                    <p class="text-sm text-[var(--muted-text)]">Distribución por método de pago</p>
                </div>
                <Pie :data="pieData" :options="pieOptions" />
                <div class="mt-4 text-center">
                    <p class="text-sm text-[var(--muted-text)]">Total Transacciones</p>
                    <p class="text-2xl font-bold text-[var(--brand)] tabular-nums">{{ totalTransactions }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement,
} from 'chart.js'
import { Line, Bar, Pie } from 'vue-chartjs'

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    PointElement
)

export default {
    name: 'TransactionsSectionComponent',
    components: { Line, Bar, Pie },
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            COLORS: {
                ACCEPT: '#16A34A',
                DECLINE: '#EF4444',
                PENDING: '#F59E0B',
                REFUND: '#8B5CF6',
                CHARGEBACK: '#EC4899',
            },
            PAYMENT_COLORS: ['#E30613', '#3B82F6', '#F59E0B', '#8B5CF6'],
            labels: {
                ACCEPT: 'Aceptada',
                DECLINE: 'Rechazada',
                PENDING: 'Pendiente',
                REFUND: 'Reembolso',
                CHARGEBACK: 'Contracargo',
            },
        }
    },
    computed: {
        totalTransactions() {
            return this.data.txByMethodPie
                .reduce((sum, item) => sum + item.value, 0)
                .toLocaleString('es-GT')
        },
        /* === Línea === */
        lineData() {
            return {
                labels: this.data.txAmountByBucket.map((d) => d.bucketLabel),
                datasets: [
                    {
                        label: 'Monto (GTQ)',
                        data: this.data.txAmountByBucket.map((d) => d.amount),
                        borderColor: 'var(--brand)',
                        tension: 0.4,
                        yAxisID: 'y1',
                    },
                    {
                        label: '# Órdenes',
                        data: this.data.txAmountByBucket.map((d) => d.count),
                        borderColor: 'var(--info)',
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y2',
                    },
                ],
            }
        },
        lineOptions() {
            return {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                stacked: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y1: { type: 'linear', position: 'left' },
                    y2: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } },
                },
            }
        },
        /* === Barras === */
        barData() {
            const labels = this.data.txByStatusStacked.map((d) => d.bucketLabel)
            const keys = Object.keys(this.COLORS)
            const datasets = keys.map((key) => ({
                label: this.labels[key],
                backgroundColor: this.COLORS[key],
                data: this.data.txByStatusStacked.map((d) => d[key] || 0),
                stack: 'stack1',
            }))
            return { labels, datasets }
        },
        barOptions() {
            return {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { x: { stacked: true }, y: { stacked: true } },
            }
        },
        /* === Pie === */
        pieData() {
            return {
                labels: this.data.txByMethodPie.map((d) => d.name),
                datasets: [
                    {
                        data: this.data.txByMethodPie.map((d) => d.value),
                        backgroundColor: this.PAYMENT_COLORS,
                    },
                ],
            }
        },
        pieOptions() {
            return {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const item = this.data.txByMethodPie[ctx.dataIndex]
                                return `${item.name}: ${item.value.toLocaleString('es-GT')} (Q ${item.amount.toLocaleString('es-GT', { minimumFractionDigits: 2 })})`
                            },
                        },
                    },
                },
            }
        },
    },
}
</script>

<style scoped>
.card-elevated {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
</style>
