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
    adminFieldCedula,
    adminFieldArchivoRuc,
    adminFieldArchivoCedula,
    adminCurrentRuc,
    adminCurrentArchivoCedula,
    adminFieldTelefono,
    adminFieldFecha, // Asegúrate de que en tu HTML este input sea type="date" o "datetime-local"
    adminFieldMotivo,
    btnSaveAppointment,
    btnDeleteAppointment,
    btnModalClose as btnFormClose,
    btnModalCancel as btnFormCancel,
    appointmentsTable,
    btnNewAppointment

} from './selectores-horario-admin.js';

import {
    formatTitle,
    formatDateString
} from './funciones-horario.js';

const currentDate = new Date();
let calendarRequestId = 0;

function showCalendarFeedback(message, type = 'danger') {
    let feedback = document.getElementById('calendar-feedback-admin');
    if (!feedback && calendarDaysAdmin?.parentElement) {
        feedback = document.createElement('div');
        feedback.id = 'calendar-feedback-admin';
        feedback.setAttribute('role', 'status');
        calendarDaysAdmin.parentElement.insertBefore(feedback, calendarDaysAdmin);
    }
    if (!feedback) return;
    feedback.className = message ? `alert alert-${type} py-2` : '';
    feedback.textContent = message;
    feedback.hidden = !message;
}

function showCurrentDocument(container, url, label) {
    if (!container) return;
    container.replaceChildren();

    if (!url) {
        container.textContent = 'Sin archivo actual.';
        return;
    }

    const link = document.createElement('a');
    link.href = url;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    link.textContent = `Ver ${label} actual`;
    container.appendChild(link);
}

function clearCalendarAppointments() {
    document.querySelectorAll('.calendar__day').forEach(day => UI.cleanCalendarDayContent(day));
}



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
            try {
                const parsed = JSON.parse(dayElement.dataset.appointments);
                stored = Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                console.error('No se pudieron leer las citas guardadas en el calendario.', error);
            }
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
        item.className = 'cita-estilo';

        // Contenido del texto
        item.textContent = `${appointment.nombre} - ${appointment.motivo}`;
        item.title = `${appointment.nombre} ${appointment.apellido}`;

        // --- LIMPIEZA ---
        // He eliminado todos los item.style.display, item.style.marginBottom, etc.
        // porque la clase .cita-estilo del CSS ya se encarga de eso.

        list.appendChild(item);
    }

    // Crear item en la lista del modal (con opción de click para editar)
    static createAdminModalItem(appointment) {
        const li = document.createElement('li');
        li.className = 'modal__item';

        const info = document.createElement('div');
        info.className = 'modal__item__info';

        const title = document.createElement('h4');
        title.className = 'modal__item__title';
        title.textContent = `${appointment.nombre || ''} ${appointment.apellido || ''}`.trim();

        const reason = document.createElement('p');
        reason.className = 'modal__item__description';
        reason.textContent = appointment.motivo || '';

        const contact = document.createElement('p');
        contact.className = 'modal__item__time';
        contact.textContent = `${appointment.telefono || ''} | ${appointment.correo || ''}`;

        const identity = document.createElement('p');
        identity.className = 'modal__item__time';
        identity.textContent = `Cédula: ${appointment.cedula || 'No proporcionada'}`;

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn btn-warning text-white';
        editButton.textContent = 'Editar';
        editButton.addEventListener('click', () => {
            listModal.close();
            openAdminForm(appointment);
        });

        info.append(title, reason, contact, identity, editButton);
        li.appendChild(info);
        modalCalendarList.appendChild(li);
    }
}

