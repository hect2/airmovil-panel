import CarFeaturesComponent from "../../components/admin/carFeatures/CarFeaturesComponent.vue";
import CarFeaturesListComponent from "../../components/admin/carFeatures/CarFeaturesListComponent.vue";

export default [
    {
        path: "/admin/carFeatures",
        component: CarFeaturesComponent,
        name: "admin.carFeatures",
        redirect: { name: "admin.carFeatures.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "carFeatures",
        },
        children: [
            {
                path: "list",
                component: CarFeaturesListComponent,
                name: "admin.carFeatures.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            }
        ],
    },
]
