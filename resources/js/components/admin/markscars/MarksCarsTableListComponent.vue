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
                    <MarksCarsTableCreateComponent :props="props" />
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="size" class="db-field-title after:hidden">{{
                                $t("label.description")
                            }}</label>
                            <input id="size" v-on:keypress="numberOnly($event)" v-model="props.search.description" type="text"
                                class="db-field-control" />
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
                <div class="bg-white rounded-2xl border border-[var(--border-color)] overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[var(--bg)] sticky top-0">
                        <tr>
                            <th
                            v-for="col in columns"
                            :key="col.key"
                            class="text-left py-4 px-6 text-sm font-semibold text-[var(--ink)] cursor-pointer hover:bg-gray-100"
                            @click="col.sortable && handleSort(col.key)"
                            >
                            <div class="flex items-center gap-2">
                                {{ col.label }}
                                <SortIcon v-if="col.sortable" :column="col.key" />
                            </div>
                            </th>
                            <th class="text-center py-4 px-6 text-sm font-semibold text-[var(--ink)]">
                            Acciones
                            </th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr
                            v-for="brand in paginatedBrands"
                            :key="brand.brandId"
                            class="border-t border-[var(--border-color)] hover:bg-[var(--bg)] transition-colors"
                        >
                            <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <img
                                v-if="brand.brandImg"
                                :src="brand.brandImg"
                                :alt="brand.brandTitle"
                                class="w-10 h-10 object-contain"
                                />
                                <span class="font-semibold text-[var(--ink)]">
                                {{ brand.brandTitle }}
                                </span>
                            </div>
                            </td>

                            <td class="py-4 px-6 text-[var(--ink)]">
                            {{ brand.modelos }}
                            </td>

                            <td class="py-4 px-6 text-[var(--ink)]">
                            Q{{ brand.priceMin }}–{{ brand.priceMax }}
                            </td>

                            <td class="py-4 px-6">
                            <div class="flex items-center gap-1">
                                <span>⭐</span>
                                <span class="text-[var(--ink)]">{{ brand.ratingAvg.toFixed(1) }}</span>
                            </div>
                            </td>

                            <td class="py-4 px-6">
                            <div class="flex flex-wrap gap-1">
                                <span
                                v-for="fuel in brand.fuels"
                                :key="fuel"
                                class="px-2 py-1 bg-[var(--bg)] text-[var(--ink)] rounded text-xs capitalize"
                                >
                                {{ fuel }}
                                </span>
                            </div>
                            </td>

                            <td class="py-4 px-6">
                            <div class="flex gap-2 text-xs">
                                <span
                                v-for="(count, status) in brand.publishCounters"
                                :key="status"
                                :class="[
                                    'px-2 py-1 rounded',
                                    status === 'published'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-gray-100 text-gray-800',
                                ]"
                                >
                                {{ count }}
                                </span>
                            </div>
                            </td>

                            <td class="py-4 px-6 text-center">
                            <button
                                @click="setSelectedBrand(brand)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--brand)] text-white rounded-full hover:bg-[var(--brand-700)] transition-colors font-medium"
                                :aria-label="`Visualizar ${brand.brandTitle}`"
                            >
                                <Eye class="w-4 h-4" />
                                Visualizar
                            </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Estado vacío -->
                <div v-if="filteredBrands.length === 0" class="text-center py-12">
                <p class="text-[var(--muted-text)]">
                    No se encontraron marcas con los filtros aplicados
                </p>
                </div>

                <!-- Paginación -->
                <div
                v-if="totalPages > 1"
                class="flex items-center justify-between px-6 py-4 border-t border-[var(--border-color)]"
                >
                <p class="text-sm text-[var(--muted-text)]">
                    Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} a
                    {{ Math.min(currentPage * itemsPerPage, filteredBrands.length) }} de
                    {{ filteredBrands.length }} marcas
                </p>

                <div class="flex gap-2">
                    <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 border border-[var(--border-color)] rounded-lg hover:bg-[var(--bg)] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                    Anterior
                    </button>

                    <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="setCurrentPage(page)"
                    :class="[
                        'px-4 py-2 rounded-lg',
                        page === currentPage
                        ? 'bg-[var(--brand)] text-white'
                        : 'border border-[var(--border-color)] hover:bg-[var(--bg)]',
                    ]"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    >
                    {{ page }}
                    </button>

                    <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 border border-[var(--border-color)] rounded-lg hover:bg-[var(--bg)] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                    Siguiente
                    </button>
                </div>
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
                    branch_id: null,
                    name: "",
                    category: "",
                    status: statusEnum.ACTIVE,
                    description: "",
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    name: "",
                    category: "",
                    status: null,
                }
            },
            demo: ENV.DEMO,
            filters: {
                
            }
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
            this.props.search.name = "";
            this.props.search.category = "";
            this.props.search.status = null;
            this.props.description = "";
            this.list();
        },
        edit: function (mark) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch('marks/edit', mark.id);
            this.props.form = {
                // branch_id: marks.branch_id,
                name: mark.name,
                category: mark.category,
                status: mark.status,
                description: mark.description,
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
