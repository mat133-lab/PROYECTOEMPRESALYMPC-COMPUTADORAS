document.addEventListener('DOMContentLoaded', function () {
    const checkSensible = document.getElementById('check_sensible');
    if (checkSensible) {
        checkSensible.addEventListener('change', function () {
            const divDocs = document.getElementById('div_documentos');
            const rucInput = document.getElementById('archivo_ruc');
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
function imprimirComprobante() {

    const { jsPDF } = window.jspdf;
    //Aqui estamos con el doc que tenga el valor y caracteristicas del jsPDF, en otras palabras es el objeto relacionado con la libreria jsPDF
    const doc = new jsPDF();
    doc.text("Comprobante de Contacto", 10, 10);
    doc.text("Nombre: " + (document.getElementById('Nombre').value || 'N/A'), 10, 20);
    doc.text("Correo Electronico: " + (document.getElementById('Correo').value || 'N/A'), 10, 30);
    doc.text("Numero de Cedula: " + (document.getElementById('cedula').value || 'N/A'), 10, 40);
    doc.text("Compañia u Organizacion: " + (document.getElementById('Compania').value || 'N/A'), 10, 50);
    const archivoRuc = document.getElementById('archivo_ruc');
    if (archivoRuc && archivoRuc.files.length > 0) {
        doc.text("Si contiene un archivo Ruc adjunto.", 10, 60);
    } else {
        doc.text("No se adjunto un archivo Ruc.", 10, 60);
    }
    const archivoCedula = document.getElementById('archivo_cedula');
    if (archivoCedula && archivoCedula.files.length > 0) {
        doc.text("Si contiene un archivo de Cedula adjunto.", 10, 70);
    } else {
        doc.text("No se adjunto un archivo de Cedula.", 10, 70);
    }
    doc.text("Mensaje: " + document.getElementById('Mensaje').value || 'N/A', 10, 80);
    doc.save('comprobante_contacto_' + document.getElementById('Nombre').value + '.pdf');
}
const imprimirbtn = document.getElementById('boton')
if(imprimirbtn){
    imprimirbtn.addEventListener('click', imprimirComprobante);
}
    
});
