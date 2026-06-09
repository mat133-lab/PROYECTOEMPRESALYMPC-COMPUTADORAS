document.addEventListener('DOMContentLoaded', function () {
    
    const ctx = document.getElementById('graficaAsistente').getContext('2d');
    let chartTopProductos = null;

    async function cargarDatosGraficoComp() {
        try {
            const response = await fetch('../php/api_grafico_asistente.php');
            const result = await response.json();

            if (result.success) {

                const datos = result.Grafica_venta_usu;

                if (chartTopProductos) {
                    chartTopProductos.data.labels = datos.nombres;
                    chartTopProductos.data.datasets[0].data = datos.cantidades;
                    chartTopProductos.data.datasets[1].data = datos.cantidades;
                    chartTopProductos.update(); 
                } else {
                    chartTopProductos = new Chart(ctx, {
                        type: 'bar', 
                        data: {
                            labels: datos.nombres,
                            datasets: [
                                {
                                    type: 'line', 
                                    label: 'Tendencia',
                                    data: datos.cantidades,
                                    borderColor: '#007bff', 
                                    backgroundColor: 'rgba(0, 123, 255, 0.1)', 
                                    borderWidth: 3,
                                    tension: 0.4, 
                                    fill: true, 
                                    order: 1 
                                },
                                {
                                    type: 'bar',
                                    label: 'Unidades Vendidas',
                                    data: datos.cantidades,
                                    backgroundColor: 'rgba(255, 145, 0, 0.7)', 
                                    borderColor: 'rgba(255, 123, 0, 1)',
                                    borderWidth: 2,
                                    borderRadius: 6, 
                                    hoverBackgroundColor: 'rgba(255, 145, 0, 1)',
                                    order: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            animation: { duration: 1000, easing: 'easeOutQuart' },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: true } }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar la gráfica de ventas:', error);
        }
    }
    

    const ctx1 = document.getElementById('graficaAsistente1').getContext('2d');
    let chartTopProductos1 = null;
    
    async function cargarDatosGraficoPro() {
        try {
            const response1 = await fetch('../php/api_grafico_asistente.php');
            const result1 = await response1.json();

            if (result1.success) {
                const datos1 = result1.Grafica_venta_pro;

                if (chartTopProductos1) {
                    chartTopProductos1.data.labels = datos1.nombres;
                    chartTopProductos1.data.datasets[0].data = datos1.cantidades;
                    chartTopProductos1.data.datasets[1].data = datos1.cantidades;
                    chartTopProductos1.update(); 
                } else {
                    chartTopProductos1 = new Chart(ctx1, {
                        type: 'bar', 
                        data: {
                            labels: datos1.nombres,
                            datasets: [
                                {
                                    type: 'line', 
                                    label: 'Tendencia',
                                    data: datos1.cantidades,
                                    borderColor: '#28a745', 
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)', 
                                    borderWidth: 3,
                                    tension: 0.4, 
                                    fill: true, 
                                    order: 1 
                                },
                                {
                                    type: 'bar',
                                    label: 'Unidades en Bodega',
                                    data: datos1.cantidades,
                                    backgroundColor: 'rgba(40, 167, 69, 0.7)', 
                                    borderColor: '#28a745',
                                    borderWidth: 2,
                                    borderRadius: 6, 
                                    hoverBackgroundColor: 'rgba(40, 167, 69, 1)',
                                    order: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            animation: { duration: 1000, easing: 'easeOutQuart' },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: true } }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar la gráfica de inventario:', error);
        }
    }

    // =======================================================
    // GRÁFICA 3: DATOS ENVIADOS A CONTACTO
    // =======================================================
    const ctx2 = document.getElementById('graficaAsistente2').getContext('2d');
    let chartContacto = null;

    async function cargarDatosGraficoContacto() {
        try {
            const response2 = await fetch('../php/api_grafico_asistente.php');
            const result2 = await response2.json();

            if (result2.success) {
                // Buscamos dentro de la nueva "caja" que creamos en PHP
                const datosContacto = result2.Grafica_contacto;

                if (chartContacto) {
                    chartContacto.data.labels = datosContacto.nombres;
                    chartContacto.data.datasets[0].data = datosContacto.cantidades;
                    chartContacto.data.datasets[1].data = datosContacto.cantidades;
                    chartContacto.update(); 
                } else {
                    chartContacto = new Chart(ctx2, {
                        type: 'bar', 
                        data: {
                            labels: datosContacto.nombres,
                            datasets: [
                                {
                                    type: 'line', 
                                    label: 'Tendencia de Mensajes',
                                    data: datosContacto.cantidades,
                                    borderColor: '#dc3545', // Color Rojo
                                    backgroundColor: 'rgba(220, 53, 69, 0.1)', 
                                    borderWidth: 3,
                                    tension: 0.4, 
                                    fill: true, 
                                    order: 1 
                                },
                                {
                                    type: 'bar',
                                    label: 'Mensajes Enviados',
                                    data: datosContacto.cantidades,
                                    backgroundColor: 'rgba(220, 53, 69, 0.7)', // Color Rojo
                                    borderColor: '#dc3545',
                                    borderWidth: 2,
                                    borderRadius: 6, 
                                    hoverBackgroundColor: 'rgba(220, 53, 69, 1)',
                                    order: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            animation: { duration: 1000, easing: 'easeOutQuart' },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: true } }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar la gráfica de contacto:', error);
        }
    }

    // =======================================================
    // GRÁFICA 4: DATOS ENVIADOS A CITAS
    // =======================================================
    const ctx3 = document.getElementById('graficaAsistente3').getContext('2d');
    let chartCitas = null;

    async function cargarDatosGraficoCitas() {
        try {
            const response3 = await fetch('../php/api_grafico_asistente.php');
            const result3 = await response3.json();

            if (result3.success) {
                // Buscamos dentro de la nueva "caja" que creamos en PHP
                const datosCitas = result3.Grafica_citas;

                if (chartCitas) {
                    chartCitas.data.labels = datosCitas.nombres;
                    chartCitas.data.datasets[0].data = datosCitas.cantidades;
                    chartCitas.data.datasets[1].data = datosCitas.cantidades;
                    chartCitas.update(); 
                } else {
                    chartCitas = new Chart(ctx3, {
                        type: 'bar', 
                        data: {
                            labels: datosCitas.nombres,
                            datasets: [
                                {
                                    type: 'line', 
                                    label: 'Tendencia de Mensajes',
                                    data: datosCitas.cantidades,
                                    borderColor: '#00adfd', // Color Rojo
                                    backgroundColor: 'rgba(0, 162, 255, 0.37)', 
                                    borderWidth: 3,
                                    tension: 0.4, 
                                    fill: true, 
                                    order: 1 
                                },
                                {
                                    type: 'bar',
                                    label: 'Mensajes Enviados',
                                    data: datosCitas.cantidades,
                                    backgroundColor: 'rgba(71, 160, 224, 0.84)', // Color Rojo
                                    borderColor: '#00aeff',
                                    borderWidth: 2,
                                    borderRadius: 6, 
                                    hoverBackgroundColor: 'rgb(1, 161, 254)',
                                    order: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            animation: { duration: 1000, easing: 'easeOutQuart' },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: true } }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar la gráfica de citas:', error);
        }
    }


    cargarDatosGraficoContacto();
    setInterval(cargarDatosGraficoContacto, 15000);

    cargarDatosGraficoCitas();
    setInterval(cargarDatosGraficoCitas, 15000);

    cargarDatosGraficoComp();
    setInterval(cargarDatosGraficoComp, 15000);

    cargarDatosGraficoPro();
    setInterval(cargarDatosGraficoPro, 15000);
});