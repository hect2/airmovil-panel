import PolizesListComponent from "../../components/admin/polizes/PolizesListComponent.vue";
import PolizesComponent from "../../components/admin/polizes/PolizesComponent.vue";
import PolizesShowComponent from "../../components/admin/polizes/PolizesShowComponent.vue";

export default [
    {
        path: "/admin/polizes",
        component: PolizesComponent,
        name: "admin.polizes",
        redirect: { name: "admin.polizes.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "polizes",
        },
        children: [
            {
                path: "list",
                component: PolizesListComponent,
                name: "admin.polizes.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: PolizesShowComponent,
                name: "admin.polizes.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
