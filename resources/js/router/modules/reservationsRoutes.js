import ReservationsComponent from "../../components/admin/reservations/ReservationsComponent.vue";
import ReservationsListComponent from "../../components/admin/reservations/ReservationsListComponent.vue";
import ReservationsShowComponent from "../../components/admin/reservations/ReservationsShowComponent.vue";

export default [
    {
        path: "/admin/reservations",
        component: ReservationsComponent,
        name: "admin.reservations",
        redirect: { name: "admin.reservations.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "reservations",
        },
        children: [
            {
                path: "list",
                component: ReservationsListComponent,
                name: "admin.reservations.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: ReservationsShowComponent,
                name: "admin.reservations.show",
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
