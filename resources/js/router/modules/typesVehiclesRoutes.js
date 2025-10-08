import TypesVehiclesComponent from "../../components/admin/typesVehicles/TypesVehiclesComponent.vue";
import TypesVehiclesListComponent from "../../components/admin/typesVehicles/TypesVehiclesListComponent.vue";
import typesVehiclesShowComponent from "../../components/admin/typesVehicles/TypesVehiclesShowComponent.vue";

export default [
    {
        path: "/admin/types-of-cars",
        component: TypesVehiclesComponent,
        name: "admin.typesVehicles",
        redirect: { name: "admin.typesVehicles.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "typesVehicles",
        },
        children: [
            {
                path: "list",
                component: TypesVehiclesListComponent,
                name: "admin.typesVehicles.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: typesVehiclesShowComponent,
                name: "admin.typesVehicles.show",
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
