<template>
    <div class="w-full min-h-screen bg-gray-50 p-4 md:p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Encabezado -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Reservas</h1>
                <p class="text-gray-600">
                    {{ filteredReservations.length }} reserva(s) encontrada(s)
                </p>
            </div>

            <!-- Filtros -->
            <FiltersComponent :search-term="searchTerm" @update:search-term="searchTerm = $event"
                :status-filter="statusFilter" @update:status-filter="statusFilter = $event"
                :payment-filter="paymentFilter" @update:payment-filter="paymentFilter = $event"
                :driver-filter="driverFilter" @update:driver-filter="driverFilter = $event" />

            <!-- Tabla escritorio -->
            <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th v-for="header in headers" :key="header"
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ header }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="reservation in currentReservations" :key="reservation.id"
                                class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-sm" :title="reservation.id">
                                    {{ reservation.id.slice(0, 8) }}...
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ formatDate(reservation.createdAt) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ reservation.user.name }}</div>
                                        <div class="text-gray-500" :title="reservation.user.email">
                                            {{ reservation.user.email.slice(0, 20) }}...
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        <div class="font-medium">{{ reservation.car.title }}</div>
                                        <div class="text-gray-500">
                                            {{ reservation.car.brand.title }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-mono font-bold">
                                    {{ reservation.car.plate }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ reservation.driverDeliveryAtCarLocation ? 'Sí' : 'No' }}
                                </td>
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ formatCurrency(reservation.totalAmount) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ reservation.paymentMethodName }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadgeComponent :status="reservation.status" />
                                </td>
                                <td class="px-4 py-3">
                                    <button @click="openReservation(reservation)"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        <Eye class="w-4 h-4" />
                                        Ver más
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vista móvil -->
            <div class="md:hidden space-y-4">
                <div v-for="reservation in currentReservations" :key="reservation.id"
                    class="bg-white rounded-lg shadow p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-mono text-sm text-gray-600">
                                {{ reservation.id.slice(0, 8) }}...
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ formatDate(reservation.createdAt) }}
                            </p>
                        </div>
                        <StatusBadgeComponent :status="reservation.status" />
                    </div>

                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-600">Usuario</p>
                            <p class="font-medium">{{ reservation.user.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Carro</p>
                            <p class="font-medium">{{ reservation.car.title }}</p>
                            <p class="text-sm text-gray-500">{{ reservation.car.plate }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total</p>
                            <p class="font-bold text-lg">
                                {{ formatCurrency(reservation.totalAmount) }}
                            </p>
                        </div>
                    </div>

                    <button @click="openReservation(reservation)"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <Eye class="w-4 h-4" />
                        Ver más
                    </button>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Mostrando {{ startIndex + 1 }} a
                    {{ Math.min(endIndex, filteredReservations.length) }} de
                    {{ filteredReservations.length }} resultados
                </p>
                <div class="flex items-center gap-2">
                    <button @click="prevPage" :disabled="currentPage === 1"
                        class="p-2 rounded-lg border hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <span class="px-4 py-2 text-sm font-medium">
                        Página {{ currentPage }} de {{ totalPages }}
                    </span>
                    <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="p-2 rounded-lg border hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <ReservationModalComponent v-if="selectedReservation" :reservation="selectedReservation"
            @close="selectedReservation = null" />
    </div>
</template>

<script>
import { formatDate, formatCurrency } from '../../utils/formatters'
import StatusBadgeComponent from './StatusBadgeComponent.vue'
import ReservationModalComponent from './ReservationModalComponent.vue'
import FiltersComponent from './FiltersComponent.vue'
import { ChevronLeft, ChevronRight, Eye } from 'lucide-vue-next'

export default {
    name: 'ReservationsSectionComponent',
    components: {
        FiltersComponent,
        StatusBadgeComponent,
        ReservationModalComponent,
        ChevronLeft,
        ChevronRight,
        Eye,
    },
    props: {
        reservations: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            searchTerm: '',
            statusFilter: '',
            paymentFilter: '',
            driverFilter: '',
            currentPage: 1,
            selectedReservation: null,
            itemsPerPage: 10,
            headers: [
                'ID',
                'Fecha',
                'Usuario',
                'Carro',
                'Placa',
                'Conductor',
                'Total',
                'Método',
                'Estatus',
                'Acciones',
            ],
        }
    },
    computed: {
        filteredReservations() {
            return this.reservations.filter((r) => {
                const term = this.searchTerm.toLowerCase()
                const matchesSearch =
                    !term ||
                    r.id.toLowerCase().includes(term) ||
                    r.user.name.toLowerCase().includes(term) ||
                    r.user.email.toLowerCase().includes(term) ||
                    r.car.title.toLowerCase().includes(term) ||
                    r.car.plate.toLowerCase().includes(term)

                const matchesStatus = !this.statusFilter || r.status === this.statusFilter
                const matchesPayment =
                    !this.paymentFilter || r.paymentMethodName === this.paymentFilter
                const matchesDriver =
                    !this.driverFilter ||
                    r.bookedWithDriver.toString() === this.driverFilter

                return matchesSearch && matchesStatus && matchesPayment && matchesDriver
            })
        },
        totalPages() {
            return Math.ceil(this.filteredReservations.length / this.itemsPerPage)
        },
        startIndex() {
            return (this.currentPage - 1) * this.itemsPerPage
        },
        endIndex() {
            return this.startIndex + this.itemsPerPage
        },
        currentReservations() {
            return this.filteredReservations.slice(this.startIndex, this.endIndex)
        },
    },
    methods: {
        formatDate,
        formatCurrency,
        openReservation(reservation) {
            this.selectedReservation = reservation
        },
        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--
        },
    },
}
</script>
