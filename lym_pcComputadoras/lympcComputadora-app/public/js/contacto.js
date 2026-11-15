document.addEventListener('DOMContentLoaded', function () {
    const checkSensible = document.getElementById('check_sensible');
    if (checkSensible) {
        checkSensible.addEventListener('change', function () {
            const divDocs = document.getElementById('div_documentos');
            const rucInput = document.getElementById('archivo_ruc');
            if (!divDocs || !rucInput) return;
            if (this.checked) {
                divDocs.style.display = 'block';
                rucInput.setAttribute('required', 'required');
            } else {
                divDocs.style.display = 'none';
                rucInput.removeAttribute('required');
            }
        });
    }
    // Funcion para imprimir el comprobante de contacto
    const receiptButton = document.getElementById('boton');
    if (!receiptButton) return;

    receiptButton.addEventListener('click', function () {

        if (!window.jspdf?.jsPDF) {
            window.alert('No se pudo cargar el generador de comprobantes. Recarga la página e inténtalo de nuevo.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        // Ocuparemos del documento el tamaño y ancho y lo guardaremos en variables
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        const nombre = document.getElementById('Nombre')?.value || 'N/A';
        const email = document.getElementById('Correo')?.value || 'N/A';
        const cedula = document.getElementById('cedula')?.value || 'N/A';
        const compania = document.getElementById('Compania')?.value || 'N/A';
        const mensaje = document.getElementById('Mensaje')?.value || 'N/A';
        //Diseños para el pdf
        const logo = new Image();
        logo.src = '../img/headerlym.png';
        logo.onerror = function () {
            console.error('No se pudo cargar el encabezado del comprobante de contacto.');
            window.alert('No se pudo cargar la imagen del encabezado del comprobante.');
        };
        logo.onload = function () {
            const footer = new Image();
            footer.src = '../img/footerlym.png';
            footer.onerror = function () {
                console.error('No se pudo cargar el pie del comprobante de contacto.');
                window.alert('No se pudo cargar la imagen del pie de página del comprobante.');
            };
            footer.onload = function () {
                const headerHeight = pageWidth * logo.height / logo.width;
                const footerHeight = pageWidth * footer.height / footer.width;
                doc.addImage(logo, 'PNG', 0, 0, pageWidth, headerHeight);
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                doc.text("Comprobante de Contacto", pageWidth / 2, 50, { align: 'center' });
                doc.setFontSize(12);
                doc.setFont(undefined, 'normal');
                doc.text("Nombre: " + nombre, 15, 65);
                doc.text("Correo Electronico: " + email, 15, 75);
                doc.text("Numero de Cedula: " + cedula, 15, 85);
                doc.text("Compañia u Organizacion: " + compania || 'N/A', 15, 95);
                const archivoRuc = document.getElementById('archivo_ruc');
                if (archivoRuc && archivoRuc.files.length > 0) {
                    doc.text("Si contiene un archivo Ruc adjunto.", 15, 105);
                } else {
                    doc.text("No se adjunto un archivo Ruc.", 15, 105);
                }
                const archivoCedula = document.getElementById('archivo_cedula');
                if (archivoCedula && archivoCedula.files.length > 0) {
                    doc.text("Si contiene un archivo de Cedula adjunto.", 15, 115);
                } else {
                    doc.text("No se adjunto un archivo de Cedula.", 15, 115);
                }
                doc.text("Mensaje: " + mensaje || 'N/A', 15, 125);
                doc.addImage(footer, 'PNG', 0, pageHeight - footerHeight, pageWidth, footerHeight);
                const nombreAr = nombre !== 'N/A' ? nombre.replace(/\s+/g, '_') : 'Usuario';
                doc.save('comprobante_contacto_usuarios_' + nombreAr + '.pdf');
            };
        };
    });
});
