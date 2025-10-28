<template>
    <LoadingComponent :props="loading" />
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end md:items-center justify-end">
        <!-- Overlay para cerrar -->
        <!-- <div class="fixed inset-0" @click="onClose" aria-label="Cerrar drawer" /> -->

        <div
            class="relative bg-white w-full md:w-[600px] h-full md:h-[90vh] md:rounded-l-2xl shadow-2xl overflow-y-auto">
            <!-- Header -->
            <div
                class="sticky top-0 bg-white border-b border-[var(--border-color)] p-4 flex items-center justify-between z-10">
                <h2 class="text-xl font-bold text-[var(--ink)]">{{ car.title }}</h2>
                <button @click="onClose" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Gallery -->
                <div v-if="images.length > 0" class="relative">
                    <div class="aspect-video bg-gray-100 rounded-xl overflow-hidden">
                        <img :src="images[currentImageIndex]" :alt="`${car.title} - Imagen ${currentImageIndex + 1}`"
                            class="w-full h-full object-cover" />
                    </div>

                    <template v-if="images.length > 1">
                        <button @click="prevImage"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow-lg"
                            aria-label="Imagen anterior">
                            <ChevronLeft class="w-5 h-5" />
                        </button>

                        <button @click="nextImage"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow-lg"
                            aria-label="Siguiente imagen">
                            <ChevronRight class="w-5 h-5" />
                        </button>

                        <div
                            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                            {{ currentImageIndex + 1 }} / {{ images.length }}
                        </div>
                    </template>
                </div>

                <!-- Specifications -->
                <div>
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-3">Especificaciones</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-if="car.engineHp" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Motor</p>
                            <p class="font-semibold text-[var(--ink)]">{{ car.engineHp }} HP</p>
                        </div>

                        <div v-if="car.gear" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Transmisión</p>
                            <p class="font-semibold text-[var(--ink)] capitalize">
                                {{ car.gear === 'automatic' ? 'Automática' : 'Manual' }}
                            </p>
                        </div>

                        <div v-if="car.fuelType" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Combustible</p>
                            <p class="font-semibold text-[var(--ink)] capitalize">{{ car.fuelType }}</p>
                        </div>

                        <div v-if="car.totalSeats" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Asientos</p>
                            <p class="font-semibold text-[var(--ink)]">{{ car.totalSeats }}</p>
                        </div>

                        <div v-if="car.totalKM != null" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Kilometraje</p>
                            <p class="font-semibold text-[var(--ink)]">{{ car.totalKM.toLocaleString() }} km</p>
                        </div>

                        <div class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Aire Acondicionado</p>
                            <p class="font-semibold text-[var(--ink)]">{{ car.hasAC ? 'Sí' : 'No' }}</p>
                        </div>

                        <div v-if="car.rentPrice" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Precio Renta</p>
                            <p class="font-semibold text-[var(--ink)]">
                                Q {{ car.rentPrice }}/{{ car.priceType || 'día' }}
                            </p>
                        </div>

                        <div v-if="car.driverRentPrice" class="bg-[var(--bg)] rounded-lg p-3">
                            <p class="text-xs text-[var(--muted-text)]">Con Conductor</p>
                            <p class="font-semibold text-[var(--ink)]">Q {{ car.driverRentPrice }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="car.description">
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-2">Descripción</h3>
                    <p class="text-[var(--muted-text)]" :class="{ 'line-clamp-3': !showFullDescription }">
                        {{ car.description }}
                    </p>

                    <button v-if="car.description.length > 150" @click="toggleDescription"
                        class="text-[var(--brand)] text-sm font-medium mt-2 hover:underline">
                        {{ showFullDescription ? 'Ver menos' : 'Ver más' }}
                    </button>
                </div>

                <!-- Owner -->
                <div v-if="car.user">
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-3">Propietario</h3>
                    <div class="bg-[var(--bg)] rounded-lg p-4 space-y-2">
                        <p v-if="car.user.name" class="font-semibold text-[var(--ink)]">
                            {{ car.user.name }}
                        </p>

                        <a v-if="car.user.email" :href="`mailto:${car.user.email}`"
                            class="flex items-center gap-2 text-[var(--brand)] hover:underline">
                            <Mail class="w-4 h-4" />
                            <span class="text-sm">{{ car.user.email }}</span>
                        </a>

                        <a v-if="car.user.mobile" :href="`tel:${car.user.ccode || ''}${car.user.mobile}`"
                            class="flex items-center gap-2 text-[var(--brand)] hover:underline">
                            <Phone class="w-4 h-4" />
                            <span class="text-sm">{{ car.user.ccode }} {{ car.user.mobile }}</span>
                        </a>
                    </div>
                </div>

                <!-- Location -->
                <div v-if="car.pickAddress">
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-3">Ubicación</h3>
                    <div class="bg-[var(--bg)] rounded-lg p-4 space-y-2">
                        <div class="flex items-start gap-2">
                            <MapPin class="w-4 h-4 text-[var(--brand)] mt-1 flex-shrink-0" />
                            <p class="text-sm text-[var(--ink)]">{{ car.pickAddress }}</p>
                        </div>

                        <p v-if="car.pickLocation && car.pickLocation.geoHash" class="text-xs text-[var(--muted-text)]">
                            GeoHash: {{ car.pickLocation.geoHash }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import { ChevronLeft, ChevronRight, X } from 'lucide-vue-next';

export default {
    name: "MarksModelDrawerComponent",
    components: {
        LoadingComponent,
        X,
        ChevronLeft,
        ChevronRight
    },
    directives: {
        print
    },
    data() {
        return {
            // car: {},
            images: [],
            currentImageIndex: 0,
            showFullDescription: false
        }
    },
    props: {
        car: {
            type: Object,
            required: true
        }
    },
    mounted() {
        if (this.car.urlImages && this.car.urlImages.length > 0) {
            this.images = this.car.urlImages;
        }
    },
    methods: {
        onClose() {
            this.$emit('close')
        },
        nextImage() {
            if (this.images.length)
                this.currentImageIndex = (this.currentImageIndex + 1) % this.images.length
        },
        prevImage() {
            if (this.images.length)
                this.currentImageIndex =
                    (this.currentImageIndex - 1 + this.images.length) % this.images.length
        },
        toggleDescription() {
            this.showFullDescription = !this.showFullDescription
        }
    }
}
</script>