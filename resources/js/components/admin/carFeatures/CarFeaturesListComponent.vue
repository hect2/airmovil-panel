<template>
    <LoadingComponent :props="loading" />

    <div class="col-12">
        <div class="db-card p-3">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.carFeatures') }}</h3>
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
                    <!-- <CarFeaturesCreateComponent :props="props" /> -->
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="title" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="title" v-model="props.search.title" type="text" class="db-field-control" />
                        </div>
                        <!-- <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="size" class="db-field-title after:hidden">{{
                                $t("label.description")
                            }}</label>
                            <input id="size" v-on:keypress="numberOnly($event)" v-model="props.search.description" type="text"
                                class="db-field-control" />
                        </div> -->

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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="carFeature in carFeatures"
                    :key="carFeature.id"
                    class="group relative bg-white rounded-2xl overflow-hidden border-2 border-[var(--border-color)] shadow-sm hover:shadow-xl hover:border-red-500 hover:shadow-red-100 transition-all duration-300 hover:-translate-y-1 transition-all duration-300 hover:text-red-500 hover:scale-105 hover:decoration-red-500"
                >
                    <!-- Imagen de ejemplo (puedes reemplazar 'diningTable.img' si tienes campo de imagen) -->
                    <div
                    class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-6 overflow-hidden"
                    >
                    <img
                        :src="carFeature.img || '/images/default-table.jpg'"
                        alt="Mesa"
                        class="w-full h-full object-contain"
                    />
                    </div>

                    <!-- Contenido de la tarjeta -->
                    <div class="p-4 border-t border-[var(--border-color)]">
                        <!-- Título -->
                        <h3 class="text-lg font-semibold text-[var(--ink)] text-center">
                            {{ carFeature.title }}
                        </h3>

                    </div>

                    <!-- Botones de acción -->
                    <div
                        v-if="permissionChecker('car_feature_show') || permissionChecker('car_feature_edit') || permissionChecker('car_feature_delete')"
                        class="absolute top-2 right-2 flex space-x-2"
                    >
                        <!-- <SmIconViewComponent
                            :link="'admin.diningTable.show'"
                            :id="diningTable.id"
                            v-if="permissionChecker('car_feature_show')"
                        /> -->
                        <!-- <SmIconSidebarModalEditComponent
                            @click="edit(carFeature)"
                            v-if="permissionChecker('car_feature_edit')"
                        /> -->
                        <!-- <SmIconDeleteComponent
                            @click="destroy(carFeature.id)"
                            v-if="permissionChecker('car_feature_delete') && demoChecker(carFeature.id)"
                        /> -->
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
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
// import CarFeaturesCreateComponent from "./CarFeaturesCreateComponent";
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

export default {
    name: "CarFeaturesListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmIconDeleteComponent,
        SmIconSidebarModalEditComponent,
        SmIconQrCodeComponent,
        SmIconViewComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        FilterComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.carFeatures"),
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
                    title: ""
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    title: ""
                }
            },
            demo: ENV.DEMO
        }
    },
    computed: {
        carFeatures: function () {
            return this.$store.getters['carFeatures/lists'];
        },
        pagination: function () {
            return this.$store.getters['carFeatures/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['carFeatures/page'];
        }
    },
    mounted() {
        this.list();
    },
    methods: {
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
            this.$store.dispatch('carFeatures/lists', this.props.search).then(res => {
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
            this.props.search.title = "";
            this.list();
        },
        edit: function (carFeature) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch('carFeatures/edit', carFeature.id);
            this.props.form = {
                title: carFeature.title,
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('carFeatures/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.carFeatures'));
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
            this.$store.dispatch("carFeatures/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.carFeatures");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
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
