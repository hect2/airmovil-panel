<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-dialog">
            <div class="drawer-header">
                <h3 class="drawer-title">{{ $t('menu.polizes') }}</h3>
                <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
            </div>
            <div class="drawer-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="policyNumber" class="db-field-title required">{{ $t("label.policyNumber") }}</label>
                            <input
                                v-model="props.form.policyNumber"
                                :class="errors.policyNumber ? 'invalid' : ''"
                                type="text"
                                id="policyNumber"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.policyNumber">{{ errors.policyNumber[0] }}</small>
                        </div>

                        <!-- Nit -->
                        <div class="form-col-12 sm:form-col-6">
                            <label for="nit" class="db-field-title required">{{ $t("label.nit") }}</label>
                            <input
                                v-model="props.form.nit"
                                :class="errors.nit ? 'invalid' : ''"
                                type="text"
                                id="nit"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.nit">{{ errors.nit[0] }}</small>
                        </div>

                        <!-- customerId -->
                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label for="customerId" class="db-field-title required">{{ $t("label.customer") }}</label>

                            <vue-select
                                id="customerId"
                                v-model="props.form.customerId"
                                :options="customerUsers"
                                label-by="email"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="Selecciona un usuario"
                                search-placeholder="Buscar..."
                                :class="errors.customerId ? 'invalid' : ''"
                            />

                            <small class="db-field-alert" v-if="errors.customerId">{{ errors.customerId[0] }}</small>
                        </div> -->

                        <div class="form-col-12 sm:form-col-6">
                            <label for="customerId" class="db-field-title required">{{ $t("label.customerId") }}</label>
                            <input
                                v-model="props.form.customerId"
                                :class="errors.customerId ? 'invalid' : ''"
                                type="text"
                                id="customerId"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.customerId">{{ errors.customerId[0] }}</small>
                        </div>

                        <!-- Dropdown-->
                        <div class="form-col-12 sm:form-col-6">
                            <!-- <label for="dropdown" class="db-field-title required">{{ $t("label.dropdown") }}</label>
                            <input
                                v-model="props.form.dropdown"
                                :class="errors.dropdown ? 'invalid' : ''"
                                type="text"
                                id="dropdown"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.dropdown">{{ errors.dropdown[0] }}</small> -->
                        </div>

                        <!-- startDate -->
                        <div class="form-col-12 sm:form-col-6">
                            <label for="startDate" class="db-field-title required">{{ $t("label.startDate") }}</label>
                            <input
                                v-model="props.form.startDate"
                                :class="errors.startDate ? 'invalid' : ''"
                                type="date"
                                id="startDate"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.startDate">{{ errors.startDate[0] }}</small>
                        </div>

                        <!-- endDate -->
                        <div class="form-col-12 sm:form-col-6">
                            <label for="endDate" class="db-field-title required">{{ $t("label.endDate") }}</label>
                            <input
                                v-model="props.form.endDate"
                                :class="errors.endDate ? 'invalid' : ''"
                                type="date"
                                id="endDate"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.endDate">{{ errors.endDate[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t('label.status') }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active"
                                            type="radio" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active" class="db-field-label">{{ $t('label.active') }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status" type="radio"
                                            id="inactive" class="custom-radio-field">
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive" class="db-field-label">{{ $t('label.inactive') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("label.save") }}</span>
                                </button>
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import SmSidebarModalCreateComponent from "../components/buttons/SmSidebarModalCreateComponent";
import LoadingComponent from "../components/LoadingComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";

export default {
    name: "PolizesCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ['props'],
    customerUsers: {
        type: Array,
        default: () => []
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            errors: {}
        }
    },
    computed: {
        addButton() {
            return { title: this.$t('button.add_polizes') };
        }
    },
    mounted() {
        // Opcional: si necesitas cargar algo al montar
    },
    methods: {
        reset() {
            appService.sideDrawerHide();
            this.errors = {};
            // Reiniciar formulario
            this.$props.props.form = {
                policyNumber: "",
                nit: "",
                customerId: "",
                startDate: "",
                endDate: "",
                status: ""
            };
            this.$store.dispatch('polizes/reset').then().catch();
        },
        save() {
            try {
                const formData = new FormData();
                formData.append('policyNumber', this.props.form.policyNumber);
                formData.append('nit', this.props.form.nit);
                formData.append('customerId', this.props.form.customerId);
                formData.append('startDate', this.props.form.startDate);
                formData.append('endDate', this.props.form.endDate);
                formData.append('status', this.props.form.status);

                const tempId = this.$store.getters['polizes/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('polizes/save', { form: formData, search: this.props.search })
                    .then(res => {
                        appService.sideDrawerHide();
                        this.loading.isActive = false;
                        alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.polizes'));
                        // Reset formulario local
                        this.props.form.policyNumber = "";
                        this.props.form.nit = "";
                        this.props.form.customerId = "";
                        this.props.form.startDate = "";
                        this.props.form.endDate = "";
                        this.props.form.status = "";
                        this.errors = {};
                    })
                    .catch(err => {
                        this.loading.isActive = false;
                        if (err.response && err.response.data && err.response.data.errors) {
                            this.errors = err.response.data.errors;
                        }
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        getUsersAdmin(){

        }
    }
}
</script>

<style scoped>
/* Puedes agregar estilos específicos si los necesitas */
</style>
