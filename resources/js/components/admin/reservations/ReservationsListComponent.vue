<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.reservations') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <!-- <PolizesCreateComponent :props="props" /> -->
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status" :options="statusReservations" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />

                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="paymentMethod" class="db-field-title after:hidden">{{
                                $t('label.payment_method')
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="paymentMethod"
                                v-model="props.search.paymentMethod" :options="paymentMethod" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="bookedWithDriver" class="db-field-title after:hidden">{{
                                $t('label.dryver')
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="bookedWithDriver"
                                v-model="props.search.bookedWithDriver" :options="bookedWithDriver" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="db-table-responsive">
                <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carro</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conductor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            <tr
                            v-for="reservation in reservations"
                            :key="reservation.id"
                            class="hover:bg-gray-50 transition-colors"
                            >
                            <td class="px-4 py-3 font-mono text-sm">
                                {{ reservation.id.slice(0, 8) }}...
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
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
                                <div class="text-gray-500">{{ reservation.car.brand.title }}</div>
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
                                <span
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-700': reservation.status === 'completed',
                                    'bg-yellow-100 text-yellow-700': reservation.status === 'pending',
                                    'bg-red-100 text-red-700': reservation.status === 'cancelled'
                                }"
                                >
                                {{ reservation.status }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="view(reservation)" v-if="permissionChecker('polizes_show')" class="db-table-action view">
                                        <i class="lab lab-view"></i>
                                        <span class="db-tooltip">{{ $t('button.view') }}</span>
                                    </button>
                                </div>
                            </td>
                            </tr>

                            <tr v-if="reservations.length === 0">
                            <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                No se encontraron reservas.
                            </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>

                <!-- 📱 MOBILE CARDS -->
                <div class="md:hidden space-y-4 mt-6">
                    <div
                        v-for="reservation in reservations"
                        :key="reservation.id"
                        class="bg-white rounded-lg shadow p-4 space-y-3"
                    >
                        <div class="flex items-start justify-between">
                        <div>
                            <p class="font-mono text-sm text-gray-600">
                            {{ reservation.id.slice(0, 8) }}...
                            </p>
                            <p class="text-sm text-gray-500">{{ formatDate(reservation.createdAt) }}</p>
                        </div>
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                            :class="{
                            'bg-green-100 text-green-700': reservation.status === 'completed',
                            'bg-yellow-100 text-yellow-700': reservation.status === 'pending',
                            'bg-red-100 text-red-700': reservation.status === 'cancelled'
                            }"
                        >
                            {{ reservation.status }}
                        </span>
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
                            <p class="font-bold text-lg">{{ formatCurrency(reservation.totalAmount) }}</p>
                        </div>
                        </div>

                        <!-- <SmIconViewComponent @click="view(reservation)" @close="showModal = false" :id="reservation.id"
                                        v-if="permissionChecker('polizes_show')" /> -->
                        <!-- Botón modal, sin afectar la URL -->
                        <button @click="view(reservation)" v-if="permissionChecker('polizes_show')" class="db-table-action view">
                            <i class="lab lab-view"></i>
                            <span class="db-tooltip">{{ $t('button.view') }}</span>
                        </button>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de reserva -->
    <ReservationsShowComponent
        v-if="showModal"
        :id="selectedReservation"
        @close="closeModal"
    />
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
// import PolizesCreateComponent from "./PolizesCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import SmIconQrCodeComponent from "../components/buttons/SmIconQrCodeComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ReservationsShowComponent from "./ReservationsShowComponent.vue";
import ENV from "../../../config/env";

export default {
    name: "ReservationsListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        // PolizesCreateComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconSidebarModalEditComponent,
        SmIconQrCodeComponent,
        SmIconViewComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        FilterComponent,
        ReservationsShowComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.reservations"),
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            props: {
                form: {
                    status: "",
                    paymentMethod: "",
                    bookedWithDriver: ""
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    status: null,
                    paymentMethod: null,
                    bookedWithDriver: null
                }
            },
            demo: ENV.DEMO,
            showModal: false,
            selectedReservation: null,
            statusReservations: [
                { id: 'completed', name: 'Completado' },
                { id: 'pending', name: 'Pendiente' },
                { id: 'cancelled', name: 'Cancelado' },
                { id: 'booked', name: 'Reservado' },
            ],
            paymentMethod: [
                { id: 'Credit Card', name: 'Tarjeta de credito' },
                { id: 'Cash', name: 'Efectivo' },
                { id: 'Wallet', name: 'Billetera' },
            ],
            bookedWithDriver: [
                { id: 'true', name: 'Si' },
                { id: 'false', name: 'No' },
            ]
        }
    },
    computed: {
        reservations: function () {
            return this.$store.getters['reservations/lists'];
        },
        pagination: function () {
            return this.$store.getters['reservations/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['reservations/page'];
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        view(reservation) {
            this.selectedReservation = reservation.id;
            appService.sideDrawerShow();
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedReservation = null; // Limpiamos selección
        },
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        demoChecker: function (tableId) {
          return ((this.demo === 'true' || this.demo === 'TRUE' || this.demo === '1' || this.demo === 1) && tableId !== 1 && tableId !== 2)
           || this.demo === 'false' || this.demo === 'FALSE' || this.demo === "";
        },
        numberOnly: function (e) {
            return appService.floatNumber(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('reservations/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.status = "";
            this.props.search.paymentMethod = "";
            this.props.search.bookedWithDriver = "";
            this.list();
        },
        edit(reservation) {
            appService.sideDrawerShow();
            this.loading.isActive = true;

            this.$store.dispatch('reservations/edit', reservation.id);
            
            const formatDate = (dateStr) => dateStr ? dateStr.split('T')[0] : '';

            this.props.form = {
                bookedWithDriver: reservation.bookedWithDriver,
                status: reservation.status,
                paymentMethod: reservation.paymentMethod
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('reservations/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.reservations'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("reservations/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.reservations");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        formatDate(dateISO) {
            if (!dateISO) return '';

            const date = new Date(dateISO);
            const dia = date.getDate().toString().padStart(2, '0');
            const mes = (date.getMonth() + 1).toString().padStart(2, '0');
            const anio = date.getFullYear();
            const hora = date.getHours().toString().padStart(2, '0');
            const minutos = date.getMinutes().toString().padStart(2, '0');

            return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
        },
        formatCurrency(value) {
            if (value == null) return '-';
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'USD'
            }).format(value);
        },
    }
}
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
