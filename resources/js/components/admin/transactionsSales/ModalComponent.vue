<template>
    <div>
        <h2>Detalles de la Transacción</h2>
        <p>Cliente: {{ transaction.client_name }}</p>
        <p>Fecha: {{ transaction.date }}</p>
        <p>Monto: {{ transaction.sign }} {{ transaction.amount }}</p>
        <p>Estado: {{ transaction.status_transaction }}</p>
        <button @click="$emit('close')">Cerrar</button>
    </div>
</template>

<script>
export default {
    name: "TransactionListComponent",
    props: {
        transaction: Object
    }
}
</script>
