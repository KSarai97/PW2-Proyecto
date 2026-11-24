
const MESES = [
    'Enero', 'Febrero', 'Marzo', 'Abril',
    'Mayo', 'Junio', 'Julio', 'Agosto',
    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const DIAS = [
    'domingo', 'lunes', 'martes',
    'miércoles', 'jueves', 'viernes', 'sábado'
];

const grid = document.getElementById('calendario-grid');
const titulo = document.getElementById('mes-titulo');
const btnAnterior = document.getElementById('mes-anterior');
const btnSiguiente = document.getElementById('mes-siguiente');
const campoFecha = document.getElementById('fecha');
const textoFecha = document.getElementById('fecha-elegida');
const errorFecha = document.getElementById('fecha-error');
const formulario = document.getElementById('form-cita');

// NUEVO: obtener el selector de horarios.
const campoHora = document.getElementById('hora');

const hoy = new Date();
hoy.setHours(0, 0, 0, 0);

let fechaInicial = campoFecha.value;

let anio = hoy.getFullYear();
let mes = hoy.getMonth();

// Si estamos modificando una cita,
// abrir el calendario en el mes de esa cita.

if (fechaInicial) {

    const partes = fechaInicial.split('-');

    anio = Number(partes[0]);
    mes = Number(partes[1]) - 1;

}

function pad(n) {
    return String(n).padStart(2, '0');
}


function actualizarHorarios() {

    const horarios = document.querySelectorAll(
        'input[name="hora"]'
    );

    if (!campoFecha.value) return;

    const ahora = new Date();

    const fechaActual =
        ahora.getFullYear() + '-' +
        pad(ahora.getMonth() + 1) + '-' +
        pad(ahora.getDate());

    horarios.forEach(radio => {

        let deshabilitar = false;

        if (campoFecha.value < fechaActual) {

            deshabilitar = true;

        } else if (campoFecha.value === fechaActual) {

            const [horas, minutos] = radio.value.split(':');

            const horaSeleccionada = new Date();

            horaSeleccionada.setHours(
                Number(horas),
                Number(minutos),
                0,
                0
            );

            if (horaSeleccionada <= ahora) {
                deshabilitar = true;
            }

        }

        radio.disabled = deshabilitar;

        if (deshabilitar && radio.checked) {
            radio.checked = false;
        }

    });

}


// =====================================
// NUEVO: ACTUALIZAR HORARIOS DISPONIBLES
// =====================================

function actualizarHorarios() {

    if (!campoHora || !campoFecha.value) return;

    const ahora = new Date();

    const fechaActual =
        ahora.getFullYear() + '-' +
        pad(ahora.getMonth() + 1) + '-' +
        pad(ahora.getDate());

    const fechaSeleccionada = campoFecha.value;

    Array.from(campoHora.options).forEach(opcion => {

        if (!opcion.value) return;

        let deshabilitar = false;

        // No permitir fechas anteriores.
        if (fechaSeleccionada < fechaActual) {

            deshabilitar = true;

        }

        // Si se selecciona el día actual,
        // comprobar si la hora ya pasó.
        else if (fechaSeleccionada === fechaActual) {

            const [horas, minutos] = opcion.value.split(':');

            const horaSeleccionada = new Date();

            horaSeleccionada.setHours(
                Number(horas),
                Number(minutos),
                0,
                0
            );

            if (horaSeleccionada <= ahora) {

                deshabilitar = true;

            }

        }

        opcion.disabled = deshabilitar;

    });

    // Limpiar el horario si dejó de estar disponible.
    const opcionSeleccionada = campoHora.selectedOptions[0];

    if (opcionSeleccionada && opcionSeleccionada.disabled) {

        campoHora.value = '';

    }

}

function dibujarCalendario() {

    titulo.textContent = MESES[mes] + ' ' + anio;

    btnAnterior.disabled =
        (anio === hoy.getFullYear() && mes === hoy.getMonth()) ||
        (anio < hoy.getFullYear());

    grid.querySelectorAll('.calendar-day').forEach(d => d.remove());

    const primerDia = new Date(anio, mes, 1).getDay();

    const diasEnMes = new Date(anio, mes + 1, 0).getDate();

    for (let i = 0; i < primerDia; i++) {

        const vacio = document.createElement('div');

        vacio.className = 'calendar-day empty';

        grid.appendChild(vacio);

    }

    for (let dia = 1; dia <= diasEnMes; dia++) {

        const fecha =
            anio + '-' + pad(mes + 1) + '-' + pad(dia);

        const celda = document.createElement('button');

        celda.type = 'button';

        celda.className = 'calendar-day';

        celda.textContent = dia;

        const fechaCelda = new Date(anio, mes, dia);

        fechaCelda.setHours(0, 0, 0, 0);

        if (fechaCelda < hoy) {

            celda.disabled = true;

        }

        if (fechaCelda.getTime() === hoy.getTime()) {

            celda.classList.add('today');

        }

        if (fecha === campoFecha.value) {

            celda.classList.add('selected');

            celda.setAttribute('aria-pressed', 'true');

        }

        celda.addEventListener('click', () => {

            elegirDia(fecha, fechaCelda);

        });

        grid.appendChild(celda);

    }

}

function elegirDia(fecha, objetoFecha) {

    campoFecha.value = fecha;

    errorFecha.hidden = true;

    textoFecha.textContent =
        'Día elegido: ' +
        DIAS[objetoFecha.getDay()] + ' ' +
        objetoFecha.getDate() + ' de ' +
        MESES[objetoFecha.getMonth()].toLowerCase() +
        ' de ' + objetoFecha.getFullYear();

    // NUEVO: actualizar horarios al elegir otro día.
    actualizarHorarios();

    dibujarCalendario();

}

function cambiarMes(delta) {

    mes += delta;

    if (mes < 0) {

        mes = 11;
        anio--;

    }

    if (mes > 11) {

        mes = 0;
        anio++;

    }

    dibujarCalendario();

}

btnAnterior.addEventListener('click', () => cambiarMes(-1));

btnSiguiente.addEventListener('click', () => cambiarMes(1));

// Validar fecha antes de enviar el formulario.

formulario.addEventListener('submit', e => {

    if (!campoFecha.value) {

        e.preventDefault();

        errorFecha.hidden = false;

        grid.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        return;

    }

    // NUEVO: actualizar horarios antes del envío.
    actualizarHorarios();

    // Comprobar que se haya seleccionado un horario válido.
    if (
        !campoHora.value ||
        campoHora.selectedOptions[0]?.disabled
    ) {

        e.preventDefault();

        alert('Selecciona un horario disponible para continuar.');

    }

});

// Dibujar calendario.
dibujarCalendario();

// NUEVO: comprobar horarios al abrir el formulario.
actualizarHorarios();

// NUEVO: actualizar horarios si el usuario deja abierta la página.
setInterval(actualizarHorarios, 30000);