// --- RENDERIZAR CALENDARIO ---
export async function renderCalendar() {
    const requestId = ++calendarRequestId;
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

    // Marcar días con número para hover
    document.querySelectorAll('.calendar__day').forEach(day => {
        const numberSpan = day.querySelector('.day-number');
        if (numberSpan && numberSpan.textContent.trim()) {
            day.classList.add('has-number');
        } else {
            day.classList.remove('has-number');
        }
    });

    // Obtener citas (API ADMIN)
    const fechaInicio = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const fechaFin = `${year}-${String(month + 1).padStart(2, '0')}-${lastMonthDay}`;

    clearCalendarAppointments();
    showCalendarFeedback('Cargando citas…', 'info');
    await getMonthlyAppointments([fechaInicio, fechaFin], requestId);
}

async function getMonthlyAppointments(dateRange, requestId) {
    try {
        const [desde, hasta] = dateRange;
        // Usamos ruta relativa segura y el archivo PHP de admin (si es el mismo, usa api_horarios.php)
        const response = await fetch(
            `../php/api_horarios_admin.php?desde=${desde}&hasta=${hasta}`
        );

        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.error || 'No se pudieron cargar las citas.');
        }

        const appointments = await response.json();
        if (!Array.isArray(appointments)) throw new Error('La respuesta de citas no tiene el formato esperado.');
        if (requestId !== calendarRequestId) return;
        displayAppointmentsInCalendar(appointments);
        showCalendarFeedback(appointments.length ? '' : 'No hay citas registradas para este mes.', appointments.length ? 'info' : 'secondary');
    } catch (error) {
        if (requestId !== calendarRequestId) return;
        clearCalendarAppointments();
        showCalendarFeedback(error.message || 'No se pudieron cargar las citas.');
        console.error('Error al cargar el calendario:', error);
    }
}

async function refreshUpcomingAppointments() {
    if (!appointmentsTable) return;

    try {
        const response = await fetch('../php/api_horarios_admin.php?upcoming=1');
        if (!response.ok) throw new Error('No se pudieron actualizar las próximas citas.');
        const appointments = await response.json();
        if (!Array.isArray(appointments)) throw new Error('La lista de citas tiene un formato no válido.');

        const rows = appointments.map(appointment => {
            const row = document.createElement('tr');
            const descriptionCell = document.createElement('td');
            const customerName = document.createElement('div');
            customerName.className = 'cita-item-nombre';
            customerName.textContent = `${appointment.nombre || ''} ${appointment.apellido || ''}`.trim();
            const date = String(appointment.fecha || '').slice(0, 10).split('-').map(Number);
            const dateLabel = document.createElement('div');
            dateLabel.className = 'cita-item-fecha';
            dateLabel.textContent = date.length === 3 && date.every(Number.isFinite)
                ? new Date(date[0], date[1] - 1, date[2], 12).toLocaleDateString('es-EC', { day: '2-digit', month: 'short' })
                : 'Fecha no disponible';
            descriptionCell.append(customerName, dateLabel);

            const actionsCell = document.createElement('td');
            actionsCell.className = 'text-end';
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'btn-icon btn-edit-custom btn-edit';
            editButton.dataset.id = String(appointment.id);
            editButton.title = 'Editar';
            editButton.textContent = '✏️';
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn-icon btn-delete-custom btn-delete';
            deleteButton.dataset.id = String(appointment.id);
            deleteButton.title = 'Eliminar';
            deleteButton.textContent = '🗑️';
            actionsCell.append(editButton, deleteButton);
            row.append(descriptionCell, actionsCell);
            return row;
        });

        if (rows.length === 0) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = 2;
            emptyCell.className = 'text-center text-muted py-3';
            emptyCell.textContent = 'No hay citas próximas.';
            emptyRow.appendChild(emptyCell);
            rows.push(emptyRow);
        }

        appointmentsTable.replaceChildren(...rows);
    } catch (error) {
        console.error('Error al actualizar la lista de próximas citas:', error);
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
    currentDate.setDate(1);
    currentDate.setMonth(currentDate.getMonth() + step);
    renderCalendar();
}

