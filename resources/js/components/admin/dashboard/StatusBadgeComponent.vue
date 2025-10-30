<template>
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
        :class="getStatusColor(status)">
        {{ getStatusLabel(status) }}
    </span>
</template>

<script>
import { getStatusColor, getStatusLabel } from '../../utils/formatters';

export default {
    name: "StatusBadge",
    components: {},
    props: {
        status: {
            type: String,
            required: true,
        },
    },
    data() {
        return {};
    },
    computed: {},
    methods: {
        getStatusColor,
        getStatusLabel,
    },
};
</script>
