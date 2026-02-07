import {
    calendarHeadingAdmin,
    calendarDaysAdmin,
    previousMonthBtnAdmin,
    nextMonthBtnAdmin,
    adminModal,
    adminFieldId,
    adminFieldNombre,
    adminFieldApellido,
    adminFieldCorreo,
    adminFieldFecha,
    adminFieldTelefono,
    adminFieldMotivo,
    btnNewAppointment,
    btnSaveAppointment,
    btnDeleteAppointment,
    btnModalClose,
    btnModalCancel,
    adminAppointmentsTable
} from './selectores-horario-admin.js';

import { formatTitle, formatDateString, formatDateRange } from './funciones-horario.js';

const currentDate = new Date();

async function renderCalendar() {
    const month = currentDate.getMonth();
    const year = currentDate.getFullYear();

    const firstMonthDate = new Date(year, month, 1);
    const lastMonthDate = new Date(year, month + 1, 0);

    const calendarTitle = currentDate.toLocaleDateString('es-CO', { month: 'long', year: 'numeric' });
    if (calendarHeadingAdmin) calendarHeadingAdmin.textContent = formatTitle(calendarTitle);

    // hide overflow days
    const lastMonthDay = lastMonthDate.getDate();
    document.querySelectorAll('.calendar__day').forEach(el => {
        const d = parseInt(el.dataset.day, 10);
        el.classList.toggle('calendar__day--hidden', d > lastMonthDay);
        // clean previous appointments
        const container = el.querySelector('.calendar__appointments');
        if (container) container.remove();
    });

    // fetch appointments for month
    const [desde, hasta] = formatDateRange([firstMonthDate, lastMonthDate]);
    try {
        const res = await fetch(`/lymPCComputadoras/php/api_horarios_admin.php?desde=${desde}&hasta=${hasta}`);
        const appointments = await res.json();
        appointments.forEach(a => {
            const day = new Date(a.fecha).getDate();
            const dayEl = document.querySelector(`.calendar__day[data-day="${day}"]`);
            if (!dayEl) return;
            let ul = dayEl.querySelector('.calendar__appointments');
            if (!ul) {
                ul = document.createElement('ul');
                ul.className = 'calendar__appointments';
                dayEl.appendChild(ul);
            }
            const li = document.createElement('li');
            li.className = 'calendar__appointment';
            li.dataset.id = a.id;
            li.textContent = `${a.nombre} ${a.apellido}`;
            ul.appendChild(li);
        });
    } catch (err) {
        console.error(err);
    }
}

function setMonth(step) {
    currentDate.setMonth(currentDate.getMonth() + step);
    renderCalendar();
}

function openModal() {
    if (!adminModal) return;
    adminModal.showModal();
}
function closeModal() {
    if (!adminModal) return;
    adminModal.close();
}

function populateForm(data = null) {
    if (!data) {
        adminFieldId.value = '';
        adminFieldNombre.value = '';
        adminFieldApellido.value = '';
        adminFieldCorreo.value = '';
        adminFieldFecha.value = '';
        adminFieldTelefono.value = '';
        adminFieldMotivo.value = '';
        adminModal.querySelector('.modal__heading').textContent = 'Nueva Cita';
        btnDeleteAppointment.style.display = 'none';
        return;
    }
    adminFieldId.value = data.id;
    adminFieldNombre.value = data.nombre || '';
    adminFieldApellido.value = data.apellido || '';
    adminFieldCorreo.value = data.correo || '';
    // format to datetime-local if possible
    if (data.fecha) {
        const dt = new Date(data.fecha);
        const iso = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0,16);
        adminFieldFecha.value = iso;
    } else adminFieldFecha.value = '';
    adminFieldTelefono.value = data.telefono || '';
    adminFieldMotivo.value = data.motivo || '';
    adminModal.querySelector('.modal__heading').textContent = 'Editar Cita';
    btnDeleteAppointment.style.display = '';
}

async function onSave(e) {
    e.preventDefault();
    const payload = {
        id: adminFieldId.value || undefined,
        nombre: adminFieldNombre.value.trim(),
        apellido: adminFieldApellido.value.trim(),
        correo: adminFieldCorreo.value.trim(),
        fecha: adminFieldFecha.value ? adminFieldFecha.value : '',
        telefono: adminFieldTelefono.value.trim(),
        motivo: adminFieldMotivo.value.trim()
    };

    try {
        if (payload.id) {
            // PUT
            await fetch('/lymPCComputadoras/php/api_horarios_admin.php', {
                method: 'PUT',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
        } else {
            // POST
            await fetch('/lymPCComputadoras/php/api_horarios_admin.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
        }
        closeModal();
        setTimeout(() => renderCalendar(), 200);
        // refresh table row list
        location.reload();
    } catch (err) {
        console.error(err);
        alert('Error guardando la cita');
    }
}

async function onDelete() {
    const id = adminFieldId.value;
    if (!id) return;
    if (!confirm('¿Eliminar esta cita?')) return;
    try {
        await fetch(`/lymPCComputadoras/php/api_horarios_admin.php?id=${id}`, { method: 'DELETE' });
        closeModal();
        setTimeout(() => renderCalendar(), 200);
        location.reload();
    } catch (err) {
        console.error(err);
        alert('Error eliminando');
    }
}

// Table click handlers
document.addEventListener('click', (e) => {
    // open edit when edit button clicked
    if (e.target.matches('.btn-edit')) {
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        fetch(`/lymPCComputadoras/php/api_horarios_admin.php?ids=${id}`).then(r => r.json()).then(data => {
            if (data && data[0]) {
                populateForm(data[0]);
                openModal();
            }
        }).catch(console.error);
    }
    if (e.target.matches('.btn-delete')) {
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        if (!confirm('¿Eliminar cita #' + id + '?')) return;
        fetch(`/lymPCComputadoras/php/api_horarios_admin.php?id=${id}`, { method: 'DELETE' }).then(() => location.reload()).catch(console.error);
    }
});

// Appointments in calendar click -> edit
document.querySelectorAll('.calendar__days').forEach(container => {
    container.addEventListener('click', (e) => {
        const ap = e.target.closest('.calendar__appointment');
        if (!ap) return;
        const id = ap.dataset.id;
        fetch(`/lymPCComputadoras/php/api_horarios_admin.php?ids=${id}`).then(r => r.json()).then(data => {
            if (data && data[0]) {
                populateForm(data[0]);
                openModal();
            }
        }).catch(console.error);
    });
});

// New appointment
btnNewAppointment?.addEventListener('click', () => { populateForm(null); openModal(); });
btnSaveAppointment?.addEventListener('click', onSave);
btnDeleteAppointment?.addEventListener('click', onDelete);
btnModalClose?.addEventListener('click', closeModal);
btnModalCancel?.addEventListener('click', closeModal);

previousMonthBtnAdmin?.addEventListener('click', () => setMonth(-1));
nextMonthBtnAdmin?.addEventListener('click', () => setMonth(1));

document.addEventListener('DOMContentLoaded', renderCalendar);
