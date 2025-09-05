import axios from 'axios'
import appService from "../../services/appService";


export const transactionsSales = {
    namespaced: true,
    state: {
        lists: [],
        page: {},
        pagination: [],
        show: {},
        temp: {
            temp_id: null,
            isEditing: false,
        },
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },

        pagination: function (state) {
            return state.pagination
        },
        page: function(state) {
            return state.page;
        },
        show: function (state) {
            return state.show;
        },
    },
    actions: {
        lists: function (context, payload) {
            console.log(payload)
            return new Promise((resolve, reject) => {
                let url = 'admin/transactionsSales';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url).then((res) => {
                    if(typeof payload.vuex === "undefined" || payload.vuex === true) {
                        context.commit('lists', res.data.data);
                        context.commit('page', res.data.meta);
                        context.commit('pagination', res.data);
                    }

                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context) {
            context.commit('reset');
        },

        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/transactionsSales/export';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url, {responseType: 'blob'}).then((res) => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reverse: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/transactionsSales/reverse';

                // Decodificar los datos JSON si es necesario
                let decodedPayload = JSON.parse(payload.body);

                axios.post(url, {
                    uuid: decodedPayload.uuid // Usa el uuid decodificado directamente
                }, {
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    responseType: 'blob' // Si necesitas que la respuesta sea en formato blob
                })
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },
        showUuid: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/transactionsSales/show';

                // Decodificar los datos JSON si es necesario
                let decodedPayload = JSON.parse(payload.body);

                axios.post(url, {
                    uuid: decodedPayload.uuid // Usa el uuid decodificado directamente
                }, {
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    responseType: 'json' // Si necesitas que la respuesta sea en formato blob
                })
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => {
                        reject(err);
                    });
            });
        },

    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload
        },
        pagination: function (state, payload) {
            state.pagination = payload;
        },
        page: function (state, payload) {
            if(typeof payload !== "undefined" && payload !== null) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                }
            }
        },
        reset: function(state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        },
        show: function (state, payload) {
            state.show = payload;
        },
    },
}
