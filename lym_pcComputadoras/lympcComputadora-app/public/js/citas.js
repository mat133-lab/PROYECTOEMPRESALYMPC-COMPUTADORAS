document.addEventListener('DOMContentLoaded', function () {
    const checkSensible = document.getElementById('check_sensible');
    if (checkSensible) {
        checkSensible.addEventListener('change', function () {
            const divDocs = document.getElementById('div_documentos');
            const rucInput = document - getElementById('archivo_ruc');
            if (this.checked) {
                divDocs.style.display = 'block';
                rucInput.setAttribute('required', 'required');
            } else {
                divDocs.style.display = 'none';
                rucInput.removeAttribute('required');
            }
        });
    }
    const impriBtn = document.getElementById('boton');
    impriBtn.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        // Ocuparemos del documento el tamaño y ancho y lo guardaremos en variables
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        const nombre = document.getElementById('Nombre')?.value || 'N/A';
        const apellido = document.getElementById('Apellido')?.value || 'N/A';
        const descripcion =  document.getElementById('reason')?.value || 'N/A';
        const email =  document.getElementById('Correo')?.value || 'N/A';
        const telefono =  document.getElementById('Telefono')?.value || 'N/A';
        const cedula =  document.getElementById('cedula')?.value || 'N/A';
        const fecha =  document.getElementById('Fecha')?.value || 'N/A';
        //Diseños para el pdf
        const logo = new Image();
        logo.src = '../img/headerlym.png';
        logo.onload = function () {
            const footer = new Image();
            footer.src = '../img/footerlym.png';
            footer.onload = function () {
                doc.addImage(logo, 'PNG', 0, 0, pageWidth, 35);
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                doc.text("Comprobante de Cita", pageWidth / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.setFont(undefined, 'normal');
                doc.text("Nombre: " + nombre , 15, 65);
                doc.text("Apellido: " + apellido, 15, 75);
                doc.text("Correo Electronico: " + email, 15, 85);
                doc.text("Numero de Cedula: " + cedula, 15, 95);
                doc.text("Fecha: " + fecha || 'N/A', 15, 105);
                const archivoRuc = document.getElementById('archivo_ruc');
                if (archivoRuc && archivoRuc.files.length > 0) {
                    doc.text("Si contiene un archivo Ruc adjunto.", 15, 115);
                } else {
                    doc.text("No se adjunto un archivo Ruc.", 15, 115);
                }
                const archivoCedula = document.getElementById('archivo_cedula');
                if (archivoCedula && archivoCedula.files.length > 0) {
                    doc.text("Si contiene un archivo de Cedula adjunto.", 15, 125);
                } else {
                    doc.text("No se adjunto un archivo de Cedula.", 15, 125);
                }
                doc.text("Telefono: " + telefono || 'N/A', 15, 135);
                doc.text("Mensaje: " + descripcion || 'N/A', 15, 145);
                doc.addImage(footer, 'PNG', 0, pageHeight - 30, pageWidth, 30);
                const nombreAr = nombre !== 'N/A' ? nombre.replace(/\s+/g, '_') : 'Usuario';
                doc.save('comprobante_cita_horario_usuarios_' + nombreAr + '.pdf');
            };
        };

    })
});
