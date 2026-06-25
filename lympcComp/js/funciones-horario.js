// Funciones de utilidad para el calendario

export function formatTitle(title) {
    return title.charAt(0).toUpperCase() + title.slice(1).replace(/_/g, ' ');
}

export function formatDateString(date) {
    const dateString = new Date(date).toLocaleDateString("es-CO", {
        month: 'long',
        day: 'numeric',
    });

    const dateStringSplitted = dateString.split(" ");
    dateStringSplitted[1] = dateStringSplitted[1].charAt(0).toUpperCase() + dateStringSplitted[1].slice(1);
    const formattedString = dateStringSplitted.join(" ");
    return formattedString;
}

export function formatTime(date) {
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const timePeriod = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12 || 12;
    return `${hours}:${minutes} ${timePeriod}`;
}

export function formatDateRange(range) {
    const ISODates = range.map(date => date.toISOString().split("T")[0]);
    const startDate = ISODates[0] + "T00:00";
    const endDate = ISODates[1] + "T23:59";
    return [startDate, endDate];
}

export function reloadPage() {
    window.location.reload();
}
