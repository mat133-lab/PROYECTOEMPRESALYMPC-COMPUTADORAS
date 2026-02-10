import {
    calendarHeading,
    calendarDays,
    previousMonthBtn,
    nextMonthBtn,
    modal as listModal, // Renombrado para evitar conflictos con el modal de formulario
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
        const list = dayElement.querySelector('.calendar__appointments');
        if (list) list.remove();

        dayElement.classList.remove('calendar__day--content');
        delete dayElement.dataset.appointments; // Limpiar data
    }


    static updateCalendarDayContent(dayElement, appointment) {
        // almacenamiento por cada día
        let stored = [];
        if (dayElement.dataset.appointments) {
            stored = JSON.parse(dayElement.dataset.appointments);
        }

        // Evitar duplicados visuales si se llama varias veces
        const exists = stored.some(app => app.id === appointment.id);
        if (!exists) {
            stored.push(appointment);
            dayElement.dataset.appointments = JSON.stringify(stored);
        }

        // Contenedor visual de citas (lista)
        let list = dayElement.querySelector('.calendar__appointments');
        if (!list) {
            list = document.createElement('ul');
            list.className = 'calendar__appointments';
            list.style.marginTop = '5px';
            list.style.padding = '0';
            list.style.listStyle = 'none';
            dayElement.appendChild(list);
            dayElement.classList.add('calendar__day--content');
        }

        // Crear elemento visual para la cita
        const item = document.createElement('li');
        item.className = 'calendar__badge'; // Reutilizamos estilo badge
        item.textContent = appointment.motivo || "Cita";
        item.title = `${appointment.nombre || ''} - ${appointment.motivo || ''}`;
        item.style.display = 'block';
        item.style.marginBottom = '2px';
        item.style.whiteSpace = 'nowrap';
        item.style.overflow = 'hidden';
        item.style.textOverflow = 'ellipsis';
        
        list.appendChild(item);
    }

    static createCalendarModalItem(appointment) {
        const li = document.createElement('li');
        li.className = 'modal__item';
        li.innerHTML = `
            <div class="modal__item__info">
                <h4 class="modal__item__title">${appointment.nombre} ${appointment.apellido}</h4>
                <p class="modal__item__description">${appointment.motivo}</p>
                <p class="modal__item__time">${appointment.correo}</p>
                <p class="modal__item__time">${appointment.telefono}</p>
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
        // Ajuste: en JS getDay() Domingo es 0, pero en CSS grid suele requerir ajuste
        // Si tu semana empieza en Lunes (1), y Domingo es 7:
        const gridColumn = firstWeekDay === 0 ? 7 : firstWeekDay;
        firstDayElement.style.gridColumnStart = gridColumn;
    }

    // Ocultar/mostrar últimos días
    for (let i = 28; i <= 31; i++) {
        const calendarDay = document.querySelector(`.calendar__day[data-day="${i}"]`);
        if (calendarDay) {
            if (i > lastMonthDay) {
                calendarDay.classList.add("calendar__day--hidden");
                calendarDay.style.display = 'none'; // Asegurar ocultamiento
            } else {
                calendarDay.classList.remove("calendar__day--hidden");
                calendarDay.style.display = '';
            }
        }
    }

    // Obtener citas del mes
    // Formato YYYY-MM-DD para la API
    const fechaInicio = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const fechaFin = `${year}-${String(month + 1).padStart(2, '0')}-${lastMonthDay}`;

    await getMonthlyAppointments([fechaInicio, fechaFin]);
}

// Obtener citas del servidor
async function getMonthlyAppointments(dateRange) {
    try {
        const [desde, hasta] = dateRange;
        // Ajusta la ruta si es necesario
        const response = await fetch(
            `../php/api_horarios.php?desde=${desde}&hasta=${hasta}`
        );

        if (!response.ok) throw new Error('Error al obtener las citas');

        const appointments = await response.json();
        displayAppointmentsInCalendar(appointments);
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mostrar citas en calendario (VERSIÓN CORREGIDA - SIN new Date)
function displayAppointmentsInCalendar(appointments) {
    // Limpiar citas previas
    document.querySelectorAll('.calendar__day').forEach(day => {
        UI.cleanCalendarDayContent(day);
    });

    const currentMonthIndex = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();

    // Agregar citas
    appointments.forEach(record => {
        if (!record.fecha) return;

        // CORRECCIÓN CLAVE: Split manual para evitar zona horaria
        // Formato esperado: "YYYY-MM-DD"
        const parts = record.fecha.split(' ')[0].split('-');
        if (parts.length < 3) return;

        const anio = parseInt(parts[0]);
        const mes = parseInt(parts[1]) - 1; // 0-11
        const dia = parseInt(parts[2]);

        // Validar que la cita pertenezca al mes visible
        if (anio === currentYear && mes === currentMonthIndex) {
            const calendarDayContainer = document.querySelector(`.calendar__day[data-day="${dia}"]`);
            if (calendarDayContainer) {
                UI.updateCalendarDayContent(calendarDayContainer, record);
            }
        }
    });
}

// Cambiar mes
export function setMonth(step) {
    const currentMonth = currentDate.getMonth();
    currentDate.setMonth(currentMonth + step);
    renderCalendar();
}

// Cargar modal de LISTA de citas (Ver citas existentes)
export function loadAppointmentsModal(calendarDay) {
    if (!calendarDay) return;

    // Obtener datos del dataset
    const storedAppointments = calendarDay.dataset.appointments;
    if (!storedAppointments) return;

    const appointments = JSON.parse(storedAppointments);

    // Preparar datos para displayAppointmentsInModal
    // Adaptamos el formato para que displayAppointmentsInModal lo entienda
    const appointmentsFormatted = appointments.map(app => ({
        id: app.id,
        text: `${app.nombre} ${app.apellido} - ${app.motivo}`, // Texto simple
        ...app // Pasamos todo el objeto por si acaso
    }));

    displayAppointmentsInModal(appointmentsFormatted, calendarDay);
}

// Mostrar citas en modal de LISTA
function displayAppointmentsInModal(appointments, calendarDay) {
    const day = calendarDay.dataset.day;
    const month = currentDate.getMonth() + 1;
    const year = currentDate.getFullYear();
    const dateString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    modalHeading.textContent = `Citas - ${formatDateString(dateString)}`;
    UI.cleanHTML(modalCalendarList);

    // Usamos el método de UI que ya tenías
    appointments.forEach(app => {
        // Si app tiene estructura completa usa UI.createCalendarModalItem
        // Si es texto simple (del código anterior) usa lógica simple
        if (app.nombre) {
            UI.createCalendarModalItem(app);
        } else {
            const li = document.createElement('li');
            li.className = 'modal__item';
            li.textContent = app.text;
            modalCalendarList.appendChild(li);
        }
    });

    listModal.showModal();
}

// --- LÓGICA DEL FORMULARIO DE CREACIÓN (Integrada) ---

// Función para abrir el formulario de nueva cita
function abrirFormularioCrear(fechaSeleccionada) {
    const formModal = document.getElementById("appointments-modal");
    if (!formModal) return;

    const modalTitle = formModal.querySelector(".modal__heading");
    const listContainer = formModal.querySelector(".modal__list"); // Usamos el contenedor de lista para inyectar el form

    modalTitle.textContent = `Agendar Cita - ${fechaSeleccionada}`;
    formModal.showModal();

    // Inyectar HTML del formulario
    listContainer.innerHTML = `
        <form method="POST" action="../php/guardar_cita.php" class="p-3">
            <input type="hidden" name="fecha" value="${fechaSeleccionada}">
            
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control" value="${window.currentUserEmail || ''}" required ${window.currentUserEmail ? 'readonly' : ''}>
            </div>
            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Motivo</label>
                <textarea name="motivo" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-warning w-100">Agendar cita física</button>
        </form>
    `;

    // Manejar cierre de este modal específico
    const closeBtns = formModal.querySelectorAll(".modal__close, .modal__button--close");
    closeBtns.forEach(btn => {
        btn.onclick = () => formModal.close();
    });
}


// Event Listeners Principales
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();

    // Listener unificado para el calendario
    if (calendarDays) {
        calendarDays.addEventListener('click', (e) => {
            // Buscar si el clic fue dentro de un día
            const dayElement = e.target.closest('.calendar__day');
            if (!dayElement) return;

            const dayNumber = dayElement.dataset.day;
            if (!dayNumber) return;

            // Decisión: ¿Ver citas o Crear cita?
            // Si el clic fue directamente en un badge o el día tiene citas Y el usuario NO hizo clic en un espacio vacío explícitamente...
            // Simplificamos: Si tiene citas -> Ver lista. Si no -> Crear.

            if (dayElement.classList.contains('calendar__day--content')) {
                // VER CITAS
                loadAppointmentsModal(dayElement);
            } else {
                // CREAR CITA
                const month = currentDate.getMonth() + 1;
                const year = currentDate.getFullYear();
                const selectedDate = `${year}-${String(month).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                abrirFormularioCrear(selectedDate);
            }
        });
    }

    // Botones de navegación
    previousMonthBtn?.addEventListener('click', () => setMonth(-1));
    nextMonthBtn?.addEventListener('click', () => setMonth(1));

    // Botones del modal de LISTA
    modalCloseBtn?.addEventListener('click', () => listModal.close());
    modalCancelBtn?.addEventListener('click', () => listModal.close());
});