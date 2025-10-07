import MarksCarsTableListComponent from "../../components/admin/categories/MarksCarsTableListComponent";
import MarksCarsTableComponent from "../../components/admin/categories/MarksCarsTableComponent";
import MarksCarsTableShowComponent from "../../components/admin/categories/MarksCarsTableShowComponent";

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
            breadcrumb: "dining_tables",
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
