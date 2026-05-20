document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('graficaAsistente').getContext('2d');
    let chartTopProductos = null;

    async function cargarDatosGrafico() {
        try {
            const response = await fetch('../php/api_grafico_asistente.php');
            const result = await response.json();

            if (result.success) {
                if (chartTopProductos) {
                    chartTopProductos.data.labels = result.nombres;
                    chartTopProductos.data.datasets[0].data = result.cantidades;
                    chartTopProductos.data.datasets[1].data = result.cantidades;
                    chartTopProductos.update(); 
                } else {
                    chartTopProductos = new Chart(ctx, {
                        type: 'bar', 
                        data: {
                            labels: result.nombres,
                            datasets: [
                                {
                                    type: 'line', 
                                    label: 'Tendencia',
                                    data: result.cantidades,
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
                                    data: result.cantidades,
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
                            animation: {
                                duration: 1000, 
                                easing: 'easeOutQuart'
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { borderDash: [5, 5] } 
                                },
                                x: {
                                    grid: { display: false } 
                                }
                            },
                            plugins: {
                                legend: { display: true }, 
                                tooltip: {
                                    backgroundColor: '#333',
                                    titleFont: { size: 14 },
                                    bodyFont: { size: 14, weight: 'bold' },
                                    padding: 12,
                                    cornerRadius: 8
                                }
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar la gráfica:', error);
        }
    }

    cargarDatosGrafico();
    setInterval(cargarDatosGrafico, 15000); 
});