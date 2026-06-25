document.addEventListener('DOMContentLoaded', function(){
    const checkSensible = document.getElementById('check_sensible');
    if(checkSensible){
        checkSensible.addEventListener('change', function(){
            const divDocs = document.getElementById('div_documentos');
            const rucInput = document-getElementById('archivo_ruc');
            if(this.checked){
                divDocs.style.display = 'block';
                rucInput.setAttribute('required', 'required');
            }else{
                divDocs.style.display = 'none';
                rucInput.removeAttribute('required');
            }
        });
    }
});