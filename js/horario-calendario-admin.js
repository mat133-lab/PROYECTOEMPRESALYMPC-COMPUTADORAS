import {
    calendarHeadingAdmin,
    calendarDaysAdmin,
    previousMonthBtnAdmin,
    nextMonthBtnAdmin,
    // Modal de Lista (Ver citas del día)
    modal as listModal,
    modalHeading,
    modalCalendarList,
    modalCloseBtn,
    // Modal de Edición/Creación (El formulario)
    adminModal,
    adminFieldId,
    adminFieldNombre,
    adminFieldApellido,
    adminFieldCorreo,
    adminFieldTelefono,
    adminFieldFecha, // Asegúrate de que en tu HTML este input sea type="date" o "datetime-local"
    adminFieldMotivo,
    btnSaveAppointment,
    btnDeleteAppointment,
    btnModalClose as btnFormClose,
    btnModalCancel as btnFormCancel

} from './selectores-horario-admin.js';

import {
    formatTitle,
    formatDateString
} from './funciones-horario.js';

const currentDate = new Date();

// --- CLASE UI (Adaptada para Admin) ---
class UI {
    static cleanHTML(element) {
        element.innerHTML = '';
    }

    static cleanCalendarDayContent(dayElement) {
        const list = dayElement.querySelector('.calendar__appointments');
        if (list) list.remove();
        dayElement.classList.remove('calendar__day--content');
        delete dayElement.dataset.appointments;
    }

    static updateCalendarDayContent(dayElement, appointment) {
        let stored = [];
        if (dayElement.dataset.appointments) {
            stored = JSON.parse(dayElement.dataset.appointments);
        }

        const exists = stored.some(app => app.id === appointment.id);
        if (!exists) {
            stored.push(appointment);
            dayElement.dataset.appointments = JSON.stringify(stored);
        }

        let list = dayElement.querySelector('.calendar__appointments');
        if (!list) {
            list = document.createElement('ul');
            list.className = 'calendar__appointments';
            dayElement.appendChild(list);
            dayElement.classList.add('calendar__day--content');
        }

        const item = document.createElement('li');

        // --- CORRECCIÓN AQUÍ ---
        // Antes tenías 'calendar__badge', ahora usamos tu clase CSS correcta:
        item.className = 'cita-estilo';

        // Contenido del texto
        item.textContent = `${appointment.nombre} - ${appointment.motivo}`;
        item.title = `${appointment.nombre} ${appointment.apellido}`;

        // --- LIMPIEZA ---
        // He eliminado todos los item.style.display, item.style.marginBottom, etc.
        // porque la clase .cita-estilo de tu CSS ya se encarga de eso.

        list.appendChild(item);
    }

    // Crear item en la lista del modal (con opción de click para editar)
    static createAdminModalItem(appointment) {
        const li = document.createElement('li');
        li.className = 'modal__item';
        li.style.cursor = 'pointer'; // Indicar que es clickeable
        li.innerHTML = `
            <div class="modal__item__info">
                <h4 class="modal__item__title">${appointment.nombre} ${appointment.apellido} <span style="font-size:0.8em; color:#ff9100;">(Editar)</span></h4>
                <p class="modal__item__description">${appointment.motivo}</p>
                <p class="modal__item__time">${appointment.telefono} | ${appointment.correo}</p>
            </div>
        `;

        // Al hacer clic en el item de la lista, abrimos el formulario de edición
        li.addEventListener('click', () => {
            listModal.close(); // Cerramos lista
            openAdminForm(appointment); // Abrimos formulario lleno
        });

        modalCalendarList.appendChild(li);
    }
}

