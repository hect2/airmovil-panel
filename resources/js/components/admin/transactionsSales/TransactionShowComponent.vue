<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title">{{ $t('label.coupon') }}</h3>
            </div>
            <div class="db-card-body">
                <div class="row">
                    <div class="col-12 sm:col-5">
<!--                        <img class="db-image" alt="coupon" :src="coupon.image">-->
                    </div>
                    <div class="col-12 sm:col-7 md:pl-8">
                        <h1>HOLAS</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import taxTypeEnum from "../../../enums/modules/taxTypeEnum";


export default {
    name: "TransactionShowComponent",
    components: {
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                taxTypeEnum: taxTypeEnum,
                taxTypeEnumArray: {
                    [taxTypeEnum.FIXED]: this.$t("label.fixed"),
                    [taxTypeEnum.PERCENTAGE]: this.$t("label.percentage"),
                },
            }
        }
    },
    computed: {
        coupon: function () {
            return this.$store.getters['transactionsSales/show'];
        }
    },
    mounted() {
        console.log(this.$route.params.id)
        this.loading.isActive = true;
        this.$store.dispatch('transactionsSales/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
    },
    methods: {
        taxTypeClass: function (status) {
            return appService.taxTypeClass(status);
        },
    }
}

</script>
