const GUATEMALA_TZ = 'America/Guatemala';

function toGuatemalaDate(isoString) {
    return new Date(new Date(isoString).toLocaleString('en-US', {
        timeZone: GUATEMALA_TZ
    }));
}

function getBucketLabel(date, bucket) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hour = String(date.getHours()).padStart(2, '0');

    switch (bucket) {
        case 'hour':
            return `${day}/${month} ${hour}:00`;
        case 'day':
            return `${day}/${month}/${year}`;
        case 'week':
            const weekStart = new Date(date);
            weekStart.setDate(date.getDate() - date.getDay());
            return `Sem ${weekStart.getDate()}/${weekStart.getMonth() + 1}`;
        case 'month':
            return `${month}/${year}`;
        default:
            return `${day}/${month}/${year}`;
    }
}

function filterByDateRange(items, range) {
    return items.filter(item => {
        const date = toGuatemalaDate(item.createdAt);
        return date >= range.start && date <= range.end;
    });
}

export function buildDashboardData(params) {
    const {
        txs,
        reservations,
        filters,
        previousPeriodTxs = [],
        previousPeriodReservations = []
    } = params;

    // Filter by date range
    const filteredTxs = filterByDateRange(txs, filters.range)
        .filter(tx => 
            (filters.txStatuses.length === 0 || filters.txStatuses.includes(tx.status)) &&
            (filters.paymentMethods.length === 0 || filters.paymentMethods.includes(tx.method))
        );

    const filteredRes = filterByDateRange(reservations, filters.range)
        .filter(res => filters.resStatuses.length === 0 || filters.resStatuses.includes(res.status));

    const prevTxs = filterByDateRange(previousPeriodTxs, {
        start: new Date(filters.range.start.getTime() - (filters.range.end.getTime() - filters.range.start.getTime())),
        end: filters.range.start
    });

    const prevRes = filterByDateRange(previousPeriodReservations, {
        start: new Date(filters.range.start.getTime() - (filters.range.end.getTime() - filters.range.start.getTime())),
        end: filters.range.start
    });

    // KPIs
    const acceptedTxs = filteredTxs.filter(tx => tx.status === 'ACCEPT');
    const totalSales = acceptedTxs.reduce((sum, tx) => sum + tx.amount, 0);
    const totalOrders = filteredTxs.length;
    const totalReservations = filteredRes.length;
    const declinedTxs = filteredTxs.filter(tx => tx.status === 'DECLINE');
    const approvalRate = totalOrders > 0 ? acceptedTxs.length / (acceptedTxs.length + declinedTxs.length) * 100 : 0;
    const averageTicket = acceptedTxs.length > 0 ? totalSales / acceptedTxs.length : 0;

    // Previous period KPIs
    const prevAcceptedTxs = prevTxs.filter(tx => tx.status === 'ACCEPT');
    const prevTotalSales = prevAcceptedTxs.reduce((sum, tx) => sum + tx.amount, 0);
    const prevTotalOrders = prevTxs.length;
    const prevTotalReservations = prevRes.length;
    const prevDeclinedTxs = prevTxs.filter(tx => tx.status === 'DECLINE');
    const prevApprovalRate = prevTotalOrders > 0 ? prevAcceptedTxs.length / (prevAcceptedTxs.length + prevDeclinedTxs.length) * 100 : 0;
    const prevAverageTicket = prevAcceptedTxs.length > 0 ? prevTotalSales / prevAcceptedTxs.length : 0;

    const calculateDelta = (current, previous) => {
        if (previous === 0) return current > 0 ? 100 : 0;
        return (current - previous) / previous * 100;
    };

    // Build time series
    const bucketMap = new Map();
    const resBucketMap = new Map();

    filteredTxs.forEach(tx => {
        const label = getBucketLabel(toGuatemalaDate(tx.createdAt), filters.bucket);
        const current = bucketMap.get(label) || { amount: 0, count: 0, byStatus: {} };
        if (tx.status === 'ACCEPT') current.amount += tx.amount;
        current.count += 1;
        current.byStatus[tx.status] = (current.byStatus[tx.status] || 0) + 1;
        bucketMap.set(label, current);
    });

    filteredRes.forEach(res => {
        const label = getBucketLabel(toGuatemalaDate(res.createdAt), filters.bucket);
        const current = resBucketMap.get(label) || { total: 0, completed: 0 };
        current.total += 1;
        if (res.status === 'completed') current.completed += 1;
        resBucketMap.set(label, current);
    });

    const txAmountByBucket = Array.from(bucketMap.entries()).map(([bucketLabel, data]) => ({
        bucketLabel,
        amount: data.amount,
        count: data.count
    })).sort((a, b) => a.bucketLabel.localeCompare(b.bucketLabel));

    const txByStatusStacked = Array.from(bucketMap.entries()).map(([bucketLabel, data]) => ({
        bucketLabel,
        ACCEPT: data.byStatus.ACCEPT || 0,
        DECLINE: data.byStatus.DECLINE || 0,
        PENDING: data.byStatus.PENDING || 0,
        REFUND: data.byStatus.REFUND || 0,
        CHARGEBACK: data.byStatus.CHARGEBACK || 0
    })).sort((a, b) => a.bucketLabel.localeCompare(b.bucketLabel));

    const methodMap = new Map();
    filteredTxs.forEach(tx => {
        const current = methodMap.get(tx.method) || { count: 0, amount: 0 };
        current.count += 1;
        if (tx.status === 'ACCEPT') current.amount += tx.amount;
        methodMap.set(tx.method, current);
    });

    const txByMethodPie = Array.from(methodMap.entries()).map(([name, data]) => ({
        name,
        value: data.count,
        amount: data.amount
    }));

    const resCountByBucket = Array.from(resBucketMap.entries()).map(([bucketLabel, data]) => ({
        bucketLabel,
        total: data.total,
        completed: data.completed
    })).sort((a, b) => a.bucketLabel.localeCompare(b.bucketLabel));

    const typeMap = new Map();
    filteredRes.forEach(res => {
        const type = res.car?.type?.title || 'Sin tipo';
        const current = typeMap.get(type) || { reservas: 0, horas: 0 };
        current.reservas += 1;
        typeMap.set(type, current);
    });

    const resByType = Array.from(typeMap.entries()).map(([type, data]) => ({
        type,
        reservas: data.reservas,
        horas: data.horas
    }));

    const statusMap = new Map();
    filteredRes.forEach(res => {
        statusMap.set(res.status, (statusMap.get(res.status) || 0) + 1);
    });

    const resStatusRadial = Array.from(statusMap.entries()).map(([name, value]) => ({ name, value }));

    const carMap = new Map();
    filteredRes.forEach(res => {
        const key = res.car?.plate || res.car?.title || 'Sin identificar';
        const current = carMap.get(key) || { title: res.car?.title || 'Sin título', plate: res.car?.plate, reservas: 0, ingresos: 0 };
        current.reservas += 1;
        current.ingresos += res.totalAmount;
        carMap.set(key, current);
    });

    const topCars = Array.from(carMap.values()).sort((a, b) => b.ingresos - a.ingresos).slice(0, 10);

    return {
        kpis: {
            totalSales,
            totalOrders,
            totalReservations,
            approvalRate,
            averageTicket,
            occupancyRate: 0,
            deltas: {
                totalSales: calculateDelta(totalSales, prevTotalSales),
                totalOrders: calculateDelta(totalOrders, prevTotalOrders),
                totalReservations: calculateDelta(totalReservations, prevTotalReservations),
                approvalRate: calculateDelta(approvalRate, prevApprovalRate),
                averageTicket: calculateDelta(averageTicket, prevAverageTicket),
                occupancyRate: 0
            }
        },
        series: {
            txAmountByBucket,
            txByStatusStacked,
            txByMethodPie,
            resCountByBucket,
            resByType,
            resStatusRadial,
            topCars
        }
    };
}
