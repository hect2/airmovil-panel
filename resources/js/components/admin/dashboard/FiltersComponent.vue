<template>
    <div class="space-y-4 mb-6">
        <!-- Buscador -->
        <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input type="text" placeholder="Buscar por ID, usuario, carro o placa..." :value="searchTerm"
                @input="$emit('update:searchTerm', $event.target.value)"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Estatus -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Estatus
                </label>
                <select :value="statusFilter" @change="$emit('update:statusFilter', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="completed">Completado</option>
                    <option value="pending">Pendiente</option>
                    <option value="canceled">Cancelado</option>
                    <option value="booked">Reservado</option>
                </select>
            </div>

            <!-- Método de pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Método de pago
                </label>
                <select :value="paymentFilter" @change="$emit('update:paymentFilter', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="Credit Card">Tarjeta de crédito</option>
                    <option value="Cash">Efectivo</option>
                    <option value="Wallet">Billetera</option>
                </select>
            </div>

            <!-- Con conductor -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Con conductor
                </label>
                <select :value="driverFilter" @change="$emit('update:driverFilter', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="true">Sí</option>
                    <option value="false">No</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
import { Search } from "lucide-vue-next";

export default {
    name: "Filters",
    components: { Search },
    props: {
        searchTerm: String,
        statusFilter: String,
        paymentFilter: String,
        driverFilter: String,
    },
    data() {
        return {};
    },
    computed: {},
    methods: {},
};
</script>
