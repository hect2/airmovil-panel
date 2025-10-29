<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-bold">
                        Reserva #{{ reservation.id.slice(0, 8) }}
                    </h2>
                    <StatusBadge :status="reservation.status" />
                </div>
                <div class="flex items-center gap-2">
                    <button @click="copyToClipboard(reservation.id)"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Copiar ID">
                        <Copy class="w-5 h-5" />
                    </button>
                    <button @click="onClose" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-b overflow-x-auto">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                    class="flex items-center gap-2 px-6 py-3 font-medium transition-colors whitespace-nowrap" :class="activeTab === tab.id
                        ? 'border-b-2 border-blue-500 text-blue-600'
                        : 'text-gray-600 hover:text-gray-900'">
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.label }}
                </button>
            </div>

            <!-- Contenido dinámico -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- RESUMEN -->
                <div v-if="activeTab === 'resumen'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Información General</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">ID:</span>
                                    <span class="font-mono">{{ reservation.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Transaction ID:</span>
                                    <span class="font-mono">{{ reservation.transactionId }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Método de pago:</span>
                                    <span>{{ reservation.paymentMethodName }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Creado:</span>
                                    <span>{{ formatDate(reservation.createdAt) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Fechas y Duración</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Recogida:</span>
                                    <span>
                                        {{ formatDate(reservation.pickupDate) }}
                                        {{ formatTime(reservation.pickupTime) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Devolución:</span>
                                    <span>
                                        {{ formatDate(reservation.returnDate) }}
                                        {{ formatTime(reservation.returnTime) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Duración:</span>
                                    <span>
                                        {{ reservation.totalDayOrHr }}
                                        {{ reservation.car.priceType === 'hr' ? 'horas' : 'días' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Desglose de Precios</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span>{{ formatCurrency(reservation.subtotal) }}</span>
                            </div>

                            <div v-if="reservation.taxAmount > 0" class="flex justify-between">
                                <span class="text-gray-600">
                                    Impuestos ({{ reservation.taxPercentage }}%):
                                </span>
                                <span>{{ formatCurrency(reservation.taxAmount) }}</span>
                            </div>

                            <div v-if="reservation.couponAmount > 0" class="flex justify-between text-green-600">
                                <span>Cupón aplicado:</span>
                                <span>-{{ formatCurrency(reservation.couponAmount) }}</span>
                            </div>

                            <div v-if="reservation.walletAmount > 0" class="flex justify-between text-blue-600">
                                <span>Wallet usado:</span>
                                <span>-{{ formatCurrency(reservation.walletAmount) }}</span>
                            </div>

                            <div class="flex justify-between text-xl font-bold border-t pt-3">
                                <span>Total:</span>
                                <span>{{ formatCurrency(reservation.totalAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="reservation.cancleReason" class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-800 mb-2">
                            Motivo de cancelación
                        </h4>
                        <p class="text-red-700">{{ reservation.cancleReason }}</p>
                    </div>
                </div>

                <!-- 🚗 CARRO -->
                <div v-if="activeTab === 'carro'" class="space-y-6">
                    <div class="relative">
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <img :src="images[currentImageIndex]" alt="Car" class="w-full h-full object-cover" />
                        </div>
                        <button v-if="images.length > 1" @click="prevImage"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                            <ChevronLeft class="w-6 h-6" />
                        </button>
                        <button v-if="images.length > 1" @click="nextImage"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                            <ChevronRight class="w-6 h-6" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    X,
    Copy,
    FileText,
    User,
    Car as CarIcon,
    MapPin,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next";
import { formatDate, formatCurrency, formatTime } from "../../utils/formatters";
import StatusBadgeComponent from "./StatusBadgeComponent.vue";

export default {
    name: "ReservationModalComponent",
    components: {
        X,
        Copy,
        FileText,
        User,
        CarIcon,
        MapPin,
        ChevronLeft,
        ChevronRight,
        StatusBadgeComponent,
    },
    props: {
        reservation: { type: Object, required: true },
        onClose: { type: Function, required: true },
    },
    data() {
        return {
            activeTab: "resumen",
            currentImageIndex: 0,
            currentGallery: "cover",
            tabs: [
                { id: "resumen", label: "Resumen", icon: FileText },
                { id: "usuario", label: "Usuario", icon: User },
                { id: "carro", label: "Carro", icon: CarIcon },
                { id: "ubicacion", label: "Ubicación", icon: MapPin },
                { id: "documentos", label: "Documentos", icon: FileText },
            ],
        };
    },
    computed: {
        images() {
            if (this.currentGallery === "cover") return this.reservation.car.urlImages;
            if (this.currentGallery === "reservation")
                return this.reservation.reservationDocumentsImages;
            return this.reservation.preReservationImages;
        },
    },
    methods: {
        formatDate,
        formatCurrency,
        formatTime,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
        },
        nextImage() {
            this.currentImageIndex =
                (this.currentImageIndex + 1) % this.images.length;
        },
        prevImage() {
            this.currentImageIndex =
                (this.currentImageIndex - 1 + this.images.length) % this.images.length;
        },
    },
};
</script>

<style scoped>
.leaflet-container {
    height: 100%;
    width: 100%;
}
</style>