// --- MODALES Y FORMULARIOS ---
// 1. Mostrar Lista de Citas (Solo lectura/selección)
function loadAppointmentsListModal(calendarDay) {
    // --- PROTECCIÓN CONTRA EL CONGELAMIENTO ---
    if (!listModal) {
        console.error("ERROR CRÍTICO: No se encuentra el <dialog id='modal-calendar'> en el HTML.");
        alert("Falta el componente modal-calendar en el HTML. Revisa la consola.");
        return;
    }
    // ------------------------------------------

    const storedAppointments = calendarDay.dataset.appointments;
    const day = calendarDay.dataset.day;
    const month = currentDate.getMonth() + 1;
    const year = currentDate.getFullYear();
    const dateString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

    // Título del modal lista
    if (modalHeading) modalHeading.textContent = `Gestión - ${formatDateString(dateString)}`;

    // Limpiamos la lista anterior
    UI.cleanHTML(modalCalendarList);

    // Botón para agregar nueva cita DESDE la lista
    const btnAddInList = document.createElement('button');
    btnAddInList.textContent = "+ Nueva Cita Aquí";
    btnAddInList.className = "btn btn-warning w-100 mb-2 fw-bold text-white"; // Clases Bootstrap

    btnAddInList.onclick = () => {
        listModal.close();
        // Pequeño timeout para evitar conflicto de modales
        setTimeout(() => {
            openAdminForm(null, dateString);
        }, 100);
    };

    modalCalendarList.appendChild(btnAddInList);

    if (storedAppointments) {
        try {
            const appointments = JSON.parse(storedAppointments);
            if (Array.isArray(appointments)) {
                appointments.forEach(app => UI.createAdminModalItem(app));
            }
        } catch (error) {
            console.error('No se pudieron abrir las citas de este día.', error);
            const notice = document.createElement('li');
            notice.textContent = 'No se pudieron cargar las citas de este día. Actualiza el calendario e inténtalo de nuevo.';
            notice.className = 'text-danger text-center small my-3';
            modalCalendarList.appendChild(notice);
        }
    } else {
        // Mensaje si no hay citas
        const noCitas = document.createElement('li');
        noCitas.textContent = "No hay citas programadas para hoy.";
        noCitas.className = "text-muted text-center small my-3";
        modalCalendarList.appendChild(noCitas);
    }

    listModal.showModal();
}
// 2. Abrir Formulario (Crear o Editar)
function openAdminForm(cita = null, fechaPreseleccionada = null) {
    if (!adminModal || !adminFieldFecha || !adminFieldNombre || !adminFieldApellido || !adminFieldCorreo
        || !adminFieldCedula || !adminFieldArchivoRuc || !adminFieldArchivoCedula
        || !adminFieldTelefono || !adminFieldMotivo) return;

    const modalTitle = adminModal.querySelector('.modal__heading');
    if (!modalTitle) return;
    const modalHeaderContainer = modalTitle.parentElement;

    if (cita) {
        // MODO EDICIÓN
        modalHeaderContainer.style.backgroundColor = '#ffc107'; // Fondo para edición (Opcional)
        modalTitle.style.color = '#ffffff'; // Letras blancas
        
        adminFieldId.value = cita.id_cita || cita.id || '';
        adminFieldNombre.value = cita.nombre || '';
        adminFieldApellido.value = cita.apellido || '';
        adminFieldCorreo.value = cita.correo || '';
        adminFieldCedula.value = cita.cedula || '';
        adminFieldArchivoRuc.value = '';
        adminFieldArchivoCedula.value = '';
        showCurrentDocument(adminCurrentRuc, cita.archivo_ruc, 'RUC');
        showCurrentDocument(adminCurrentArchivoCedula, cita.archivo_cedula, 'copia de cédula');
        adminFieldFecha.value = cita.fecha ? String(cita.fecha).slice(0, 10) : '';
        adminFieldTelefono.value = cita.telefono || '';
        adminFieldMotivo.value = cita.motivo || '';

        if (cita.fecha) {
            adminFieldFecha.value = String(cita.fecha).slice(0, 10);
        }

        modalTitle.textContent = 'Editar cita';
        if (btnDeleteAppointment) btnDeleteAppointment.style.display = 'block'; // Mostrar botón borrar
    } else {
        // MODO CREACIÓN
        modalHeaderContainer.style.backgroundColor = '#ffc107'; // Cambia el color del fondo (ej. oscuro)
        modalTitle.style.color = '#ffffff';
        
        modalTitle.textContent = 'Nueva Cita Administrativa';
        adminFieldId.value = ''; // ID vacío indica creación
        adminFieldNombre.value = '';
        adminFieldApellido.value = '';
        adminFieldCorreo.value = '';
        adminFieldCedula.value = '';
        adminFieldArchivoRuc.value = '';
        adminFieldArchivoCedula.value = '';
        showCurrentDocument(adminCurrentRuc, null, 'RUC');
        showCurrentDocument(adminCurrentArchivoCedula, null, 'copia de cédula');
        adminFieldTelefono.value = '';
        adminFieldMotivo.value = '';
        adminFieldFecha.value = '';
        

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
        cedula: adminFieldCedula.value.trim(),
        telefono: adminFieldTelefono.value.trim(),
        fecha: adminFieldFecha.value,
        motivo: adminFieldMotivo.value.trim()
    };

    // Validar
    if (!payload.nombre || !payload.apellido || !payload.correo || !payload.telefono || !payload.fecha || !payload.motivo || !adminFieldCorreo.checkValidity()) {
        alert('Completa nombre, apellido, correo válido, fecha, teléfono y motivo.');
        return;
    }
    if (payload.cedula && !/^\d{6,20}$/.test(payload.cedula)) {
        alert('La cédula debe contener entre 6 y 20 dígitos.');
        return;
    }

    try {
        const url = '../php/api_horarios_admin.php';
        const formData = new FormData();
        Object.entries(payload).forEach(([key, value]) => formData.append(key, value ?? ''));
        if (adminFieldArchivoRuc.files[0]) {
            formData.append('archivo_ruc', adminFieldArchivoRuc.files[0]);
        }
        if (adminFieldArchivoCedula.files[0]) {
            formData.append('archivo_cedula', adminFieldArchivoCedula.files[0]);
        }

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            adminModal.close();
            await renderCalendar();
            await refreshUpcomingAppointments();
        } else {
            alert('Error al guardar: ' + (result.error || result.message || 'No se pudo guardar la cita.'));
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
        const response = await fetch(`../php/api_horarios_admin.php?id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            adminModal.close();
            await renderCalendar();
            await refreshUpcomingAppointments();
        } else {
            const result = await response.json().catch(() => ({}));
            alert(result.error || 'No se pudo eliminar la cita.');
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión al eliminar');
    }
}

// --- EVENT LISTENERS ---
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    refreshUpcomingAppointments();

    // Navegación
    previousMonthBtnAdmin?.addEventListener('click', () => setMonth(-1));
    nextMonthBtnAdmin?.addEventListener('click', () => setMonth(1));
    // Botón '+ Nueva' — registrar una sola vez
    btnNewAppointment?.addEventListener('click', () => openAdminForm());

    // Clic en el calendario
    if (calendarDaysAdmin) {
        calendarDaysAdmin.addEventListener('click', (e) => {
            const dayElement = e.target.closest('.calendar__day');
            if (!dayElement) return;

            const dayNumber = dayElement.dataset.day;
            if (!dayNumber) return;

            const dayNumberLabel = dayElement.querySelector('.day-number');
            if (!dayNumberLabel || !dayNumberLabel.textContent.trim()) return;
            if (dayElement.classList.contains('calendar__day--hidden')) return;

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
    if (appointmentsTable) {
        appointmentsTable.addEventListener('click', async (e) => {
            const btnEdit = e.target.closest('.btn-edit');

            if (btnEdit) {
                const id = btnEdit.dataset.id;

                // ¡AQUÍ BORRAMOS EL botonEditar! Pasamos directo a buscar los datos

                try {
                    // Busca los datos de la cita en la base de datos
                    const response = await fetch(`../php/api_horarios_admin.php?id=${encodeURIComponent(id)}`);

                    if (response.ok) {
                        const cita = await response.json();
                        // ¡MAGIA! Esta línea es la que abre tu ventana emergente con los datos
                        openAdminForm(cita);
                    } else {
                        const result = await response.json().catch(() => ({}));
                        alert(result.error || 'No se pudieron cargar los datos de la cita.');
                    }
                } catch (error) {
                    console.error(error);
                }
                return;
            }

            const btnDelete = e.target.closest('.btn-delete');
            if (btnDelete) {
                const id = btnDelete.dataset.id;
                adminFieldId.value = id;
                deleteAppointment();
                return;
            }
        });
    }

    const modalSaveBtn = document.getElementById('btn-generar-appointment');
    modalSaveBtn?.addEventListener('click', () => {
        if (!window.jspdf?.jsPDF) {
            alert('No se pudo cargar el generador de comprobantes.');
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        // Ocuparemos del documento el tamaño y ancho y lo guardaremos en variables
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
    
        const nombre = document.getElementById('appointment-nombre')?.value || 'N/A';
        const apellido = document.getElementById('appointment-apellido')?.value || 'N/A' || '';
        const descripcion = document.getElementById('appointment-motivo')?.value || 'N/A';
        const email = document.getElementById('appointment-correo')?.value || 'N/A';
        const telefono = document.getElementById('appointment-telefono')?.value || 'N/A';
        const fecha = document.getElementById('appointment-fecha')?.value || 'N/A';
        //Diseños para el pdf
        const logo = new Image();
        logo.src = '../img/headerlym.png';
        logo.onerror = function () {
            console.error('No se pudo cargar el encabezado del comprobante de cita.');
            window.alert('No se pudo cargar la imagen del encabezado del comprobante.');
        };
        logo.onload = function () {
            const footer = new Image();
            footer.src = '../img/footerlym.png';
            footer.onerror = function () {
                console.error('No se pudo cargar el pie del comprobante de cita.');
                window.alert('No se pudo cargar la imagen del pie de página del comprobante.');
            };
            footer.onload = function () {
                const headerHeight = pageWidth * logo.height / logo.width;
                const footerHeight = pageWidth * footer.height / footer.width;
                doc.addImage(logo, 'PNG', 0, 0, pageWidth, headerHeight);
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                doc.text("Comprobante de Cita", pageWidth / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.setFont(undefined, 'normal');
                if (nombre !== 'N/A' && apellido !== 'N/A') {
                    doc.text("Nombre: " + nombre, 15, 65);
                    doc.text("Apellido: " + apellido, 15, 75);
                    doc.text("Email: " + email, 15, 85);
                    doc.text("Numero de Telefono: " + telefono, 15, 95);
                    doc.text("Fecha y Hora: " + fecha, 15, 105);

                    const textoMotivo = doc.splitTextToSize("Motivo de la Cita: " + descripcion, pageWidth - 30);
                    doc.text(textoMotivo, 15, 115);

                } else {
                    doc.text("Nombre: " + nombre, 15, 65);
                    doc.text("Correo Electronico: " + email, 15, 75);
                    doc.text("Numero de Telefono: " + telefono, 15, 85);
                    doc.text("Fecha y Hora: " + fecha, 15, 95);
                    const textoMotivo = doc.splitTextToSize("Motivo de la Cita: " + descripcion, pageWidth - 30);
                    doc.text(textoMotivo, 15, 105);
                }
                doc.addImage(footer, 'PNG', 0, pageHeight - footerHeight, pageWidth, footerHeight);
                const nombreAr = nombre !== 'N/A' ? nombre.replace(/\s+/g, '_') : 'Usuario';
                doc.save('comprobante_cita_horario_usuarios_' + nombreAr + '.pdf');
            }
        };
    });
    
});
