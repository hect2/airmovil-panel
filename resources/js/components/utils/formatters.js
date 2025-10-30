export function formatDate(dateString) {
    if (!dateString) return "—";
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

export function formatCurrency(amount) {
    if (amount == null || isNaN(amount)) return "Q 0.00";
    return `Q ${amount.toLocaleString("es-GT", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export function formatTime(time) {
    if (!time) return "—";
    const parts = time.split(":");
    if (parts.length >= 2) {
        return `${parts[0].padStart(2, "0")}:${parts[1].padStart(2, "0")}`;
    }
    return time;
}

export function getStatusColor(status) {
    switch (status) {
        case "completed":
            return "bg-green-100 text-green-800 border-green-200";
        case "pending":
            return "bg-amber-100 text-amber-800 border-amber-200";
        case "canceled":
            return "bg-red-100 text-red-800 border-red-200";
        case "booked":
            return "bg-blue-100 text-blue-800 border-blue-200";
        default:
            return "bg-gray-100 text-gray-800 border-gray-200";
    }
}

export function getStatusLabel(status) {
    switch (status) {
        case "completed":
            return "Completado";
        case "pending":
            return "Pendiente";
        case "canceled":
            return "Cancelado";
        case "booked":
            return "Reservado";
        default:
            return status || "Desconocido";
    }
}
