document.addEventListener('DOMContentLoaded', function () {
    const METRICAS = {
        compras: {
            tipo: 'compras',
            api: 'Grafica_venta_usu',
            labelGrafica: 'Compras recientes por usuarios',
            etiquetaLinea: 'Tendencia de ventas',
            etiquetaBarra: 'Unidades vendidas',
            colorLinea: '#007bff',
            colorFondo: 'rgba(0, 123, 255, 0.1)',
            colorBarra: 'rgba(255, 145, 0, 0.7)',
            bordeBarra: 'rgba(255, 123, 0, 1)',
            hoverBarra: 'rgba(255, 145, 0, 1)',
            unidad: 'unidades vendidas',
            top: 'producto líder'
        },
        inventario: {
            tipo: 'inventario',
            api: 'Grafica_venta_pro',
            labelGrafica: 'Productos llegados al local',
            etiquetaLinea: 'Tendencia de inventario',
            etiquetaBarra: 'Unidades en bodega',
            colorLinea: '#28a745',
            colorFondo: 'rgba(40, 167, 69, 0.1)',
            colorBarra: 'rgba(40, 167, 69, 0.7)',
            bordeBarra: '#28a745',
            hoverBarra: 'rgba(40, 167, 69, 1)',
            unidad: 'unidades registradas',
            top: 'producto con mayor ingreso'
        },
        contacto: {
            tipo: 'contacto',
            api: 'Grafica_contacto',
            labelGrafica: 'Datos enviados por contacto',
            etiquetaLinea: 'Tendencia de mensajes',
            etiquetaBarra: 'Mensajes enviados',
            colorLinea: '#dc3545',
            colorFondo: 'rgba(220, 53, 69, 0.1)',
            colorBarra: 'rgba(220, 53, 69, 0.7)',
            bordeBarra: '#dc3545',
            hoverBarra: 'rgba(220, 53, 69, 1)',
            unidad: 'mensajes recibidos',
            top: 'usuario con mayor actividad'
        },
        citas: {
            tipo: 'citas',
            api: 'Grafica_citas',
            labelGrafica: 'Datos enviados por citas',
            etiquetaLinea: 'Tendencia de citas',
            etiquetaBarra: 'Citas registradas',
            colorLinea: '#00adfd',
            colorFondo: 'rgba(0, 162, 255, 0.37)',
            colorBarra: 'rgba(71, 160, 224, 0.84)',
            bordeBarra: '#00aeff',
            hoverBarra: 'rgb(1, 161, 254)',
            unidad: 'citas registradas',
            top: 'usuario con mayor demanda'
        }
    };

    let chartTopProductos = null;
    let chartTopProductos1 = null;
    let chartContacto = null;
    let chartCitas = null;
    let datosGraficosCache = null;
    let cacheTimer = null;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function toNumber(value) {
        const number = Number(value);
        return Number.isFinite(number) ? number : 0;
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('es-ES').format(Math.round(toNumber(value)));
    }

    function formatPercent(value) {
        return `${toNumber(value).toFixed(1)}%`;
    }

    function calcularEstadisticas(labels, valores) {
        const etiquetas = labels || [];
        const cantidades = valores || [];
        const longitud = Math.max(etiquetas.length, cantidades.length);
        const pares = Array.from({ length: longitud }, function (_, index) {
            return {
                label: etiquetas[index] ?? `Categoría ${index + 1}`,
                valor: toNumber(cantidades[index])
            };
        }).filter(function (item) {
            return item.valor > 0;
        });

        const total = pares.reduce(function (suma, item) {
            return suma + item.valor;
        }, 0);

        if (total <= 0) {
            return {
                count: longitud,
                total: 0,
                average: 0,
                max: null,
                min: null,
                range: 0,
                topShare: 0,
                pares: []
            };
        }

        const ordenados = pares.slice().sort(function (a, b) {
            return b.valor - a.valor;
        });
        const max = ordenados[0];
        const min = ordenados[ordenados.length - 1];

        return {
            count: pares.length,
            total: total,
            average: total / Math.max(pares.length, 1),
            max: max,
            min: min,
            range: max.valor - min.valor,
            topShare: (max.valor / total) * 100,
            pares: ordenados
        };
    }

    function nivelConcentracion(porcentaje) {
        if (porcentaje >= 60) {
            return 'alta';
        }

        if (porcentaje >= 35) {
            return 'media';
        }

        return 'baja';
    }

    function recomendacionPorTipo(metrica, estadisticas) {
        const top = estadisticas.max;
        const segundo = estadisticas.pares[1];
        const tercero = estadisticas.pares[2];

        if (metrica.tipo === 'compras') {
            return `Priorizar disponibilidad, exhibición y seguimiento comercial de ${escapeHtml(top.label)}, porque concentra la mayor parte de las ventas. Si ${segundo ? escapeHtml(segundo.label) : 'el segundo lugar'} y ${tercero ? escapeHtml(tercero.label) : 'el tercer lugar'} están cerca del promedio, conviene activar promociones cruzadas para equilibrar la demanda y aumentar el ticket promedio.`;
        }

        if (metrica.tipo === 'inventario') {
            return `Mantener control estricto de ${escapeHtml(top.label)} para evitar quiebres de stock, pero validar su rotación real antes de seguir incrementando bodega. Revisar productos con baja participación para detectar exceso de inventario, productos lentos o necesidad de reasignación comercial.`;
        }

        if (metrica.tipo === 'contacto') {
            return `Reforzar tiempos de respuesta para ${escapeHtml(top.label)} y preparar plantillas de atención para los temas más repetidos. La concentración de mensajes permite ordenar prioridades, asignar responsables y medir si la comunicación está generando conversión o solo consultas sin cierre.`;
        }

        if (metrica.tipo === 'citas') {
            return `Ajustar agenda y capacidad operativa alrededor de ${escapeHtml(top.label)}, asegurando confirmaciones, recordatorios y seguimiento posterior. Si la concentración es alta, conviene reservar cupos estratégicos y prevenir saturación en los horarios de mayor demanda.`;
        }

        return `Monitorear ${escapeHtml(top.label)} como prioridad principal y comparar su evolución contra el promedio general.`;
    }

    function generarAnalisis(metrica, labels, valores) {
        const estadisticas = calcularEstadisticas(labels, valores);

        if (estadisticas.total <= 0) {
            return `
                <p class="mb-0"><strong>Resumen analítico:</strong> No hay datos suficientes para generar un análisis confiable en <strong>${escapeHtml(metrica.labelGrafica)}</strong>. Se recomienda verificar la carga de registros, periodos consultados y filtros activos antes de tomar decisiones operativas.</p>
            `;
        }

        const top = estadisticas.max;
        const concentracion = nivelConcentracion(estadisticas.topShare);
        const diferenciaPromedio = top.valor - estadisticas.average;
        const resumen = `Total acumulado: <strong>${formatNumber(estadisticas.total)} ${metrica.unidad}</strong>; promedio por categoría: <strong>${formatNumber(estadisticas.average)}</strong>. El ${metrica.top} es <strong>${escapeHtml(top.label)}</strong> con ${formatNumber(top.valor)} registros (${formatPercent(estadisticas.topShare)}), con distribución ${concentracion}. La diferencia contra el promedio es de <strong>${formatNumber(diferenciaPromedio)}</strong>, lo que confirma su peso operativo en la lectura actual.`;
        const recomendacion = recomendacionPorTipo(metrica, estadisticas);

        return `
            <p class="mb-1"><strong>Resumen analítico:</strong> ${resumen}</p>
            <p class="mb-0"><strong>Acción recomendada:</strong> ${recomendacion}</p>
        `;
    }

    function mostrarAnalisis(index, metrica, labels, valores) {
        const contenedorLocal = document.getElementById(`iaAnalisisGrafica${index}`);

        if (contenedorLocal) {
            contenedorLocal.classList.remove('ia-analisis-vacio');
            contenedorLocal.innerHTML = generarAnalisis(metrica, labels, valores);
        }
    }

    function mostrarAnalisisVacio(index, metrica, mensaje) {
        const contenedorLocal = document.getElementById(`iaAnalisisGrafica${index}`);

        if (contenedorLocal) {
            contenedorLocal.classList.add('ia-analisis-vacio');
            contenedorLocal.innerHTML = `
                <p class="mb-0"><strong>Resumen analítico:</strong> ${escapeHtml(mensaje)} Verifique la conexión con el endpoint, la disponibilidad de registros y los filtros activos antes de tomar decisiones operativas.</p>
            `;
        }
    }

    function crearConfiguracion(datos, metrica) {
        return {
            type: 'bar',
            data: {
                labels: datos.nombres || [],
                datasets: [
                    {
                        type: 'line',
                        label: metrica.etiquetaLinea,
                        data: datos.cantidades || [],
                        borderColor: metrica.colorLinea,
                        backgroundColor: metrica.colorFondo,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        order: 1
                    },
                    {
                        type: 'bar',
                        label: metrica.etiquetaBarra,
                        data: datos.cantidades || [],
                        backgroundColor: metrica.colorBarra,
                        borderColor: metrica.bordeBarra,
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: metrica.hoverBarra,
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
        };
    }

    function actualizarGrafico(chart, ctx, datos, metrica, factory) {
        if (!ctx) {
            return null;
        }

        if (chart) {
            chart.data.labels = datos.nombres || [];
            chart.data.datasets.forEach(function (dataset) {
                dataset.data = datos.cantidades || [];
            });
            chart.update();
            return chart;
        }

        return new Chart(ctx, factory(datos, metrica));
    }

    async function obtenerDatosGraficos() {
        if (datosGraficosCache) {
            return datosGraficosCache;
        }

        const response = await fetch('../php/api_grafico_asistente.php');
        const result = await response.json();
        datosGraficosCache = result;

        if (cacheTimer) {
            clearTimeout(cacheTimer);
        }

        cacheTimer = setTimeout(function () {
            datosGraficosCache = null;
        }, 14000);

        return result;
    }

    async function cargarDatosGraficoComp() {
        try {
            const result = await obtenerDatosGraficos();

            if (!result || !result.success) {
                mostrarAnalisisVacio(0, METRICAS.compras, 'No se pudo cargar la información de compras recientes.');
                return;
            }

            const datos = result[METRICAS.compras.api] || { nombres: [], cantidades: [] };
            const canvas = document.getElementById('graficaAsistente');
            const ctx = canvas ? canvas.getContext('2d') : null;
            chartTopProductos = actualizarGrafico(chartTopProductos, ctx, datos, METRICAS.compras, crearConfiguracion);
            mostrarAnalisis(0, METRICAS.compras, datos.nombres, datos.cantidades);
        } catch (error) {
            console.error('Error al cargar la gráfica de ventas:', error);
            mostrarAnalisisVacio(0, METRICAS.compras, 'Error al procesar la gráfica de compras recientes.');
        }
    }

    async function cargarDatosGraficoPro() {
        try {
            const result = await obtenerDatosGraficos();

            if (!result || !result.success) {
                mostrarAnalisisVacio(1, METRICAS.inventario, 'No se pudo cargar la información de productos llegados al local.');
                return;
            }

            const datos = result[METRICAS.inventario.api] || { nombres: [], cantidades: [] };
            const canvas = document.getElementById('graficaAsistente1');
            const ctx = canvas ? canvas.getContext('2d') : null;
            chartTopProductos1 = actualizarGrafico(chartTopProductos1, ctx, datos, METRICAS.inventario, crearConfiguracion);
            mostrarAnalisis(1, METRICAS.inventario, datos.nombres, datos.cantidades);
        } catch (error) {
            console.error('Error al cargar la gráfica de inventario:', error);
            mostrarAnalisisVacio(1, METRICAS.inventario, 'Error al procesar la gráfica de productos llegados al local.');
        }
    }

    async function cargarDatosGraficoContacto() {
        try {
            const result = await obtenerDatosGraficos();

            if (!result || !result.success) {
                mostrarAnalisisVacio(2, METRICAS.contacto, 'No se pudo cargar la información de mensajes de contacto.');
                return;
            }

            const datos = result[METRICAS.contacto.api] || { nombres: [], cantidades: [] };
            const canvas = document.getElementById('graficaAsistente2');
            const ctx = canvas ? canvas.getContext('2d') : null;
            chartContacto = actualizarGrafico(chartContacto, ctx, datos, METRICAS.contacto, crearConfiguracion);
            mostrarAnalisis(2, METRICAS.contacto, datos.nombres, datos.cantidades);
        } catch (error) {
            console.error('Error al cargar la gráfica de contacto:', error);
            mostrarAnalisisVacio(2, METRICAS.contacto, 'Error al procesar la gráfica de mensajes de contacto.');
        }
    }

    async function cargarDatosGraficoCitas() {
        try {
            const result = await obtenerDatosGraficos();

            if (!result || !result.success) {
                mostrarAnalisisVacio(3, METRICAS.citas, 'No se pudo cargar la información de citas registradas.');
                return;
            }

            const datos = result[METRICAS.citas.api] || { nombres: [], cantidades: [] };
            const canvas = document.getElementById('graficaAsistente3');
            const ctx = canvas ? canvas.getContext('2d') : null;
            chartCitas = actualizarGrafico(chartCitas, ctx, datos, METRICAS.citas, crearConfiguracion);
            mostrarAnalisis(3, METRICAS.citas, datos.nombres, datos.cantidades);
        } catch (error) {
            console.error('Error al cargar la gráfica de citas:', error);
            mostrarAnalisisVacio(3, METRICAS.citas, 'Error al procesar la gráfica de citas registradas.');
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
