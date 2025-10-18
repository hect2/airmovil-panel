<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.items') }}</h3>
                <div class="db-card-filter">
                    <!-- <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                        </div>
                    </div> -->
                    <ItemCreateComponent :props="props" v-if="permissionChecker('items_create')" />
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

            <!-- PRODUCTOS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="relative bg-white rounded overflow-hidden shadow"
                >
                    <!-- Imagen del producto -->
                    <img
                    :src="item.img"
                    alt="Producto"
                    class="w-full h-64 object-cover"
                    />

                    <!-- Botones de acción en la esquina superior derecha -->
                    <div
                    v-if="permissionChecker('items_edit') || permissionChecker('items_delete')"
                    class="absolute top-2 right-2 flex space-x-2"
                    >
                    <SmIconSidebarModalEditComponent
                        @click="edit(item)"
                        v-if="permissionChecker('items_edit')"
                    />
                    <SmIconDeleteComponent
                        @click="destroy(item.id)"
                        v-if="permissionChecker('items_delete')"
                    />
                    </div>
                </div>
            </div>
            <!--------------- -->

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
import ItemCreateComponent from "./ItemCreateComponent";
import alertService from "../../../services/alertService";
import statusEnum from "../../../enums/modules/statusEnum";
import askEnum from "../../../enums/modules/askEnum";
import itemTypeEnum from "../../../enums/modules/itemTypeEnum";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "ItemListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ItemCreateComponent,
        LoadingComponent,
        SmIconSidebarModalEditComponent,
        SmIconDeleteComponent,
        SmIconViewComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                statusEnum: statusEnum,
                itemTypeEnum: itemTypeEnum,
                askEnum: askEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.items"),
            },
            taxProps: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc'
                }
            },
            categoryProps: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc'
                }
            },
            props: {
                form: {
                    img: "",
                    // price: "",
                    // description: "",
                    // caution: "",
                    // is_featured: askEnum.YES,
                    // item_type: itemTypeEnum.VEG,
                    // item_category_id: null,
                    // tax_id: null,
                    // status: statusEnum.ACTIVE,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    title: "",
                    // price: "",
                    // item_category_id: null,
                    // status: null,
                    // tax_id: null,
                    // item_type: null,
                    // is_featured: null
                }
            }
        }
    },
    mounted() {
        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;
        this.$store.dispatch('itemCategory/lists', this.categoryProps.search).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
        this.$store.dispatch('tax/lists', this.taxProps.search).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    computed: {
        items: function () {
            return this.$store.getters['item/lists'];
        },
        pagination: function () {
            return this.$store.getters['item/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['item/page'];
        },
        itemCategories: function () {
            return this.$store.getters["itemCategory/lists"];
        },
        taxes: function () {
            return this.$store.getters['tax/lists'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },

    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        numberOnly: function (e) {
            return appService.floatNumber(e);
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
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('item/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (item) {
            console.log("ITEM", item);
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch('item/edit', item.id);
            this.loading.isActive = false;
            this.props.errors = {};
            this.props.form = {
                img: item.img
            };
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('item/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.items'));
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
            this.$store.dispatch("item/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.items");
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
