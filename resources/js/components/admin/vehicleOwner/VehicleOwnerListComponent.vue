<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.vehicleOwner") }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                        </div>
                    </div>
                    <VehicleOwnerCreateComponent :props="props" v-if="permissionChecker('customers_edit')" />
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchName" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="searchName" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchEmail" class="db-field-title after:hidden">{{
                                $t("label.email")
                            }}</label>
                            <input id="searchEmail" v-model="props.search.email" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchPhone" class="db-field-title after:hidden">{{
                                $t("label.phone")
                            }}</label>
                            <input id="searchPhone" v-model="props.search.phone" v-on:keypress="phoneNumber($event)"
                                type="text" class="db-field-control" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.status"
                                :options="[{ id: enums.statusCustomerEnum.ACTIVE, name: $t('label.active') }, { id: enums.statusCustomerEnum.PENDING, name: $t('label.pending') }, { id: enums.statusCustomerEnum.INACTIVE, name: $t('label.inactive') },]"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
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
                <table class="db-table stripe" id="print">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t("label.name") }}</th>
                            <th class="db-table-head-th">{{ $t("label.email") }}</th>
                            <th class="db-table-head-th">{{ $t("label.phone") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('vehicleOwner_show') || permissionChecker('vehicleOwner_edit') || permissionChecker('vehicleOwner_delete')">
                                {{ $t("label.action") }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="vehicleOwners.length > 0">
                        <tr class="db-table-body-tr" v-for="customer in vehicleOwners" :key="customer">
                            <td class="db-table-body-td">
                                {{ textShortener(customer.name, 20) }}
                            </td>
                            <td class="db-table-body-td">
                                {{ customer.email }}
                            </td>
                            <td class="db-table-body-td">
                                {{ customer.country_code + '' + customer.phone }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(customer.status)">
                                    {{ enums.statusCustomerEnumArray[customer.status] }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('customers_show') || permissionChecker('customers_edit') || permissionChecker('customers_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <!-- <SmIconViewComponent :link="'admin.vehicleOwner.show'" :id="customer.id"
                                        v-if="permissionChecker('vehicleOwner_show')" /> -->
                                    <SmIconSidebarModalEditComponent @click="edit(customer)"
                                        v-if="permissionChecker('customers_edit')" />
                                    <!-- <SmIconDeleteComponent @click="destroy(customer.id)"
                                        v-if="customer.id !== 2 && permissionChecker('vehicleOwner_delete')" /> -->

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
import VehicleOwnerCreateComponent from "./VehicleOwnerCreateComponent.vue";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import statusCustomerEnum from "../../../enums/modules/statusCustomerEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import print from "vue3-print-nb";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";

export default {
    name: "VehicleOwnerComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        VehicleOwnerCreateComponent,
        LoadingComponent,
        SmIconViewComponent,
        SmIconSidebarModalEditComponent,
        SmIconDeleteComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusCustomerEnum: statusCustomerEnum,
                statusCustomerEnumArray: {
                    [statusCustomerEnum.ACTIVE]: this.$t("label.active"),
                    [statusCustomerEnum.INACTIVE]: this.$t("label.inactive"),
                    [statusCustomerEnum.PENDING]: this.$t("label.pending"),
                },
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.vehicleOwner"),
            },
            props: {
                form: {
                    name: "",
                    email: "",
                    phone: "",
                    country_code: "",
                    status: statusCustomerEnum.ACTIVE,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                    name: "",
                    email: "",
                    phone: "",
                    status: null,
                },
            },
            country_code: "",
        };
    },
    mounted() {
        this.list();
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch('company/lists').then(companyRes => {
            this.loading.isActive = true;
            this.$store.dispatch('countryCode/show', companyRes.data.data.company_country_code).then(res => {
                this.country_code = res.data.data.calling_code;
                this.loading.isActive = false;

            }).catch((err) => {
                this.loading.isActive = false;

            });
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    computed: {
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
        vehicleOwners: function () {
            return this.$store.getters["vehicleOwners/lists"];
        },
        pagination: function () {
            return this.$store.getters["vehicleOwners/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["vehicleOwners/page"];
        },
        countryCode: function () {
            return this.$store.getters['countryCode/show'];
        }
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusCustomerClass(status);
        },
        phoneNumber(e) {
            return appService.phoneNumber(e);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = "";
            this.props.search.email = "";
            this.props.search.phone = "";
            this.props.search.status = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("vehicleOwners/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        edit: function (vehicleOwner) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store
                .dispatch("vehicleOwners/edit", vehicleOwner.id)
                .then((res) => {
                    this.loading.isActive = false;
                    this.props.errors = {};
                    this.props.form = {
                        name: vehicleOwner.name,
                        email: vehicleOwner.email,
                        phone: vehicleOwner.phone,
                        status: vehicleOwner.status,
                        country_code: this.country_code,
                        nit: vehicleOwner.nit,
                        lastName: vehicleOwner.lastName,
                        documentType: vehicleOwner.documentType,
                        documentId: vehicleOwner.documentId,
                        birthDate: vehicleOwner.birthDate,
                        country: vehicleOwner.country,
                        mobile: vehicleOwner.mobile,
                        gender: vehicleOwner.gender,
                        zone: vehicleOwner.zone,
                        address: vehicleOwner.address,
                        department: vehicleOwner.department,
                        municipality: vehicleOwner.municipality,
                    };
                })
                .catch((err) => {
                    alertService.error(err.response.data.message);
                });
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("vehicleOwners/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.vehicleOwner"));
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("vehicleOwners/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.vehicleOwner");
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
    },
};
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
