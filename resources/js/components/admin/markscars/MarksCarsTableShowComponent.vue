<template>
    <LoadingComponent :props="loading" />
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-[var(--border-color)]">
                <div class="flex items-center gap-4">
                    <!-- <pre>{{ car.brand }}</pre> -->
                    <img :src="car.brand?.img" :alt="car.brand.title"
                        class="w-12 h-12 object-contain" />
                    <h2 class="text-2xl font-bold text-[var(--ink)]">
                        {{ car.brand.title }}
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="onClose" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-[var(--border-color)] overflow-x-auto">
                <button v-for="tab in tabs" :key="tab.id" @click="setActiveTab(tab.id)" :class="[
                    'px-6 py-3 font-medium transition-colors whitespace-nowrap',
                    activeTab === tab.id
                        ? 'border-b-2 border-[var(--car)] text-[var(--car)]'
                        : 'text-[var(--muted-text)] hover:text-[var(--ink)]'
                ]">
                    {{ tab.label }}
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- ====================== RESUMEN ====================== -->
                <div v-if="activeTab === 'resumen'" class="space-y-6">
                    <!-- KPIs -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-[var(--bg)] rounded-xl p-4">
                            <p class="text-sm text-[var(--muted-text)]">Engranaje</p>
                            <p class="text-2xl font-bold text-[var(--ink)]">
                                {{ setNameGear(car.gear) }}
                            </p>
                        </div>
                        <div class="bg-[var(--bg)] rounded-xl p-4">
                            <p class="text-sm text-[var(--muted-text)]">Rango Precios</p>
                            <p class="text-2xl font-bold text-[var(--ink)]">
                                Q{{ car.rentPrice }}
                            </p>
                        </div>
                        <div class="bg-[var(--bg)] rounded-xl p-4">
                            <p class="text-sm text-[var(--muted-text)]">Rating Promedio</p>
                            <p class="text-2xl font-bold text-[var(--ink)]">
                                ⭐ {{ car.rating ? car.rating.toFixed(1) : '' }}
                            </p>
                        </div>
                        <div class="bg-[var(--bg)] rounded-xl p-4">
                            <p class="text-sm text-[var(--muted-text)]">Combustibles</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="text-xs bg-white px-2 py-1 rounded capitalize">
                                    {{ setNameFuelType(car.fuelType) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Models Grid -->
                    <div>
                        <h3 class="text-lg font-semibold text-[var(--ink)] mb-4">Modelos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- <div v-for="car in brand.cars" :key="car.id" -->
                            <div class="bg-white border border-[var(--border-color)] rounded-xl p-4 hover:shadow-md transition-shadow">
                                <img v-if="car.urlCover" :src="car.urlCover" :alt="car.title"
                                    class="w-full h-32 object-cover rounded-lg mb-3" />
                                <h4 class="font-semibold text-[var(--ink)] mb-1">{{ car.title }}</h4>
                                <p v-if="car.plate" class="text-sm text-[var(--muted-text)] font-mono mb-2">
                                    {{ car.plate }}
                                </p>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-[var(--brand)] font-semibold">
                                        Q{{ car.rentPrice }}/{{ car.priceType }}
                                    </span>
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs',
                                        car.publishStatus === 'published'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ car.publishStatus }}
                                    </span>
                                </div>

                                <button @click="setSelectedCar(car)"
                                    class="w-full mt-3 px-3 py-2 bg-[var(--brand)] text-white rounded-lg hover:bg-[var(--brand-700)] transition-colors text-sm font-medium">
                                    Ver ficha
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====================== MODELOS ====================== -->
                <div v-if="activeTab === 'modelos'" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[var(--border-color)]">
                                <th v-for="head in ['Modelo', 'Tipo', 'HP', 'Asientos', 'Transmisión', 'Combustible', 'Estado', 'Acciones']"
                                    :key="head" class="text-left py-3 px-4 text-sm font-semibold text-[var(--ink)]">
                                    {{ head }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="car in brand.cars" :key="car.id"
                                class="border-b border-[var(--border-color)] hover:bg-[var(--bg)]">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-[var(--ink)]">{{ car.title }}</div>
                                    <div v-if="car.plate" class="text-xs text-[var(--muted-text)] font-mono">
                                        {{ car.plate }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <img v-if="car.type?.img" :src="car.type.img" :alt="car.type.title"
                                            class="w-6 h-6 object-contain" />
                                        <span class="text-sm text-[var(--ink)]">
                                            {{ car.type?.title }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm text-[var(--ink)]">{{ car.engineHp }}</td>
                                <td class="py-3 px-4 text-sm text-[var(--ink)]">{{ car.totalSeats }}</td>
                                <td class="py-3 px-4 text-sm text-[var(--ink)] capitalize">
                                    {{ car.gear }}
                                </td>
                                <td class="py-3 px-4 text-sm text-[var(--ink)] capitalize">
                                    {{ car.fuelType }}
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs',
                                        car.status === 'available'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ car.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <button @click="setSelectedCar(car)"
                                        class="p-2 hover:bg-[var(--bg)] rounded-lg transition-colors"
                                        aria-label="Ver ficha">
                                        <Eye class="w-4 h-4 text-[var(--brand)]" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ====================== MAPA ====================== -->
                <div v-if="activeTab === 'mapa'" class="space-y-4">
                    <div class="h-96 rounded-xl overflow-hidden border border-[var(--border-color)]">
                        <MapContainer :center="[mapCenter.latitude, mapCenter.longitude]" :zoom="12"
                            style="height: 100%; width: 100%">
                            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                attribution="&copy; OpenStreetMap" />
                            <Marker v-for="car in brand.cars" v-if="car.pickLocation?.geoPoint" :key="car.id"
                                :position="[car.pickLocation.geoPoint.latitude, car.pickLocation.geoPoint.longitude]">
                                <Popup>
                                    <div class="text-sm">
                                        <p class="font-semibold">{{ car.title }}</p>
                                        <p v-if="car.plate" class="text-xs font-mono">{{ car.plate }}</p>
                                    </div>
                                </Popup>
                            </Marker>
                        </MapContainer>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-semibold text-[var(--ink)]">Ubicaciones</h4>
                        <div class="space-y-1">
                            <div v-for="car in brand.cars.filter(c => c.pickLocation?.geoPoint)" :key="car.id"
                                class="text-sm text-[var(--muted-text)] hover:text-[var(--ink)] cursor-pointer">
                                {{ car.title }} - {{ car.pickAddress }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====================== CARACTERÍSTICAS ====================== -->
                <!-- <div v-if="activeTab === 'caracteristicas'">
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-4">
                        Características disponibles
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="featureId in allFeatures" :key="featureId"
                            class="px-3 py-2 bg-[var(--bg)] text-[var(--ink)] rounded-lg text-sm">
                            {{ resolveFeatureName(featureId) }}
                        </span>
                    </div>
                    <p v-if="allFeatures.length === 0" class="text-center text-[var(--muted-text)] py-8">
                        No hay características disponibles
                    </p>
                </div> -->
            
                <div v-if="activeTab === 'caracteristicas'">
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-4">
                        Características disponibles
                    </h3>

                    <!-- Si hay características -->
                    <div v-if="car.dataFeatures.length" 
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        
                        <div v-for="feature in car.dataFeatures" :key="feature.id"
                            class="group bg-white rounded-2xl overflow-hidden border border-[var(--border-color)] shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 hover:border-[var(--brand)]">

                            <!-- Imagen -->
                            <div class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-6 overflow-hidden">
                                <img
                                    :src="feature.img || '/images/default-feature.jpg'"
                                    :alt="feature.title"
                                    class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105"
                                />
                            </div>

                            <!-- Título -->
                            <div class="p-4 border-t border-[var(--border-color)]">
                                <h3 class="text-center text-[var(--ink)] font-semibold text-base">
                                    {{ feature.title }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Si no hay características -->
                    <p v-else class="text-center text-[var(--muted-text)] py-8">
                        No hay características disponibles
                    </p>
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
import print from "vue3-print-nb";
import { X } from "lucide-vue-next";

export default {
    name: "MarksCarsTableShowComponent",
    components: {
        LoadingComponent,
        PrintComponent,
        X
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
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            printObj: {
                id: "print",
                popTitle: this.$t("menu.marksCars"),
            },
            activeTab: 'resumen',
            allFeatures: [],
            selectedCar: null,
        }
    },
    computed: {
        car: function () {
            return this.$store.getters['marks/show'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        tabs(){
            return [
                { id: 'resumen', label: 'Resumen' },
                // { id: 'modelos', label: 'Modelos' },
                // { id: 'mapa', label: 'Mapa' },
                { id: 'caracteristicas', label: 'Características' }
            ];
        } 
    },
    mounted() {
        if(this.id){
            this.featchCars();
        }
    },
    methods: {
        featchCars(){
            this.loading.isActive = true;
            this.$store.dispatch('marks/show', this.id).then(res => {
                this.loading.isActive = false;
                this.allFeatures = this.res?.features || []
            }).catch((error) => {
                this.loading.isActive = false;
            });
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        onClose() {
            this.$emit('close');
        },
        setActiveTab(tabId) {
            this.activeTab = tabId
        },
        setSelectedCar(car) {
            this.$emit('open-car-drawer', car)
        },
        resolveFeatureName(featureId) {
            if (!this.setting?.features) return featureId
            const found = this.setting.features.find(f => f.id === featureId)
            return found ? found.name : featureId
        },
        setNameGear(gear){
            switch(gear){
                case 'manual':
                    return 'Manual';
                case 'automatic':
                    return 'Automatico';
                default:
                    return 'Manuel';
            }
        },
        setNameFuelType(fuelType){
            switch(fuelType){
                case 'electric':
                    return 'Electrico';
                case 'gasoline':
                    return 'Gasolina';
                case 'diesel':
                    return 'Disel';
                default:
                    return 'Disel';
            }
        }
    }
}
</script>