// --- RENDERIZAR CALENDARIO ---
export async function renderCalendar() {
    const month = currentDate.getMonth();
    const year = currentDate.getFullYear();

    const firstMonthDate = new Date(year, month, 1);
    const lastMonthDate = new Date(year, month + 1, 0);

    const calendarTitle = currentDate.toLocaleDateString("es-CO", {
        month: "long",
        year: "numeric"
    });

    if (calendarHeadingAdmin) calendarHeadingAdmin.textContent = formatTitle(calendarTitle);

    const firstWeekDay = firstMonthDate.getDay();
    const lastMonthDay = lastMonthDate.getDate();

    // Ajustar grid
    const firstDayElement = document.querySelector('.calendar__day[data-day="1"]');
    if (firstDayElement && firstDayElement.parentElement) {
        const gridColumn = firstWeekDay === 0 ? 7 : firstWeekDay;
        firstDayElement.style.gridColumnStart = gridColumn;
    }

    // Ocultar/Mostrar días sobrantes
    for (let i = 28; i <= 31; i++) {
        const calendarDay = document.querySelector(`.calendar__day[data-day="${i}"]`);
        if (calendarDay) {
            if (i > lastMonthDay) {
                calendarDay.classList.add("calendar__day--hidden");
                calendarDay.style.display = 'none';
            } else {
                calendarDay.classList.remove("calendar__day--hidden");
                calendarDay.style.display = '';
            }
        }
    }

    // Obtener citas (API ADMIN)
    const fechaInicio = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const fechaFin = `${year}-${String(month + 1).padStart(2, '0')}-${lastMonthDay}`;

    await getMonthlyAppointments([fechaInicio, fechaFin]);
}

async function getMonthlyAppointments(dateRange) {
    try {
        const [desde, hasta] = dateRange;
        // Usamos ruta relativa segura y el archivo PHP de admin (si es el mismo, usa api_horarios.php)
        const response = await fetch(
            `../php/api_horarios_admin.php?desde=${desde}&hasta=${hasta}`
        );

        if (!response.ok) throw new Error('Error al obtener citas admin');

        const appointments = await response.json();
        displayAppointmentsInCalendar(appointments);
    } catch (error) {
        console.error('Error:', error);
    }
}

function displayAppointmentsInCalendar(appointments) {
    // Limpiar
    document.querySelectorAll('.calendar__day').forEach(day => {
        UI.cleanCalendarDayContent(day);
    });

    const currentMonthIndex = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();

    appointments.forEach(record => {
        if (!record.fecha) return;

        // --- CORRECCIÓN DE FECHA (Igual que en el cliente) ---
        const parts = record.fecha.split(' ')[0].split('-');
        if (parts.length < 3) return;

        const anio = parseInt(parts[0]);
        const mes = parseInt(parts[1]) - 1;
        const dia = parseInt(parts[2]);

        if (anio === currentYear && mes === currentMonthIndex) {
            const calendarDayContainer = document.querySelector(`.calendar__day[data-day="${dia}"]`);
            if (calendarDayContainer) {
                UI.updateCalendarDayContent(calendarDayContainer, record);
            }
        }
    });
}

// --- NAVEGACIÓN ---
export function setMonth(step) {
    const currentMonth = currentDate.getMonth();
    currentDate.setMonth(currentMonth + step);
    renderCalendar();
}

// --- MODALES Y FORMULARIOS ---

// 1. Mostrar Lista de Citas (Solo lectura/selección)
function loadAppointmentsListModal(calendarDay) {
    const storedAppointments = calendarDay.dataset.appointments;
    const day = calendarDay.dataset.day;
    const month = currentDate.getMonth() + 1;
    const year = currentDate.getFullYear();
    const dateString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    // Título del modal lista
    modalHeading.textContent = `Gestión - ${formatDateString(dateString)}`;

    // Botón para agregar nueva cita DESDE la lista
    const btnAddInList = document.createElement('button');
    btnAddInList.textContent = "+ Nueva Cita Aquí";
    btnAddInList.className = "modal__button modal__button--primary";
    btnAddInList.style.width = "100%";
    btnAddInList.style.marginBottom = "10px";
    btnAddInList.onclick = () => {
        listModal.close();
        openAdminForm(null, dateString);
    };

    UI.cleanHTML(modalCalendarList);
    modalCalendarList.appendChild(btnAddInList);

    if (storedAppointments) {
        const appointments = JSON.parse(storedAppointments);
        appointments.forEach(app => UI.createAdminModalItem(app));
    }

    listModal.showModal();
}

