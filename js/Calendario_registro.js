const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
               'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

const grid = document.getElementById('calendario-grid');
const titulo = document.getElementById('mes-titulo');
const btnAnterior = document.getElementById('mes-anterior');
const btnSiguiente = document.getElementById('mes-siguiente');
const campoFecha = document.getElementById('fecha');
const textoFecha = document.getElementById('fecha-elegida');

const hoy = new Date();
hoy.setHours(0, 0, 0, 0);

// Iniciamos la vista por defecto 20 años atrás para facilitar la selección de la fecha de nacimiento
let anio = hoy.getFullYear() - 20; 
let mes = hoy.getMonth();

function pad(n) {
    return String(n).padStart(2, '0');
}

function dibujarCalendario() {
    titulo.textContent = MESES[mes] + ' ' + anio;

    // Bloquea el botón "siguiente" si estamos en el mes y año actual (no se puede nacer en el futuro)
    if (btnSiguiente) {
        btnSiguiente.disabled = (anio === hoy.getFullYear() && mes === hoy.getMonth());
    }

    grid.querySelectorAll('.calendar-day').forEach(d => d.remove());

    const primerDia = new Date(anio, mes, 1).getDay();
    const diasEnMes = new Date(anio, mes + 1, 0).getDate();

    for (let i = 0; i < primerDia; i++) {
        const vacio = document.createElement('div');
        vacio.className = 'calendar-day empty';
        grid.appendChild(vacio);
    }

    for (let dia = 1; dia <= diasEnMes; dia++) {
        const fecha = anio + '-' + pad(mes + 1) + '-' + pad(dia);
        const celda = document.createElement('button');
        celda.type = 'button';
        celda.className = 'calendar-day';
        celda.textContent = dia;

        const fechaCelda = new Date(anio, mes, dia);
        
        // REGLA INVERTIDA: Se deshabilitan las fechas FUTURAS (mayores al día de hoy)
        if (fechaCelda > hoy) {
            celda.disabled = true;
        }

        if (fechaCelda.getTime() === hoy.getTime()) {
            celda.classList.add('today');
        }

        if (fecha === campoFecha.value) {
            celda.classList.add('selected');
            celda.setAttribute('aria-pressed', 'true');
        }

        celda.addEventListener('click', () => elegirDia(fecha, fechaCelda));
        grid.appendChild(celda);
    }
}

function elegirDia(fecha, objetoFecha) {
    campoFecha.value = fecha;
    if (textoFecha) {
        textoFecha.textContent = 'Fecha elegida: ' + DIAS[objetoFecha.getDay()] + ' ' +
            objetoFecha.getDate() + ' de ' + MESES[objetoFecha.getMonth()].toLowerCase() + ' de ' + objetoFecha.getFullYear();
    }
    dibujarCalendario();
}

function cambiarMes(delta) {
    mes += delta;
    if (mes < 0) { mes = 11; anio--; }
    if (mes > 11) { mes = 0; anio++; }
    dibujarCalendario();
}

if (btnAnterior) btnAnterior.addEventListener('click', () => cambiarMes(-1));
if (btnSiguiente) btnSiguiente.addEventListener('click', () => cambiarMes(1));

dibujarCalendario();