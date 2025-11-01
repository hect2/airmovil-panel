import VehicleOwnerComponent from "../../components/admin/vehicleOwner/VehicleOwnerComponent.vue";
import VehicleOwnerListComponent from "../../components/admin/vehicleOwner/VehicleOwnerListComponent.vue";

export default [
    {
        path: "/admin/vehicleOwners",
        component: VehicleOwnerComponent,
        name: "admin.vehicleOwners",
        redirect: { name: "admin.vehicleOwners.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "customers",
            breadcrumb: "vehicleOwners",
        },
        children: [
            {
                path: "list",
                component: VehicleOwnerListComponent,
                name: "admin.vehicleOwners.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customers",
                    breadcrumb: "",
                },
            }
        ],
    },
]
