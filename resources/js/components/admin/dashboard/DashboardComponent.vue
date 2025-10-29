<template>
    <div class="min-h-screen bg-[var(--bg)]" :style="{
        backgroundImage:
            'radial-gradient(circle at 20% 50%, rgba(227, 6, 19, 0.03) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.03) 0%, transparent 50%)',
    }">
        <!-- Filtros -->
        <!-- <DashboardFiltersComponent :filters="filters" @filters-change="filters = $event" @export="handleExportCSV" /> -->

        <div class="max-w-[1400px] mx-auto px-6 lg:px-10 pb-10 space-y-8">
            <!-- KPIs Grid -->
            <div v-if="dashboardData && dashboardData.kpis"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <KpiCardComponent title="Ventas Totales" :value="dashboardData.kpis.totalSales"
                    :delta="dashboardData.kpis.deltas.totalSales" format="currency">
                    <template #icon>
                        <DollarSign class="w-6 h-6" />
                    </template>
                </KpiCardComponent>

                <KpiCardComponent title="Órdenes Totales" :value="dashboardData.kpis.totalOrders"
                    :delta="dashboardData.kpis.deltas.totalOrders" format="number">
                    <template #icon>
                        <ShoppingCart class="w-6 h-6" />
                    </template>
                </KpiCardComponent>

                <KpiCardComponent title="Reservas Totales" :value="dashboardData.kpis.totalReservations"
                    :delta="dashboardData.kpis.deltas.totalReservations" format="number">
                    <template #icon>
                        <CalendarCheck class="w-6 h-6" />
                    </template>
                </KpiCardComponent>

                <KpiCardComponent title="Tasa de Aprobación" :value="dashboardData.kpis.approvalRate"
                    :delta="dashboardData.kpis.deltas.approvalRate" format="percentage">
                    <template #icon>
                        <TrendingUp class="w-6 h-6" />
                    </template>
                </KpiCardComponent>

                <KpiCardComponent title="Ticket Promedio" :value="dashboardData.kpis.averageTicket"
                    :delta="dashboardData.kpis.deltas.averageTicket" format="currency">
                    <template #icon>
                        <Ticket class="w-6 h-6" />
                    </template>
                </KpiCardComponent>

                <KpiCardComponent title="Ocupación" value="—" format="percentage">
                    <template #icon>
                        <Clock class="w-6 h-6" />
                    </template>
                </KpiCardComponent>
            </div>

            <!-- Tabs Section -->
            <div class="bg-white rounded-2xl card-elevated border border-[var(--border-color)] overflow-hidden">
                <div class="border-b border-[var(--border-color)] bg-gray-50">
                    <div class="flex gap-1 p-2">
                        <button @click="activeTab = 'transactions'" :class="[
                            'flex-1 px-6 py-3 text-sm font-semibold rounded-xl transition-all',
                            activeTab === 'transactions'
                                ? 'bg-white text-[var(--brand)] shadow-sm'
                                : 'text-[var(--muted-text)] hover:text-[var(--ink)] hover:bg-white/50',
                        ]">
                            Transacciones
                        </button>
                        <button @click="activeTab = 'reservations'" :class="[
                            'flex-1 px-6 py-3 text-sm font-semibold rounded-xl transition-all',
                            activeTab === 'reservations'
                                ? 'bg-white text-[var(--brand)] shadow-sm'
                                : 'text-[var(--muted-text)] hover:text-[var(--ink)] hover:bg-white/50',
                        ]">
                            Reservas
                        </button>
                    </div>
                </div>

                <div class="p-6 lg:p-8">
                    <TransactionsSectionComponent
                        v-if="activeTab === 'transactions'"
                        :data="dashboardData.series"
                    />

                    <ReservationsSectionComponent
                        v-else
                        :reservations="dashboardData.series"
                    />
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import {
    DollarSign,
    ShoppingCart,
    CalendarCheck,
    TrendingUp,
    Ticket,
    Clock,
} from "lucide-vue-next";
import { buildDashboardData } from "../../utils/dashboardData.js";
import DashboardFiltersComponent from "./DashboardFiltersComponent.vue";
import KpiCardComponent from "./KpiCardComponent.vue";
import TransactionsSectionComponent from "./TransactionsSectionComponent.vue";
import ReservationsSectionComponent from "./ReservationsSectionComponent.vue";

