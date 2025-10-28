<template>
    <LoadingComponent :props="loading" />
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-bold">
                        Reserva #{{ reservation.id ? reservation.id.slice(0, 8) : '' }}
                    </h2>
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border', getStatusColor(reservation.status)]">
                        {{ getStatusLabel(reservation.status) }}
                    </span>

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
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                    'flex items-center gap-2 px-6 py-3 font-medium transition-colors whitespace-nowrap',
                    activeTab === tab.id
                        ? 'border-b-2 border-blue-500 text-blue-600'
                        : 'text-gray-600 hover:text-gray-900'
                ]">
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.label }}
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
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
                                    <span class="font-mono">{{ reservation.paymentMethodName }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Creado:</span>
                                    <span class="font-mono">{{ formatDate(reservation.createdAt) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Fechas y Duración</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Recogida:</span>
                                    <span class="font-mono">
                                        {{ formatDatePersonalized(reservation.pickupDate, reservation.pickupTime) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Devolución:</span>
                                    <span class="font-mono">
                                        {{ formatDatePersonalized(reservation.returnDate, reservation.returnTime) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Duración:</span>
                                    <span class="font-mono">
                                        {{ reservation.totalDayOrHr }}
                                        {{ reservation.car.priceType === 'hr' ? 'horas' : 'días' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Creado:</span>
                                    <span class="font-mono">{{ formatDate(reservation.createdAt) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-12">
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Desglose de precios</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sub Total:</span>
                                    <span class="font-mono">{{ formatCurrency(reservation.subtotal) }}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-mono">{{ formatCurrency(reservation.totalAmount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'usuario'" class="space-y-6">
                    <!-- Sección de usuario -->
                    <div class="flex items-center gap-4">
                        <img v-if="reservation.user.profile_pic" :src="reservation.user.profile_pic"
                            :alt="reservation.user.name" class="w-20 h-20 rounded-full object-cover" />
                        <div v-else class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <User class="w-10 h-10 text-gray-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">{{ reservation.user.name }}</h3>
                            <p class="text-gray-600">{{ reservation.user.rol }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <a href="#" class="text-blue-600 hover:underline">{{ reservation.user.email }}</a>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Teléfono:</span>
                                <a href="#" class="text-blue-600 hover:underline">{{ reservation.user.ccode }}
                                    {{ reservation.user.mobile }}</a>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estado:</span>
                                <span class="{{reservation.user.status === '1' ? 'text-green-600' : 'text-red-600'}}">
                                {{ reservation.user.status === '1' ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                    <div class=" space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Wallet:</span>
                                <span>
                                    {{ formatCurrency(parseFloat(reservation.user.wallet)) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Registro:</span>
                                <span>{{ formatDate(reservation.user.rdate) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'carro'" class="space-y-6">

                    <div class="relative">
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                            <img v-if="currentImages.length" :src="currentImages[currentImageIndex]" alt="Car" class="w-full h-full object-cover" />

                        </div>

                        <template v-if="currentImages.length > 1">
                            <button @click="prevImage"
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                                <ChevronLeft class="w-6 h-6" />
                            </button>
                            <button @click="nextImage"
                                class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg">
                                <ChevronRight class="w-6 h-6" />
                            </button>
                        </template>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2">
                            <!-- <button @click="setGallery('cover')"></button> -->
                            <button @click="setGallery('cover')"
                                :class="['px-3 py-1 rounded text-sm', currentGallery === 'cover' ? 'bg-blue-600 text-white' : 'bg-white/80']">
                                Carro
                            </button>

                            <button v-if="reservation.reservationDocumentsImages.length > 0"
                                @click="setGallery('reservation')"
                                :class="['px-3 py-1 rounded text-sm', currentGallery === 'reservation' ? 'bg-blue-600 text-white' : 'bg-white/80']">
                                Docs Reserva
                            </button>

                            <button v-if="reservation.preReservationImages.length > 0"
                                @click="setGallery('prereservation')"
                                :class="['px-3 py-1 rounded text-sm', currentGallery === 'prereservation' ? 'bg-blue-600 text-white' : 'bg-white/80']">
                                Pre-reserva
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold">{{ reservation.car.title }}</h3>
                            <div class="flex items-center gap-2">
                                <img :src="reservation.car.brand.img" :alt="reservation.car.brand.title"
                                    class="w-8 h-8 object-contain" />
                                <span class="font-medium">{{ reservation.car.brand.title }}</span>
                            </div>
                            <p class="text-gray-600">{{ reservation.car.description }}</p>

                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Placa:</span>
                                    <span class="font-mono font-bold">{{ reservation.car.plate }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span>{{ reservation.car.type.title }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Motor:</span>
                                    <span>{{ reservation.car.engineHp }} HP</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Transmisión:</span>
                                    <span class="capitalize">{{ reservation.car.gear }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Combustible:</span>
                                    <span class="capitalize">{{ reservation.car.fuelType }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Asientos:</span>
                                    <span>{{ reservation.car.totalSeats }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kilometraje:</span>
                                    <span>{{ reservation.car.totalKM.toLocaleString() }} km</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">A/C:</span>
                                    <span>{{ reservation.car.hasAC ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-semibold">Precios</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Renta:</span>
                                    <span class="font-bold">
                                        {{ formatCurrency(reservation.car.rentPrice) }}/
                                        {{ reservation.car.priceType === 'hr' ? 'hora' : 'día' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Con conductor:</span>
                                    <span class="font-bold">{{ formatCurrency(reservation.car.driverRentPrice)
                                        }}</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t">
                                <h4 class="font-semibold mb-2">Características</h4>
                                <div class="flex flex-wrap gap-2">
                                    <!-- <span v-for="(feature, index) in reservation.car.features" :key="index"
                                        class="px-2 py-1 bg-gray-100 rounded text-xs">
                                        {{ feature.slice(0, 8) }}
                                    </span> -->
                                    <span v-for="(feature, index) in reservation.car.features || []" :key="index" class="px-2 py-1 bg-gray-100 rounded text-xs">
                                        {{ feature.slice(0, 8) }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-4 border-t">
                                <h4 class="font-semibold mb-2">Propietario</h4>
                                <div class="space-y-1">
                                    <p class="font-medium">{{ reservation.car.user.name }}</p>
                                    <p class="text-sm text-gray-600">{{ reservation.car.user.email }}</p>
                                    <p class="text-sm text-gray-600">{{ reservation.car.user.ccode }} {{
                                        reservation.car.user.mobile }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--AQUI TERMINA-->
                </div>

                <div v-if="activeTab === 'ubicacion'" class="space-y-6">
                    <!-- Mapa con Vue2Leaflet o similar -->
                </div>

                <div v-if="activeTab === 'documentos'" class="space-y-6">
                    <!-- Documentos de reserva -->
                    <div v-if="reservation.reservationDocumentsImages.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Documentos de reserva</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a v-for="(img, index) in reservation.reservationDocumentsImages" :key="index"
                                :href="img" target="_blank" rel="noopener noreferrer"
                                class="aspect-square bg-gray-100 rounded-lg overflow-hidden hover:opacity-80 transition-opacity">
                                <img :src="img" :alt="`Documento ${index + 1}`"
                                    class="w-full h-full object-cover" />
                            </a>
                        </div>
                    </div>
                    <!-- Imágenes pre-reserva -->
                    <div v-if="reservation.preReservationImages.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Imágenes pre-reserva</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a v-for="(img, index) in reservation.preReservationImages"
                            :key="index"
                            :href="img"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="aspect-square bg-gray-100 rounded-lg overflow-hidden hover:opacity-80 transition-opacity">
                            <img :src="img" :alt="`Pre-reserva ${index + 1}`" class="w-full h-full object-cover" />
                        </a>
                        </div>
                    </div>

                    <!-- Documentos del cliente -->
                    <div v-if="reservation.customerDocuments.length > 0">
                        <h3 class="text-lg font-semibold mb-3">Documentos del cliente</h3>
                        <div class="space-y-2">
                        <a v-for="(doc, index) in reservation.customerDocuments"
                            :key="index"
                            :href="doc"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <FileText class="w-5 h-5 text-gray-600" />
                            <span>Documento {{ index + 1 }}</span>
                            <ExternalLink class="w-4 h-4 ml-auto text-gray-400" />
                        </a>
                        </div>
                    </div>

                    <!-- Mensaje si no hay documentos -->
                    <div v-if="reservation.reservationDocumentsImages.length === 0 &&
                                reservation.preReservationImages.length === 0 &&
                                reservation.customerDocuments.length === 0"
                        class="text-center py-12 text-gray-500">
                        No hay documentos disponibles
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import appService from "../../../services/appService";
import PrintComponent from "../components/buttons/export/PrintComponent";
// import { X, Copy, ExternalLink, User, Car as CarIcon, MapPin, FileText, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import print from "vue3-print-nb";
import { ChevronLeft, ChevronRight, X, ExternalLink,  CarIcon, User, FileText, Copy } from 'lucide-vue-next';

export default {
    name: "ReservationsShowComponent",
    components: {
        LoadingComponent,
        PrintComponent,
        ChevronLeft,
        ChevronRight,
        X,
        ExternalLink,
        User,
        FileText,
        CarIcon,
        Copy
    },
    directives: {
        print
    },
    props: {
        id: String,
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            activeTab: "resumen",
            // tabs: [
            //     { id: "resumen", label: this.$t("label.summary") },
            //     { id: "usuario", label: this.$t("label.user") },
            //     { id: "carro", label: this.$t("label.car") },
            //     // { id: "ubicacion", label: this.$t("label.location") },
            //     { id: "documentos", label: this.$t("label.documents") }
            // ],
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            printObj: {
                id: "print",
                popTitle: this.$t("menu.reservations"),
            },
            currentImageIndex: 0,
            currentGallery: 'cover'
        }
    },
    computed: {
        reservation: function () {
            return this.$store.getters['reservations/show'];
        },
        currentImages() {
            console.log(this.reservation.car);
            if (!this.reservation || !this.reservation.car) return [];
            switch(this.currentGallery) {
                case 'cover': 
                    return this.reservation.car.urlImages || [];
                case 'reservation': 
                    return this.reservation.reservationDocumentsImages || [];
                case 'prereservation': 
                    return this.reservation.preReservationImages || [];
                default: 
                    return [];
            }
        },
        tabs() {
            return [
                { id: "resumen", label: this.$t("label.summary"), icon: FileText },
                { id: "usuario", label: this.$t("label.user"), icon: User },
                { id: "carro", label: this.$t("label.car"), icon: CarIcon },
                // { id: "ubicacion", label: this.$t("label.location") },
                { id: "documentos", label: this.$t("label.documents"), icon: FileText }
            ];
        }
    },
    mounted() {
        if (this.id) {
            this.fetchReservation();
        }
    },
    methods: {
        fetchReservation() {
            this.loading.isActive = true;
            this.$store.dispatch('reservations/show', this.id).then(res => {
                this.loading.isActive = false;
            }).catch((error) => {
                this.loading.isActive = false;
            });
        },
        onClose() {
            this.$emit('close');
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            console.log(`${day}/${month}/${year} ${hours}:${minutes}`);
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },
        formatDatePersonalized(dateString, time){
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            
            const normalizedTime = time.split(':').map(t => t.padStart(2, '0')).join(':');

            return `${day}/${month}/${year} ${normalizedTime}`;

        },
        formatCurrency(amount) {
            return `Q ${amount.toLocaleString('es-GT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        },
        nextImage() {
            this.currentImageIndex = (this.currentImageIndex + 1) % this.currentImages.length;
        },
        prevImage() {
            this.currentImageIndex = (this.currentImageIndex - 1 + this.currentImages.length) % this.currentImages.length;
        },
        setGallery(gallery) {
            this.currentGallery = gallery;
            this.currentImageIndex = 0;
        },
        getStatusLabel(status){
            console.log(status);
            switch (status) {
                case 'completed':
                    return 'Completado';
                case 'pending':
                    return 'Pendiente';
                case 'canceled':
                    return 'Cancelado';
                case 'booked':
                    return 'Reservado';
                default:
                    return status;
            }
        },
        getStatusColor(status){
            switch (status) {
                case 'completed':
                    return 'bg-green-100 text-green-800 border-green-200';
                case 'pending':
                    return 'bg-amber-100 text-amber-800 border-amber-200';
                case 'canceled':
                    return 'bg-red-100 text-red-800 border-red-200';
                case 'booked':
                    return 'bg-blue-100 text-blue-800 border-blue-200';
                default:
                    return 'bg-gray-100 text-gray-800 border-gray-200';
            }
        }
    }
}
</script>
