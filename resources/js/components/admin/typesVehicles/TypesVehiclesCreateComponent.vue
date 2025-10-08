<template>
    <LoadingComponent :props="loading" />
    <SmSidebarModalCreateComponent :props="addButton" />

    <div id="sidebar" class="drawer">
        <div class="drawer-dialog">
            <div class="drawer-header">
                <h3 class="drawer-title">{{ $t('menu.typesVehicles') }}</h3>
                <button class="fa-solid fa-xmark close-btn" @click="reset"></button>
            </div>
            <div class="drawer-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title required">{{ $t("label.name") }}</label>
                            <input
                                v-model="props.form.name"
                                :class="errors.name ? 'invalid' : ''"
                                type="text"
                                id="name"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>

                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label for="description" class="db-field-title required">{{ $t("label.description") }}</label>
                            <input
                                v-model="props.form.description"
                                :class="errors.description ? 'invalid' : ''"
                                type="text"
                                id="description"
                                class="db-field-control"
                            >
                            <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                        </div> -->
                        <div class="form-col-12 sm:form-col-6">
                            <label for="description" class="db-field-title">{{ $t("label.description") }}</label>
                            <textarea
                                v-model="props.form.description"
                                :class="errors.description ? 'invalid' : ''"
                                id="description"
                                class="db-field-control"
                            ></textarea>
                            <small class="db-field-alert" v-if="errors.description">{{ errors.description[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title">{{ $t("label.image") }}</label>
                            <input
                                @change="changeImage"
                                :class="errors.image ? 'invalid' : ''"
                                id="image"
                                type="file"
                                class="db-field-control"
                                ref="imageProperty"
                                accept="image/png, image/jpeg, image/jpg"
                            >
                            <small class="db-field-alert" v-if="errors.image">{{ errors.image[0] }}</small>
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
    name: "TypesVehiclesCreateComponent",
    components: { SmSidebarModalCreateComponent, LoadingComponent },
    props: ['props'],
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
            errors: {},
            image: null,
        }
    },
    computed: {
        addButton() {
            return { title: this.$t('button.add_vehicles') };
        }
    },
    mounted() {
        // Opcional: si necesitas cargar algo al montar
    },
    methods: {
        changeImage(e) {
            this.image = e.target.files[0];
        },
        reset() {
            appService.sideDrawerHide();
            this.$store.dispatch('typesVehicles/reset').then().catch();
            this.errors = {};
            // Reiniciar formulario
            this.$props.props.form = {
                name: "",
                image: "",
                description: "",
            };
            if (this.image) {
                this.image = null;
                this.$refs.imageProperty.value = null;
            }
        },
        save() {
            try {
                const formData = new FormData();
                formData.append('name', this.props.form.name);
                formData.append('description', this.props.form.description);

                if (this.image) {
                    formData.append('image', this.image);
                }

                const tempId = this.$store.getters['typesVehicles/temp'].temp_id;
                this.loading.isActive = true;
                this.$store.dispatch('typesVehicles/save', { form: formData, search: this.props.search })
                    .then(res => {
                        appService.sideDrawerHide();
                        this.loading.isActive = false;
                        alertService.successFlip((tempId === null ? 0 : 1), this.$t('menu.typesVehicles'));
                        // Reset formulario local
                        this.props.form.name = "";
                        this.props.form.description = "";
                        this.image = null;
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
        }
    }
}
</script>

<style scoped>
/* Puedes agregar estilos específicos si los necesitas */
</style>