export default {
    name: "DashboardComponent",
    components: {
        DashboardFiltersComponent,
        KpiCardComponent,
        TransactionsSectionComponent,
        ReservationsSectionComponent,
        DollarSign,
        ShoppingCart,
        CalendarCheck,
        TrendingUp,
        Ticket,
        Clock,
    },
    data() {
        const now = new Date();
        const getRecentDate = (daysAgo, hoursAgo = 10) => {
            const date = new Date(now);
            date.setDate(date.getDate() - daysAgo);
            date.setHours(hoursAgo, 0, 0, 0);
            return date.toISOString();
        };

        return {
            activeTab: "transactions",
            filters: {
                range: {
                    start: (() => {
                        const d = new Date();
                        d.setDate(d.getDate() - 30);
                        return d;
                    })(),
                    end: new Date(),
                },
                bucket: "day",
                txStatuses: [],
                paymentMethods: [],
                resStatuses: [],
            },
            mockTransactions: [
                { id: "t1", createdAt: getRecentDate(1, 10), status: "ACCEPT", amount: 1200, method: "Credit Card" },
                { id: "t2", createdAt: getRecentDate(2, 11), status: "DECLINE", amount: 800, method: "Wallet" },
                { id: "t3", createdAt: getRecentDate(3, 9), status: "ACCEPT", amount: 4400, method: "Credit Card" },
                { id: "t4", createdAt: getRecentDate(5, 14), status: "ACCEPT", amount: 3200, method: "Cash" },
                { id: "t5", createdAt: getRecentDate(7, 16), status: "PENDING", amount: 1500, method: "Credit Card" },
            ],
            mockReservations: [
                { id: "r1", createdAt: getRecentDate(1, 10), status: "completed", totalAmount: 4400, car: { title: "Mazda MX-5 Miata" } },
                { id: "r2", createdAt: getRecentDate(3, 12), status: "pending", totalAmount: 5000, car: { title: "Tesla Model 3" } },
            ],
            dashboardData: "",
        };
    },
    mounted() {
        this.generateDashboard();
        window.dispatchEvent(
            new CustomEvent("nav:enter", { detail: { route: "/dashboard" } })
        );
    },
    watch: {
        filters: {
            handler(newFilters) {
                this.generateDashboard();
                window.dispatchEvent(
                    new CustomEvent("dashboard:filter", { detail: newFilters })
                );
            },
            deep: true,
        },
    },
    methods: {
        generateDashboard() {
            this.dashboardData = buildDashboardData({
                txs: this.mockTransactions,
                reservations: this.mockReservations,
                filters: this.filters,
                previousPeriodTxs: this.mockTransactions,
                previousPeriodReservations: this.mockReservations,
            });
            window.dispatchEvent(
                new CustomEvent("dashboard:render", {
                    detail: {
                        kpis: this.dashboardData.kpis,
                        series: this.dashboardData.series,
                    },
                })
            );

            console.log("DATA_DASHBOARD", this.dashboardData);
        },
        handleExportCSV() {
            const data =
                this.activeTab === "transactions"
                    ? this.mockTransactions
                    : this.mockReservations;
            if (!data.length) return;
            const headers = Object.keys(data[0]).join(",");
            const rows = data.map((item) => Object.values(item).join(","));
            const csv = [headers, ...rows].join("\n");
            const blob = new Blob([csv], { type: "text/csv" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `${this.activeTab}_${new Date()
                .toISOString()
                .split("T")[0]}.csv`;
            a.click();
        },
    },
};
</script>
