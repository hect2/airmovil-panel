import axios from 'axios';
import appService from "../../services/appService";

export const marks = {
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
        lists: state => state.lists,
        pagination: state => state.pagination,
        page: state => state.page,
        show: state => state.show,
        temp: state => state.temp,
    },
    actions: {
        lists({ commit }, payload) {
            return new Promise((resolve, reject) => {
                let url = '/admin/marks';
                if (payload) {
                    url += appService.requestHandler(payload);
                }
                axios.get(url)
                    .then(res => {
                        if (typeof payload?.vuex === "undefined" || payload.vuex === true) {
                            commit('lists', res.data.data);
                            commit('page', res.data.meta);
                            commit('pagination', res.data);
                        }
                        resolve(res);
                    })
                    .catch(reject);
            });
        },
        save({ dispatch, commit, state }, payload) {
            return new Promise((resolve, reject) => {
                let method = axios.post;
                let url = '/admin/marks';
                if (state.temp.isEditing) {
                    method = axios.post;
                    url = `/admin/marks/${state.temp.temp_id}`;
                }
                method(url, payload.form)
                    .then(res => {
                        dispatch('lists', payload.search);
                        commit('reset');
                        resolve(res);
                    })
                    .catch(reject);
            });
        },
        edit({ commit }, payload) {
            commit('temp', payload);
        },
        destroy({ dispatch }, payload) {
            return new Promise((resolve, reject) => {
                axios.delete(`/admin/marks/${payload.id}`)
                    .then(res => {
                        dispatch('lists', payload.search);
                        resolve(res);
                    })
                    .catch(reject);
            });
        },
        show({ commit }, id) {
            return new Promise((resolve, reject) => {
                axios.get(`/admin/marks/show/${id}`)
                    .then(res => {
                        commit('show', res.data.data);
                        resolve(res);
                    })
                    .catch(reject);
            });
        },
        reset({ commit }) {
            commit('reset');
        },
        export(_, payload) {
            return new Promise((resolve, reject) => {
                let url = '/admin/marks/export';
                if (payload) {
                    url += appService.requestHandler(payload);
                }
                axios.get(url, { responseType: 'blob' })
                    .then(resolve)
                    .catch(reject);
            });
        },
    },
    mutations: {
        lists(state, payload) {
            state.lists = payload;
        },
        pagination(state, payload) {
            state.pagination = payload;
        },
        page(state, payload) {
            if (payload) {
                state.page = {
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                };
            }
        },
        show(state, payload) {
            state.show = payload;
        },
        temp(state, id) {
            state.temp.temp_id = id;
            state.temp.isEditing = true;
        },
        reset(state) {
            state.temp.temp_id = null;
            state.temp.isEditing = false;
        }
    },
};
