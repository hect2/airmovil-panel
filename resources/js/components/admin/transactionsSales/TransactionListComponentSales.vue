<template>
    <div class="row">
        <div class="col-12">
            <BreadcrumbComponent />
        </div>
        <LoadingComponent :props="loading" />
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.transactions') }}</h3>
                    <div class="db-card-filter">
                        <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                        <FilterComponent />
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list">
                                <PrintComponent :props="printObj" />
<!--                                <ExcelComponent :method="xls" />-->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-filter-div">
                    <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                        <div class="row">
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="transaction_id" class="db-field-title after:hidden">{{
                                    $t('label.transaction_id')
                                }}</label>
                                <input id="transaction_id" v-model="props.search.request_id" type="text"
                                    class="db-field-control">
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="status_transaction" class="db-field-title after:hidden">{{ $t('label.status_transaction') }}</label>
                                <select id="status_transaction" v-model="props.search.status_transaction" class="db-field-control">
                                    <option value="">Todas</option>
                                    <option value="ACCEPT">Aceptadas</option>
                                    <option value="REJECT">Rechazadas</option>
                                    <option value="REVERSE">Anulada</option>
                                </select>
                            </div>

<!--                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">-->
<!--                                <label for="payment" class="db-field-title after:hidden">{{-->
<!--                                    $t('label.payment')-->
<!--                                }}</label>-->
<!--                                <vue-select class="db-field-control f-b-custom-select" id="payment"-->
<!--                                    v-model="props.search.payment" :options="paymentGateways" label-by="name"-->
<!--                                    value-by="slug" :closeOnSelect="true" :searchable="true" :clearOnClose="true"-->
<!--                                    placeholder="&#45;&#45;" search-placeholder="&#45;&#45;" />-->
<!--                            </div>-->

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchStartDate" class="db-field-title after:hidden">{{
                                    $t('label.date')
                                }}</label>
                                <Datepicker hideInputIcon autoApply :enableTimePicker="false" utc="false"
                                    @update:modelValue="handleDate" v-model="props.form.date" range
                                    :preset-ranges="presetRanges">
                                    <template #yearly="{ label, range, presetDateRange }">
                                        <span @click="presetDateRange(range)">{{ label }}</span>
                                    </template>
                                </Datepicker>
                            </div>

                            <div class="col-12">
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <button class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-search-line lab-font-size-16"></i>
                                        <span>{{ $t('button.search') }}</span>
                                    </button>
                                    <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                        <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                        <span>{{ $t('button.clear') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="db-table-responsive">
                    <table class="db-table stripe" id="print" :dir="direction">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th">{{ $t('label.transaction_id') }}</th>
                                <th class="db-table-head-th">{{ $t('label.customer') }}</th>
                                <th class="db-table-head-th">{{ $t('label.date') }}</th>
                                <th class="db-table-head-th">{{ $t('label.payment_method') }}</th>
                                <th class="db-table-head-th">{{ $t('label.status_transaction') }}</th>
                                <th class="db-table-head-th">{{ $t('label.total') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="transactions.length > 0">
                            <tr class="db-table-body-tr" v-for="transaction in transactions" :key="transaction">
                                <td class="db-table-body-td">
                                    {{ transaction.request_id }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.client_name }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.date }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.payment }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.status_transaction }}
                                </td>
                                <td class="db-table-body-td">
                                    <span class="text-[#2AC769]" v-if="transaction.status_transaction == 'Aceptada'">
                                        {{ transaction.sign }} {{ transaction.amount }}
                                    </span>
                                    <span class="text-[#FB4E4E]" v-else>
                                        {{ transaction.sign }} {{ transaction.amount }}
                                    </span>
                                </td>
                                <td class="db-table-body-td hidden-print">
                                    <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
<!--                                        <SmIconSidebarModalEditComponent @click="edit(transaction)"/>-->
<!--                                        <SmIconViewComponent :link="'admin.transactionsSales.show'" :id="transaction.id"/>-->

                                        <button class="db-table-action view" @click="show(transaction)">
                                            <i class="lab lab-view"></i>
                                            <span class="db-tooltip">{{ $t('button.view') }}</span>
                                        </button>

                                        <button class="db-table-action delete" @click="destroy(transaction)"  v-if="transaction.status_transaction == 'Aceptada'">
                                            <i class="lab lab-undo"></i>
                                            <span class="db-tooltip">{{ $t('button.reverse') }}</span>
                                        </button>
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
    </div>
    <div class="modal" id="transaction-modal">
        <div class="modal-content"></div>
    </div>
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import BreadcrumbComponent from "../components/BreadcrumbComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent.vue";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent.vue";

export default {
    name: "TransactionListComponentSales",
    components: {
        SmIconDeleteComponent, SmIconSidebarModalEditComponent,
        SmIconViewComponent,
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
        BreadcrumbComponent
    },
    setup() {
        const date = ref();

        const presetRanges = ref([
            { label: 'Today', range: [new Date(), new Date()] },
            { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: 'Last month',
                range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
            },
            { label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
            {
                label: 'This year (slot)',
                range: [startOfYear(new Date()), endOfYear(new Date())],
                slot: 'yearly',
            },
        ]);

        return {
            date,
            presetRanges,
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {},
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.transactions"),
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: "desc",
                    branch_id: null,
                    status_transaction: "",
                    request_id: "",
                    client_name: "",
                    payment: null,
                    from_date: "",
                    to_date: ""
                }
            }
        }
    },
    mounted() {
        this.$store.dispatch("defaultAccess/show").then(res => {
            this.props.search.branch_id = res.data.data.branch_id;
            this.list();
        }).catch();

        this.$store.dispatch('paymentGateway/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        transactions: function () {
            return this.$store.getters['transactionsSales/lists'];
        },
        paymentGateways: function () {
            return this.$store.getters['transactionsSales/lists'];
        },
        pagination: function () {
            return this.$store.getters['transactionsSales/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['transactionsSales/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        orderStatusClass: function (status) {
            return appService.orderStatusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        search: function () {
            this.list();
        },
        handleDate: function (e) {
            if (e) {
                this.props.search.from_date = e[0];
                this.props.search.to_date = e[1];
            } else {
                this.props.form.date = null;
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_type = "desc";
            this.props.search.status_transaction = "";
            this.props.search.request_id = "";
            this.props.search.client_name = "";
            this.props.search.payment = null;
            this.props.search.from_date = "";
            this.props.search.to_date = "";
            this.props.form.date = "";
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('transactionsSales/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("transactionsSales/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.transactionsSales");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        show: function (transactions) {

            try {
                // this.loading.isActive = true;
                this.$store
                    .dispatch("transactionsSales/showUuid", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ uuid: transactions.uuid }),
                    })
                    .then((res) => {
                        appService.modalShows("#transaction-modal", res.data.data);

                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    });
            } catch (err) {
                this.loading.isActive = false;
                console.log(err.message)
                alertService.error(err.message);
            }


        },
        destroy: function (addressId) {
            appService.reverseConfirmation(addressId.uuid, addressId)
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("transactionsSales/reverse", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ uuid: addressId.uuid }),
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.administrators"));
                                window.location.reload();

                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        console.log(err.message)
                        alertService.error(err.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error("Ocurrió un error al intentar confirmar la reversión.");
                });
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
