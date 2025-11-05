<template>
    <LoadingComponent :props="loading" />  

    <footer class="text-white bg-[var(--brand)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Columna izquierda -->
                <div>
                    <h3 class="text-white text-2xl font-bold mb-2">AIRMOVIL</h3>
                    <p class="text-white/90 mb-6">
                        {{ $t('label.subscribe_short_text') }}
                    </p>
                    <form @submit.prevent="saveSubscription" class="flex gap-2">
                        <input type="email" v-model="subscriptionProps.post.email"
                            :placeholder="$t('label.your_email_address')"
                            class="bg-white flex-1 px-4 py-3 rounded-lg text-gray-900 focus:ring-2 focus:ring-white focus:outline-none"
                            required />
                        <button type="submit" style="background: white; color: var(--brand)"
                            class="px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors bg-white">
                            {{ $t('button.subscribe') }}
                        </button>
                    </form>
                </div>

                <!-- Columna derecha -->
                <div>
                    <h4 class="text-white text-xl font-bold mb-4">{{ $t('label.useful_links') }}</h4>
                    <div class="space-y-3">
                        <div v-for="page in pages" :key="page.id">
                            <router-link :to="{ name: 'frontend.page', params: { slug: page.slug } }"
                                class="flex items-center gap-2 text-white/90 hover:text-white transition-colors">
                                <i class="fa-solid fa-link w-5 h-5"></i>
                                {{ page.title }}
                            </router-link>
                        </div>

                        <a v-if="setting.company_email" :href="`mailto:${setting.company_email}`"
                            class="flex items-center gap-2 text-white/90 hover:text-white transition-colors">
                            <i class="fa-solid fa-envelope w-5 h-5"></i>
                            {{ setting.company_email }}
                        </a>

                        <a v-if="setting.company_phone" :href="`tel:${setting.company_phone}`"
                            class="flex items-center gap-2 text-white/90 hover:text-white transition-colors">
                            <i class="fa-solid fa-phone w-5 h-5"></i>
                            {{ setting.company_phone }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onda decorativa -->
        <div class="h-24 overflow-hidden">
            <svg class="absolute bottom-0 w-full" viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0,64 C240,96 480,96 720,64 C960,32 1200,32 1440,64 L1440,120 L0,120 Z" fill="#001233" />
                <path d="M0,80 C320,48 640,48 960,80 C1120,96 1280,96 1440,80 L1440,120 L0,120 Z" fill="#001233"
                    opacity="0.5" />
            </svg>
        </div>

        <!-- <div class="py-8 border-t border-white/20">
            <p class="text-sm text-center text-white">{{ setting.site_copyright }}</p>
        </div> -->
    </footer>
</template>

<script>
import statusEnum from "../../../enums/modules/statusEnum";
import menuSectionEnum from "../../../enums/modules/menuSectionEnum";
import axios from "axios";
import alertService from "../../../services/alertService";
import LoadingComponent from "../../frontend/components/LoadingComponent";

export default {
    name: "FrontendFooterComponent",
    components: { LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            subscriptionProps: {
                post: {
                    email: "",
                },
            },
        };
    },
    computed: {
        setting() {
            return this.$store.getters["frontendSetting/lists"];
        },
        pages() {
            return this.$store.getters["frontendPage/lists"];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store
            .dispatch("frontendPage/lists", {
                paginate: 0,
                order_column: "id",
                order_type: "asc",
                menu_section_id: menuSectionEnum.FOOTER,
                status: statusEnum.ACTIVE,
            })
            .finally(() => (this.loading.isActive = false));
    },
    methods: {
        saveSubscription() {
            const url = "/frontend/subscriber";
            this.loading.isActive = true;
            axios
                .post(url, this.subscriptionProps.post)
                .then(() => {
                    this.subscriptionProps.post.email = "";
                    alertService.success(this.$t("message.subscribe"));
                })
                .finally(() => (this.loading.isActive = false));
        },
    },
};
</script>
