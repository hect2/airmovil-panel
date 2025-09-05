import TransactionSalesListComponent from "../../components/admin/transactionsSales/TransactionListComponentSales.vue";
import TransactionShowComponent from "../../components/admin/transactionsSales/TransactionShowComponent.vue";
import CouponListComponent from "../../components/admin/coupons/CouponListComponent.vue";

export default [
    {
        path: '/admin/transactionsSales',
        component: TransactionSalesListComponent,
        name: 'admin.transactionsSales',
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'transactionsSales',
            breadcrumb: 'transactions_sales'
        },
        children: [

            {
                path: "show/:id",
                component: TransactionShowComponent,
                name: "admin.transactionsSales.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "transactionsSales",
                    breadcrumb: "view",
                },
            },
        ],
    }
]
