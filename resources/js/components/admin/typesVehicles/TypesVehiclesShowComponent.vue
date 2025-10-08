<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">

        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('menu.typesVehicles') }}</h3>

                <div class="db-card-filter">
                    <button v-print="printObj" class="db-btn h-[37px] text-white bg-primary">
                        <i class="lab lab-printer-line lab-font-size-17"></i>
                        {{ $t('button.print') }}
                    </button>
                </div>
            </div>
            <div class="db-card-body" id="print">
                <!-- <img class="w-36 mx-auto mb-1" :src="setting.theme_logo" alt="logo"> -->
                <p class="text-center">
                    <span class="block capitalize mt-4">{{ typesVehicles.name }}</span>
                    <span class="block">{{ typesVehicles.description }}</span>
                </p>
                <img class="w-48 sm:w-60 mx-auto my-4" :src="typesVehicles.image" alt="Marca">
                <!-- <p class="text-center mb-6">
                    <span class="block capitalize">{{ typesVehicles.name }}</span>
                </p> -->

                <p class="text-center">
                    <!-- <span class="block font-medium">{{ $t('message.table_scan') }}</span> -->
                    <!-- <span class="block text-lg font-semibold capitalize">{{ $t('message.thank_you') }}</span> -->
                </p>
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

export default {
    name: "TypesVehicleShowComponent",
    components: {
        LoadingComponent,
        PrintComponent
    },
    directives: {
        print
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
                popTitle: this.$t("menu.typesVehicles"),
            },
        }
    },
    computed: {
        typesVehicles: function () {
            return this.$store.getters['typesVehicles/show'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('typesVehicles/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
    }
}
</script>
