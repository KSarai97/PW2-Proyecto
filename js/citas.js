const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
               'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

const grid = document.getElementById('calendario-grid');
const titulo = document.getElementById('mes-titulo');
const btnAnterior = document.getElementById('mes-anterior');
const btnSiguiente = document.getElementById('mes-siguiente');
const campoFecha = document.getElementById('fecha');
const textoFecha = document.getElementById('fecha-elegida');
const errorFecha = document.getElementById('fecha-error');
const formulario = document.getElementById('form-cita');

const hoy = new Date();
hoy.setHours(0, 0, 0, 0);

let anio = hoy.getFullYear();
let mes = hoy.getMonth();

function pad(n) {
    return String(n).padStart(2, '0');
}

function dibujarCalendario() {
    titulo.textContent = MESES[mes] + ' ' + anio;

    // No se puede ir a meses anteriores al actual
    btnAnterior.disabled = (anio === hoy.getFullYear() && mes === hoy.getMonth());

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
        if (fechaCelda < hoy) celda.disabled = true;
        if (fechaCelda.getTime() === hoy.getTime()) celda.classList.add('today');

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
    errorFecha.hidden = true;
    textoFecha.textContent = 'Día elegido: ' + DIAS[objetoFecha.getDay()] + ' ' +
        objetoFecha.getDate() + ' de ' + MESES[objetoFecha.getMonth()].toLowerCase() + ' de ' + objetoFecha.getFullYear();
    dibujarCalendario();
}

function cambiarMes(delta) {
    mes += delta;
    if (mes < 0) { mes = 11; anio--; }
    if (mes > 11) { mes = 0; anio++; }
    dibujarCalendario();
}

btnAnterior.addEventListener('click', () => cambiarMes(-1));
btnSiguiente.addEventListener('click', () => cambiarMes(1));

// El día elegido no es un campo "required" nativo, así que se valida aquí
formulario.addEventListener('submit', e => {
    if (!campoFecha.value) {
        e.preventDefault();
        errorFecha.hidden = false;
        grid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

dibujarCalendario();