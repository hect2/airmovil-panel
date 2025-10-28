<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.marksCars') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <!-- <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                        </div>
                    </div> -->
                    <!-- <MarksCarsTableCreateComponent :props="props" /> -->
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="fulType" class="db-field-title after:hidden">{{
                                $t("label.typeGasoline")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="fuelType"
                                v-model="props.search.fuelType" :options="fuelType" label-by="name" value-by="value" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />

                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="gear" class="db-field-title after:hidden">{{
                                $t('label.Gear')
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="gear"
                                v-model="props.search.gear" :options="transmision" label-by="name"
                                value-by="value" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="hasAc" class="db-field-title after:hidden">{{
                                $t('label.ac-sin-ac')
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="hasAc"
                                v-model="props.search.hasAc" :options="Ac" label-by="name"
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
                <div class="hidden md:block bg-white rounded-lg overflow-hidden shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-2">Marca<SortIcon column="brand" /></div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-2">Engranaje/Transmision<SortIcon column="modelos" /></div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" >
                                        <div class="flex items-center gap-2">Precio Renta<SortIcon column="priceMin" /></div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center gap-2">Rating<SortIcon column="rating" /></div>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Combustible</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estados</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="brand in marks"
                                    :key="brand.id"
                                    class="border-t border-[var(--border-color)] hover:bg-[var(--bg)] transition-colors"
                                >
                                    <td class="py-4 px-6">
                                        <!-- <pre>{{ brand.brand }}</pre> -->
                                        <div class="flex items-center gap-3">
                                            <img
                                                :src="brand.brand.img"
                                                :alt="brand.brand.title"
                                                class="w-10 h-10 object-contain"
                                            />
                                            <span class="font-semibold text-[var(--ink)]">{{ brand.title }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-[var(--ink)]">{{ setNameGear(brand.gear) }}</td>

                                    <td class="py-4 px-6 text-[var(--ink)]">
                                        <!-- Q{{ brand.priceMin }}–{{ brand.priceMax }} -->
                                        Q{{ brand.rentPrice }}
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-1">
                                            <span>⭐</span>
                                            <span class="text-[var(--ink)]">{{ brand.rating ? brand.rating.toFixed(1) : '' }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-1 bg-[var(--bg)] text-[var(--ink)] rounded text-xs capitalize">
                                            {{ setNameFuelType(brand.fuelType) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6">
                                        <div class="flex gap-2 text-xs">
                                            <span
                                                :class="[
                                                    'px-2 py-1 rounded',
                                                    brand.status === 'available'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                                ]"
                                                >
                                                {{ setNameStatus(brand.status) }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center gap-2">
                                            <button @click="view(brand)" v-if="permissionChecker('marksCars_show')" class="db-table-action view">
                                                <i class="lab lab-view"></i>
                                                <span class="db-tooltip">{{ $t('button.view') }}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
                <!--termina table-->

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cars -->
    <MarksCarsTableShowComponent
        v-if="showModal"
        :id="seletedMark"
        @close="closeModal"
        @open-car-drawer="openCarDrawer"
    />

    <MarksModelDrawerComponent
        v-if="showDrawer"
        :car="selectedCar"
        @close="showDrawer = false"
    />
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import MarksCarsTableCreateComponent from "./MarksCarsTableCreateComponent";
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
import ENV from "../../../config/env";
import MarksCarsTableShowComponent from "./MarksCarsTableShowComponent.vue";
import MarksModelDrawerComponent from "./MarksModelDrawerComponent.vue";

export default {
    name: "MarksCarsTableListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        MarksCarsTableCreateComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconSidebarModalEditComponent,
        SmIconQrCodeComponent,
        SmIconViewComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        FilterComponent,
        MarksCarsTableShowComponent,
        MarksModelDrawerComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.marksCars"),
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
                    fuelType: null,
                    gear: null,
                    hasAc: null
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    fuelType: null,
                    gear: null,
                    hasAc: null
                }
            },
            demo: ENV.DEMO,
            filters: {
                fuelType: null,
                gear: null,
                hasAc: null
            },
            showModal: false,
            seletedMark: null,
            showDrawer: false,
            selectedCar: null,
            fuelType: [
                { value: 'electric', name: 'Electrico' },
                { value: 'gasoline', name: 'Gasolina' },
                { value: 'diesel', name: 'Disel' }
            ],
            transmision: [
                { value: 'automatic', name: 'Automatico' },
                { value: 'manual', name: 'Mecanico' }
            ],
            Ac: [
                { value: 1, name: 'Si' },
                { value: 2, name: 'No' }
            ],
        }
    },
    computed: {
        marks: function () {
            return this.$store.getters['marks/lists'];
        },
        pagination: function () {
            return this.$store.getters['marks/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['marks/page'];
        }
    },
    mounted() {
        this.list();
    },
    methods: {
        view(brand) {
            this.seletedMark = brand.id;
            appService.sideDrawerShow();
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.seletedMark = null;
        },
        openCarDrawer(car) {
            console.log("CAR DRAWER");
            this.selectedCar = car
            this.showDrawer = true
        },
        closeDrawer() {
            this.showDrawer = false
            this.selectedCar = null
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
            this.$store.dispatch('marks/lists', this.props.search).then(res => {
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
            this.props.search.fuelType = "";
            this.props.search.gear = "";
            this.props.search.hasAc = "";
            this.list();
        },
        edit: function (mark) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch('marks/edit', mark.id);
            this.props.form = {
                // branch_id: marks.branch_id,
                // name: mark.name,
                // category: mark.category,
                // status: mark.status,
                // description: mark.description,
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('marks/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.marksCars'));
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
            this.$store.dispatch("marks/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.marksCars");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        setNameStatus(status){
            switch(status){
                case 'available':
                    return 'Disponible';
                case 'published':
                    return 'Publicado';
                default:
                    return 'Pendiente';
            }
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

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
