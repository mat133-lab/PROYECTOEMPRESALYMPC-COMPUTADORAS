import {
    calendarHeading,
    calendarDays,
    previousMonthBtn,
    nextMonthBtn,
    modal,
    modalHeading,
    modalCalendarList,
    modalCloseBtn,
    modalCancelBtn,
} from './selectores-horario.js';

import {
    formatTitle,
    formatDateString,
    formatDateRange,
    formatTime
} from './funciones-horario.js';

const currentDate = new Date();

// Clase UI para manipular el DOM
class UI {
    static cleanHTML(element) {
        element.innerHTML = '';
    }

    static cleanCalendarDayContent(dayElement) {
        const appointmentsContainer = dayElement.querySelector('.calendar__appointments');
        if (appointmentsContainer) {
            appointmentsContainer.remove();
        }
        dayElement.classList.remove('calendar__day--content');
    }

    static updateCalendarDayContent(dayElement, appointment) {
        // Si no existe el contenedor de citas, crearlo
        let appointmentsContainer = dayElement.querySelector('.calendar__appointments');
        if (!appointmentsContainer) {
            appointmentsContainer = document.createElement('ul');
            appointmentsContainer.className = 'calendar__appointments';
            dayElement.classList.add('calendar__day--content');
            dayElement.appendChild(appointmentsContainer);
        }

        // Crear elemento de cita
        const appointmentItem = document.createElement('li');
        appointmentItem.dataset.id = appointment.id;
        appointmentItem.className = 'calendar__appointment';
        appointmentItem.textContent = appointment.nombre + ' ' + appointment.apellido;
        appointmentsContainer.appendChild(appointmentItem);
    }

    static createCalendarModalItem(appointment) {
        const li = document.createElement('li');
        li.className = 'modal__item';
        li.innerHTML = `
            <div class="modal__item__info">
                <h4 class="modal__item__title">${appointment.nombre} ${appointment.apellido}</h4>
                <p class="modal__item__description">${appointment.motivo}</p>
                <p class="modal__item__time">📧 ${appointment.correo}</p>
                <p class="modal__item__time">📱 ${appointment.telefono}</p>
            </div>
        `;
        modalCalendarList.appendChild(li);
    }
}

// Renderizar calendario
export async function renderCalendar() {
    const month = currentDate.getMonth();
    const year = currentDate.getFullYear();

    const firstMonthDate = new Date(year, month, 1);
    const lastMonthDate = new Date(year, month + 1, 0);

    const calendarTitle = currentDate.toLocaleDateString("es-CO", {
        month: "long",
        year: "numeric"
    });

    calendarHeading.textContent = formatTitle(calendarTitle);

    const firstWeekDay = firstMonthDate.getDay();
    const lastMonthDay = lastMonthDate.getDate();

    // Ajustar grid al primer día de la semana
    const firstDayElement = document.querySelector('.calendar__day[data-day="1"]');
    if (firstDayElement && firstDayElement.parentElement) {
        firstDayElement.parentElement.style.gridColumnStart = firstWeekDay === 0 ? 7 : firstWeekDay;
    }

    // Ocultar/mostrar últimos días
    for (let i = 28; i <= 31; i++) {
        const calendarDay = document.querySelector(`.calendar__day[data-day="${i}"]`);
        if (calendarDay) {
            calendarDay.classList.toggle("calendar__day--hidden", i > lastMonthDay);
        }
    }

    // Obtener citas del mes
    await getMonthlyAppointments(formatDateRange([firstMonthDate, lastMonthDate]));
}

// Obtener citas del servidor
async function getMonthlyAppointments(dateRange) {
    try {
        const [desde, hasta] = dateRange;
        const response = await fetch(
            `/lymPCComputadoras/php/api_horarios.php?desde=${desde}&hasta=${hasta}`
        );
        
        if (!response.ok) throw new Error('Error al obtener las citas');
        
        const appointments = await response.json();
        displayAppointmentsInCalendar(appointments);
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mostrar citas en calendario
function displayAppointmentsInCalendar(appointments) {
    // Limpiar citas previas
    document.querySelectorAll('.calendar__day').forEach(day => {
        UI.cleanCalendarDayContent(day);
    });

    // Agregar citas
    appointments.forEach(record => {
        const date = new Date(record.fecha);
        const day = date.getDate();
        const calendarDayContainer = document.querySelector(`.calendar__day[data-day="${day}"]`);
        
        if (calendarDayContainer) {
            UI.updateCalendarDayContent(calendarDayContainer, record);
        }
    });
}

// Cambiar mes
export function setMonth(step) {
    const currentMonth = currentDate.getMonth();
    currentDate.setMonth(currentMonth + step);
    renderCalendar();
}

// Cargar modal de citas
export function loadAppointmentsModal(e) {
    const calendarDay = e.target.closest(".calendar__day--content");
    if (!calendarDay) return;

    const appointmentsElements = calendarDay.querySelectorAll(".calendar__appointment");
    if (appointmentsElements.length === 0) return;

    const appointments = [];
    appointmentsElements.forEach(item => {
        const text = item.textContent.trim();
        const parts = text.split(' ');
        appointments.push({
            id: item.dataset.id,
            text: text
        });
    });

    displayAppointmentsInModal(appointments, calendarDay);
}

// Mostrar citas en modal
function displayAppointmentsInModal(appointments, calendarDay) {
    const day = calendarDay.dataset.day;
    const month = currentDate.getMonth() + 1;
    const year = currentDate.getFullYear();
    const dateString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    
    modalHeading.textContent = `Citas - ${formatDateString(dateString)}`;
    UI.cleanHTML(modalCalendarList);
    
    appointments.forEach(appointment => {
        const li = document.createElement('li');
        li.className = 'modal__item';
        li.textContent = appointment.text;
        modalCalendarList.appendChild(li);
    });
    
    openModal();
}

// Abrir modal
function openModal() {
    modal.showModal();
}

// Cerrar modal
function closeModal() {
    modal.close();
}

// Event Listeners
document.addEventListener('DOMContentLoaded', renderCalendar);

previousMonthBtn?.addEventListener('click', () => setMonth(-1));
nextMonthBtn?.addEventListener('click', () => setMonth(1));

calendarDays?.addEventListener('click', loadAppointmentsModal);
modalCancelBtn?.addEventListener('click', closeModal);