// 2. Abrir Formulario (Crear o Editar)
function openAdminForm(cita = null, fechaPreseleccionada = null) {
    if (!adminModal) return;

    const modalTitle = adminModal.querySelector('.modal__heading');

    if (cita) {
        // MODO EDICIÓN
        modalTitle.textContent = 'Editar Cita';
        adminFieldId.value = cita.id;
        adminFieldNombre.value = cita.nombre;
        adminFieldApellido.value = cita.apellido;
        adminFieldCorreo.value = cita.correo;
        adminFieldTelefono.value = cita.telefono;
        adminFieldMotivo.value = cita.motivo;

        // Manejo de fecha para input type="date"
        // Si viene "YYYY-MM-DD HH:MM:SS", cortamos
        const fechaSolo = cita.fecha.split(' ')[0];
        adminFieldFecha.value = fechaSolo;

        if (btnDeleteAppointment) btnDeleteAppointment.style.display = 'block'; // Mostrar botón borrar
    } else {
        // MODO CREACIÓN
        modalTitle.textContent = 'Nueva Cita Administrativa';
        adminFieldId.value = ''; // ID vacío indica creación
        adminFieldNombre.value = '';
        adminFieldApellido.value = '';
        adminFieldCorreo.value = '';
        adminFieldTelefono.value = '';
        adminFieldMotivo.value = '';

        if (fechaPreseleccionada) {
            adminFieldFecha.value = fechaPreseleccionada;
        }

        if (btnDeleteAppointment) btnDeleteAppointment.style.display = 'none'; // Ocultar botón borrar
    }

    adminModal.showModal();
}

// 3. Guardar (Create / Update)
async function saveAppointment(e) {
    e.preventDefault();

    const payload = {
        id: adminFieldId.value || null, // Si tiene ID es Update, si no es Create
        nombre: adminFieldNombre.value.trim(),
        apellido: adminFieldApellido.value.trim(),
        correo: adminFieldCorreo.value.trim(),
        telefono: adminFieldTelefono.value.trim(),
        fecha: adminFieldFecha.value,
        motivo: adminFieldMotivo.value.trim()
    };

    // Validar
    if (!payload.fecha || !payload.nombre || !payload.motivo) {
        alert("Por favor completa los campos obligatorios");
        return;
    }

    try {
        const method = payload.id ? 'PUT' : 'POST';
        const url = '../php/api_horarios_admin.php'; // Misma URL, el método define la acción

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success || response.ok) {
            adminModal.close();
            renderCalendar(); // Recargar calendario
            // alert('Guardado correctamente'); // Opcional
        } else {
            alert('Error al guardar: ' + (result.message || 'Desconocido'));
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al guardar');
    }
}

// 4. Eliminar
async function deleteAppointment() {
    const id = adminFieldId.value;
    if (!id) return;

    if (!confirm('¿Estás seguro de eliminar esta cita permanentemente?')) return;

    try {
        const response = await fetch(`../php/api_horarios_admin.php?id=${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            adminModal.close();
            renderCalendar();
        } else {
            alert('No se pudo eliminar');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al eliminar');
    }
}

// --- EVENT LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();

    // Navegación
    previousMonthBtnAdmin?.addEventListener('click', () => setMonth(-1));
    nextMonthBtnAdmin?.addEventListener('click', () => setMonth(1));

    // Clic en el calendario
    if (calendarDaysAdmin) {
        calendarDaysAdmin.addEventListener('click', (e) => {
            const dayElement = e.target.closest('.calendar__day');
            if (!dayElement) return;

            const dayNumber = dayElement.dataset.day;
            if (!dayNumber) return;

            // Lógica Admin:
            // Si hay citas -> Abrir Lista.
            // Si no -> Abrir Formulario Crear.
            if (dayElement.classList.contains('calendar__day--content')) {
                loadAppointmentsListModal(dayElement);
            } else {
                const month = currentDate.getMonth() + 1;
                const year = currentDate.getFullYear();
                const selectedDate = `${year}-${String(month).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                openAdminForm(null, selectedDate);
            }
        });
    }

    // Botones del Modal Lista
    modalCloseBtn?.addEventListener('click', () => listModal.close());

    // Botones del Modal Formulario (Admin)
    btnSaveAppointment?.addEventListener('click', saveAppointment);
    btnDeleteAppointment?.addEventListener('click', deleteAppointment);
    btnFormClose?.addEventListener('click', () => adminModal.close());
    btnFormCancel?.addEventListener('click', () => adminModal.close());
});