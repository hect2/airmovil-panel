import MarksCarsTableListComponent from "../../components/admin/markscars/MarksCarsTableListComponent.vue";
import MarksCarsTableComponent from "../../components/admin/markscars/MarksCarsTableComponent.vue";
import MarksCarsTableShowComponent from "../../components/admin/markscars/MarksCarsTableShowComponent.vue";

export default [
    {
        path: "/admin/marcas",
        component: MarksCarsTableComponent,
        name: "admin.marksCars",
        redirect: { name: "admin.marksCars.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "settings",
            breadcrumb: "marksCars",
        },
        children: [
            {
                path: "list",
                component: MarksCarsTableListComponent,
                name: "admin.marksCars.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "settings",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: MarksCarsTableShowComponent,
                name: "admin.marksCars.show",
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
