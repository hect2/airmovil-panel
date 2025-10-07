<template>
    <LoadingComponent :props="loading" />
    <div class="container">

        <div v-if="!logged" >
            <CTAHomeComponent />
        </div>

        <div v-if="logged" class="my-14 p-6">
            <router-link
                v-if="profile.role_id !== enums.roleEnum.CUSTOMER && Object.keys(authDefaultPermission).length > 0"
                :to="{ name: 'admin.dashboard' }"
                class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-dashboard lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('menu.dashboard') }}</span>
            </router-link>

            <!-- <router-link :to="{ name: 'frontend.myOrder' }"
                         class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-reserve-line lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('button.my_orders') }}</span>
            </router-link>

            <router-link :to="{ name: 'frontend.editProfile' }"
                         class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-edit lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('button.edit_profile') }}</span>
            </router-link>

            <router-link :to="{ name: 'frontend.chat' }"
                         class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-messages-line lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('button.chat') }}</span>
            </router-link>

            <router-link :to="{ name: 'frontend.address' }"
                         class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-map lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('button.address') }}</span>
            </router-link>

            <router-link :to="{ name: 'frontend.changePassword' }"
                         class="paper-link transition w-full flex items-center gap-3.5 py-2.5 border-b last:border-none border-[#EFF0F6]">
                <i class="lab lab-key lab-font-size-17"></i>
                <span class="text-sm leading-6 capitalize">{{ $t('button.change_password') }}</span>
            </router-link> -->
        </div>
    </div>
</template>

<script>
import SliderComponent from "../../frontend/home/SliderComponent";
import CategoryComponent from "../components/CategoryComponent";
import FeaturedItemComponent from "../home/FeaturedItemComponent";
import PopularItemComponent from "../home/PopularItemComponent";
import OfferComponent from "../components/OfferComponent";
import categoryDesignEnum from "../../../enums/modules/categoryDesignEnum";
import statusEnum from "../../../enums/modules/statusEnum";
import LoadingComponent from "../components/LoadingComponent";
import TrackOrderComponent from "./TrackOrderComponent";
import activityEnum from "../../../enums/modules/activityEnum";
import roleEnum from "../../../enums/modules/roleEnum";
import CTAHomeComponent from "../components/CTAHomeComponent.vue";

export default {
    name: "HomeComponent",
    components: {
        CTAHomeComponent,
        TrackOrderComponent,
        CategoryComponent,
        SliderComponent,
        FeaturedItemComponent,
        PopularItemComponent,
        OfferComponent,
        LoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            categoryProps: {
                design: categoryDesignEnum.FIRST,
                slug: '',
            },
            limit: 4,
            enums: {
                activityEnum: activityEnum,
                roleEnum: roleEnum
            },
        };
    },
    computed: {
        logged: function () {
            return this.$store.getters.authStatus;
        },
        authDefaultPermission: function () {
            return this.$store.getters.authDefaultPermission;
        },
        profile: function () {
            return this.$store.getters.authInfo;
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch("frontendItemCategory/lists", {
            paginate: 0,
            order_column: "sort",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        }).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },
    watch: {
        categories: {
            deep: true,
            handler(category) {
                if (category.length > 0) {
                    if (category[0].slug !== "undefined") {
                        this.categoryProps.slug = category[0].slug;
                    }
                }
            },
        },
    },
};
</script>
