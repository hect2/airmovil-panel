import VueSimpleAlert from "vue3-simple-alert";
import store from "../store";
import statusEnum from "../enums/modules/statusEnum";
import orderStatusEnum from "../enums/modules/orderStatusEnum";
import askEnum from "../enums/modules/askEnum";
import taxTypeEnum from "../enums/modules/taxTypeEnum";
import currencyPositionEnum from "../enums/modules/currencyPositionEnum";

export default {
    sideDrawerShow: function (id = 'sideDrawer') {
        const drawerDivs = document?.querySelectorAll(".drawer");
        const drawerSets = document?.querySelectorAll("[data-drawer]");
        drawerSets?.forEach((drawerSet) => {
            const targetElm = document?.querySelector(drawerSet?.dataset?.drawer);
            drawerSets?.forEach(drawerBtn => drawerBtn?.classList?.remove("active"));
            drawerDivs?.forEach(drawerDiv => drawerDiv?.classList?.remove("active"));
            targetElm?.classList?.add("active");
            drawerSet?.classList?.add("active");
            document.body.style.overflowY = "hidden";
            document?.querySelector(".backdrop")?.classList?.add("active");
        });
    },
    sideDrawerHide: function (id = 'sideDrawer') {
        const drawerDivs = document?.querySelectorAll(".drawer");
        const drawerSets = document?.querySelectorAll("[data-drawer]");
        document?.querySelectorAll("#sidebar")?.forEach((closeBtn) => {
            drawerSets?.forEach(drawerBtn => drawerBtn?.classList?.remove("active"));
            drawerDivs?.forEach(drawerDiv => drawerDiv?.classList?.remove("active"));
            document?.querySelector(".backdrop")?.classList?.remove("active");
            document.body.style.overflowY = "auto"
        });
    },

    modalShow: function (id = '.modal') {
        let modalButton = document?.querySelectorAll("[data-modal]");
        modalButton?.forEach((modalBtn) => {
            const modalTarget = document?.querySelector(id);
            modalTarget?.classList?.add("active");
            document.body.style.overflowY = "hidden";
        });
    },

    modalHide: function (id = ".modal") {
        let modalDivs = document?.querySelectorAll(id);
        document.body.style.overflowY = "auto";
        modalDivs?.forEach((modalDiv) => modalDiv?.classList?.remove("active"));
    },

    phoneNumber: function (e) {
        let char = String.fromCharCode(e.keyCode);
        if (/^[+]?[0-9]*$/.test(char)) return true;
        else e.preventDefault();
    },

    onlyNumber: function (e) {
        let res = (e.charCode !== 8 && e.charCode === 0 || (e.charCode >= 48 && e.charCode <= 57));
        if (res)
            return true;
        else
            e.preventDefault();
    },

    floatNumber: function (e) {
        let char = String.fromCharCode(e.keyCode);
        if (/^[.]?[0-9]*$/.test(char)) return true;
        else e.preventDefault();
    },

    currencyFormat(amount, decimal, currency, position) {
        if (position === currencyPositionEnum.LEFT) {
            return currency + parseFloat(amount).toFixed(decimal);
        } else {
            return parseFloat(amount).toFixed(decimal) + currency;
        }
    },

    destroyConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "No podrás recuperar el registro borrado.",
            "¿Seguro?",
            "warning",
            {
                confirmButtonText: "Sí, bórralo.",
                cancelButtonText: "¡No, cancela!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },

    reverseConfirmation: function () {
        return new VueSimpleAlert.confirm(
            "Desas anular esta transacción. Una vez aceptada, no se podrá revertir su estado anterior.",
            "¿Seguro?",
            "warning",
            {
                confirmButtonText: "Sí, Anular!",
                cancelButtonText: "No, Cancelar!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    modalShows: function (id = ".modal", data = null) {
        const modalTarget = document?.querySelector(id);

        if (modalTarget) {
            // Agregar clase activa al modal y deshabilitar scroll en la página
            modalTarget.classList.add("active");
            document.body.style.overflowY = "hidden";

            // Agregar datos dinámicos al modal
            if (data) {
                const modalContent = modalTarget.querySelector(".modal-content");
                if (modalContent) {
                    modalContent.innerHTML = `
                <style>
                    .modal-content {
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        max-width: 800px;
                        width: 90%;
                        margin: 0 auto;
                        animation: fadeIn 0.3s ease-in-out;
                    }

                    .modal.active {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position: fixed;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.8);
                        z-index: 1000;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: scale(0.9);
                        }
                        to {
                            opacity: 1;
                            transform: scale(1);
                        }
                    }

                    .close-modal-btn {
                        position: absolute;
                        top: 10px;
                        right: 10px;
                        background: none;
                        border: none;
                        font-size: 24px;
                        cursor: pointer;
                        color: #333;
                        transition: color 0.3s ease-in-out;
                    }

                    .close-modal-btn:hover {
                        color: #ff0000;
                    }

                    .modal-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                    }

                    .modal-header h2 {
                        font-size: 20px;
                        margin: 0;
                        color: #333;
                    }

                    .modal-body {
                        display: flex;
                        flex-direction: column;
                        gap: 15px;
                    }

                    .modal-body p {
                        margin: 0;
                        font-size: 14px;
                        color: #555;
                    }

                    .modal-body p strong {
                        color: #000;
                    }

                    .transaction-details {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 20px;
                    }

                    .transaction-details div {
                        flex: 1 1 calc(50% - 20px);
                        background: #f8f8f8;
                        padding: 10px;
                        border-radius: 5px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    }

                    .transaction-details div p {
                        margin: 0 0 5px;
                        font-size: 14px;
                        color: #555;
                    }

                    .transaction-details div p strong {
                        color: #000;
                    }

                    .voucher-link {
                        color: #007bff;
                        text-decoration: none;
                        font-size: 14px;
                    }

                    .voucher-link:hover {
                        text-decoration: underline;
                    }

                    .modal-footer {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 10px;
                    }

                    .modal-footer button {
                        padding: 5px 10px;
                        border: none;
                        background: #007bff;
                        color: #fff;
                        border-radius: 5px;
                        cursor: pointer;
                        transition: background 0.3s ease-in-out;
                    }

                    .modal-footer button:hover {
                        background: #0056b3;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }

                    table, th, td {
                        border: 1px solid #ddd;
                    }

                    th, td {
                        padding: 10px;
                        text-align: left;
                    }

                    th {
                        background-color: #f4f4f4;
                        color: #333;
                    }

                    .transaction-voucher {
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                        margin-top: 20px;
                    }

                    .transaction-voucher .voucher-card {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: 10px 15px;
                        background-color: #f8f9fa;
                        border-radius: 5px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                        text-decoration: none;
                        color: #333;
                    }

                    .transaction-voucher .voucher-card:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                        background-color: #e9ecef;
                    }

                    .transaction-voucher .voucher-card i {
                        font-size: 20px;
                        color: #007bff;
                        margin-right: 10px;
                    }

                    .transaction-voucher .voucher-card span {
                        font-weight: 500;
                        font-size: 14px;
                        color: #333;
                    }

                    .transaction-voucher .voucher-card:hover span {
                        color: #0056b3;
                    }

                    .error-message {
                        background-color: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                        padding: 15px;
                        margin-top: 20px;
                        border-radius: 5px;
                    }

                    .accepted-status {
                        background-color: rgb(60, 187, 187);
                        color: #fff;
                        padding: 15px;
                        border-radius: 5px;
                        margin-top: 20px;
                    }
                </style>

                <div class="modal-header">
                    <h2>Detalles de la Transacción</h2>
                    <button class="close-modal-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="transaction-details">
                        <div>
                            <p><strong>ID:</strong> ${data.request_id}</p>
                            <p><strong>Cliente:</strong> ${data.client_name}</p>
                            <p><strong>Fecha:</strong> ${data.date_transaction}</p>
                            <p><strong>Monto:</strong> ${data.currency} ${data.total}</p>
                            <p><strong>Estado:</strong> ${data.status_transaction}</p>
                        </div>
                        <div>
                            <p><strong>ID Orden:</strong> ${data.id_order}</p>
                            <p><strong>Fecha de Transacción:</strong> ${data.date_transaction}</p>
                            <p><strong>Tarjeta:</strong> **** **** **** ${data.value_payment}</p>
                            <p><strong>Tipo de tarjeta:</strong> ${data.type_card}</p>
                        </div>
                    </div>

                    ${data.status_transaction === "REJECT" ? `
                        <div class="error-message">
                            <p><strong>Código de Error:</strong> ${data.error.code}</p>
                            <p><strong>Descripción:</strong> ${data.error.description}</p>
                        </div>
                    ` : ''}

                    ${data.status_transaction === "ACCEPT" ? `
                        <div class="accepted-status">
                            <p><strong>¡Transacción Aceptada!</strong></p>
                        </div>
                    ` : ''}

                    ${data.status_transaction != "REJECT" ? `
                    <div class="transaction-voucher">
                        <a href="${data.url_voucher}" class="voucher-card" target="_blank">
                            <i class="fa fa-file-alt"></i>
                            <span>Ver Voucher</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                        ${data.status_transaction == "REVERSE" ? `
                            <a v-if="data.url_voucher_reverse && data.url_voucher_reverse !== ''"
                               href="${data.url_voucher_reverse}" class="voucher-card" target="_blank">
                                <i class="fa fa-undo-alt"></i>
                                <span>Ver Voucher de Reverse</span>
                                <i class="fa fa-arrow-right"></i>
                            </a>

                        ` : ''}
                        <a href="/admin/pos-orders/show/${data.id_order}" class="voucher-card">
                            <i class="fa fa-receipt"></i>
                            <span>Ver Orden</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                    ` : ''}

                    <table>
                        <thead>
                            <tr>
                                <th>Producto/Servicio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.detail.map(item => `
                            <tr>
                                <td>${item.description}</td>
                                <td>${item.quantity}</td>
                                <td>GTQ ${item.subtotal}</td>
                            </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn-close-modal">Cerrar</button>
                </div>
                `;

                    // Agregar eventos para los botones de cierre y agregar comentario
                    modalContent.querySelector(".btn-close-modal")?.addEventListener("click", () => {
                        this.modalHide(id);
                    });
                }
            }
        }
    },



    acceptOrder: function () {
        return new VueSimpleAlert.confirm(
            "No podrá anular el pedido.!",
            "¿Seguro?",
            "warning",
            {
                confirmButtonText: "¡Sí, acéptalo!",
                cancelButtonText: "¡No, cancela!",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },
    cancelOrder: function () {
        return new VueSimpleAlert.confirm(
            "No podrá aceptar el pedido.!",
            "¿Seguro?",
            "warning",
            {
                confirmButtonText: "Sí, ¡Cancélalo!",
                cancelButtonText: "No, Cancelar",
                confirmButtonColor: "#696cff",
                cancelButtonColor: "#8592a3",
            }
        );
    },

    distance: function (lat1, lng1, lat2, lng2) {
        let radiationLat1 = Math.PI * lat1 / 180
        let radiationLat2 = Math.PI * lat2 / 180
        let theta = lng1 - lng2;
        let radiationTheta = Math.PI * theta / 180
        let distance = Math.sin(radiationLat1) * Math.sin(radiationLat2) + Math.cos(radiationLat1) * Math.cos(radiationLat2) * Math.cos(radiationTheta);
        distance = Math.acos(distance)
        distance = distance * 180 / Math.PI
        distance = distance * 60 * 1.1515
        distance = distance * 1.609344
        return distance;
    },

    recursiveRouter: function (routes, permission) {
        let i, j;
        for (i = 0; i < routes.length; i++) {
            for (j = 0; j < permission.length; j++) {
                if (typeof routes[i].meta !== "undefined" && routes[i].meta) {
                    if (typeof routes[i].meta.permissionUrl !== "undefined" && routes[i].meta.permissionUrl) {
                        if (routes[i].meta.permissionUrl === permission[j].url) {
                            routes[i].meta.access = permission[j].access;
                            routes[i].meta.title = permission[j].title;
                        }

                        if (typeof routes[i].children !== "undefined" && routes[i].children) {
                            this.recursiveRouter(routes[i].children, permission);
                        }
                    }
                }
            }
        }
    },

    textShortener: function (text, number = 30) {
        if (text) {
            if (!(text.length < number)) {
                return text.substring(0, number) + "..";
            }
        }
        return text;
    },
    statusClass: function (status) {
        if (status === statusEnum.ACTIVE) {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-red-600 bg-red-100";
        }
    },

    orderStatusClass: function (status) {
         if(status == orderStatusEnum.ACCEPT || status == orderStatusEnum.PROCESSING){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#2AC769] bg-[#CBFFE0]";
        }
        else if(status == orderStatusEnum.PENDING){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#F6A609] bg-[#FFEEC6]";
        }
        else if(status == orderStatusEnum.OUT_FOR_DELIVERY){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#008BBA] bg-[#BDEFFF]";
        }
        else if(status == orderStatusEnum.DELIVERED){
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-primary bg-[#FFD7E7]";
        }
        else {
            return "py-0.5 px-2 rounded-full text-[10px] font-rubik leading-4 first-letter:capitalize whitespace-nowrap text-[#FB4E4E] bg-[#FFDADA]";
        }
    },

    askClass: function (ask) {
        if (ask === askEnum.YES) {
            return "db-table-badge text-green-600 bg-green-100";
        } else {
            return "db-table-badge text-red-600 bg-red-100";
        }
    },

    taxTypeClass: function (type) {
        if (type === taxTypeEnum.FIXED) {
            return "db-table-badge text-blue-500 bg-blue-100";
        } else {
            return "db-table-badge text-orange-500 bg-orange-100";
        }
    },

    requestHandler: function (requests) {
        let i = 1;
        let what = "?";
        let response = "";

        for (let request in requests) {
            if (requests[request] !== "" && requests[request] !== null) {
                if (i !== 1) {
                    response += "&";
                }
                response += request + "=" + requests[request];
            }
            i++;
        }

        if (response) {
            response = what + response;
        }

        return response;
    },

    responsiveLoad: function() {
        let mainHeader = document?.querySelector(".db-header");
        let subHeader = document?.querySelector(".sub-header");
        let mainHeight = mainHeader?.scrollHeight;

        if (subHeader) {
            subHeader.style.top = `${mainHeight}px`;
        }
    },


    permissionChecker: function (permissionName) {
        let i, permissions = store.getters.authPermission;
        for (i = 0; i < permissions.length; i++) {
            if (typeof permissions[i].name !== "undefined" && permissions[i].name) {
                if (permissions[i].name === permissionName) {
                    return permissions[i].access;
                }
            }
        }
    },

    formDataShow: function (formData) {
        for (let pair of formData.entries()) {
            console.log(pair[0] + " : " + pair[1]);
        }
    },
};
