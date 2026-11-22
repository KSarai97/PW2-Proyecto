
// =========================================
// CALENDARIO DE CITAS CON MONGODB
// =========================================

let citas = [];

const MESES = [
    'Enero', 'Febrero', 'Marzo', 'Abril',
    'Mayo', 'Junio', 'Julio', 'Agosto',
    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const grid = document.getElementById('calendario-grid');
const titulo = document.getElementById('mes-titulo');
const lista = document.getElementById('citas-lista');
const tituloCitas = document.getElementById('citas-titulo');

const hoy = new Date();

let anio = hoy.getFullYear();
let mes = hoy.getMonth();


// =========================================
// OBTENER CITAS DESDE MONGODB
// =========================================

async function cargarCitas() {

    try {

      //  const respuesta = await fetch('obtener_citas.php');
      const respuesta = await fetch(
    'obtener_citas.php?t=' + Date.now()
);

        if (!respuesta.ok) {
            throw new Error('Error al consultar las citas.');
        }

        const datos = await respuesta.json();

        if (!Array.isArray(datos)) {
            throw new Error('La respuesta del servidor no es válida.');
        }

        citas = datos;

        dibujarCalendario();
        dibujarLista();

    } catch (error) {

        console.error('Error:', error);

        lista.textContent =
            'No fue posible cargar las citas. Intenta nuevamente.';

    }

}


// =========================================
// FUNCIONES AUXILIARES
// =========================================

function pad(n) {
    return String(n).padStart(2, '0');
}

function crear(etiqueta, clase, texto) {

    const el = document.createElement(etiqueta);

    if (clase) {
        el.className = clase;
    }

    if (texto !== undefined) {
        el.textContent = texto;
    }

    return el;

}


// =========================================
// OBTENER CITAS DEL MES
// =========================================

function citasDelMes() {

    const prefijo = anio + '-' + pad(mes + 1) + '-';

    return citas
        .filter(c => c.fecha.startsWith(prefijo))
        .sort((a, b) => {

            const comparacion = a.fecha.localeCompare(b.fecha);

            if (comparacion !== 0) {
                return comparacion;
            }

            return a.hora.localeCompare(b.hora);

        });

}


// =========================================
// DIBUJAR CALENDARIO
// =========================================

function dibujarCalendario() {

    titulo.textContent = MESES[mes] + ' ' + anio;

    // Eliminar días anteriores
    grid.querySelectorAll('.calendar-day').forEach(d => d.remove());

    const primerDia = new Date(anio, mes, 1).getDay();

    const diasEnMes = new Date(anio, mes + 1, 0).getDate();

    const delMes = citasDelMes();

    // Espacios antes del primer día
    for (let i = 0; i < primerDia; i++) {

        grid.appendChild(
            crear('div', 'calendar-day empty')
        );

    }

    // Crear días del mes
    for (let dia = 1; dia <= diasEnMes; dia++) {

        const fecha = anio + '-' + pad(mes + 1) + '-' + pad(dia);

        const celda = crear('div', 'calendar-day');

        celda.appendChild(
            crear('span', '', dia)
        );

        // Identificar el día actual
        const esHoy =
            dia === hoy.getDate() &&
            mes === hoy.getMonth() &&
            anio === hoy.getFullYear();

        if (esHoy) {

            celda.classList.add('today');

            celda.setAttribute(
                'aria-label',
                'Hoy, ' + dia
            );

        }

        // Buscar citas correspondientes al día
        delMes
            .filter(c => c.fecha === fecha)
            .forEach(c => {

                celda.classList.add('has-event');

                const hora = c.hora.slice(0, 5);

                const pill = crear(
                    'span',
                    'event-pill ' + c.estado,
                    hora
                );

                pill.title =
                    c.especialista + ' - ' + c.modalidad;

                pill.setAttribute(
                    'aria-label',
                    'Cita ' + hora + ' con ' + c.especialista
                );

                celda.appendChild(pill);

            });

        grid.appendChild(celda);

    }

}


// =========================================
// DIBUJAR LISTA DE CITAS
// =========================================

function dibujarLista() {

    lista.replaceChildren();

    tituloCitas.textContent =
        'Citas de ' + MESES[mes].toLowerCase();

    const delMes = citasDelMes();

    if (delMes.length === 0) {

        const vacio = crear(
            'p',
            'no-citas',
            'No tienes citas este mes. '
        );

        const enlace = crear(
            'a',
            '',
            'Agendar una cita'
        );

        enlace.href = 'citas.php';

        vacio.appendChild(enlace);

        lista.appendChild(vacio);

        return;

    }

    delMes.forEach(c => {

        const tarjeta = crear('div', 'appointment-card');

        // =====================================
        // FECHA
        // =====================================

        const fecha = crear('div', 'appointment-date');

        fecha.appendChild(
            crear('span', 'day', c.fecha.slice(8, 10))
        );

        fecha.appendChild(
            crear('span', 'month', MESES[mes].slice(0, 3).toUpperCase())
        );


        // =====================================
        // INFORMACIÓN DE LA CITA
        // =====================================

        const info = crear('div', 'appointment-info');

        info.appendChild(
            crear('strong', '', c.especialista)
        );

        info.appendChild(
            crear(
                'p',
                '',
                c.hora + ' · Modalidad: ' + c.modalidad
            )
        );


        
// =====================================
// ESTADO DE LA CITA
// =====================================
const cancelada = c.estado === 'cancelada';
const completada = c.estado === 'completada';

let textoEstado = 'Pendiente';
let claseEstado = 'status-pending';

if (completada) {
    textoEstado = 'Completada';
    claseEstado = 'status-completed';
}

if (cancelada) {
    textoEstado = 'Cancelada';
    claseEstado = 'status-cancelled';
}
const estado = crear(
    'span',
    'appointment-status ' + claseEstado,
    textoEstado
);


// =====================================
// BOTONES DE ACCIONES
// =====================================

const acciones = crear('div', 'appointment-actions');

// Si la cita no está cancelada, mostrar botones
if (!cancelada && !completada) {

    // Botón modificar
    const botonModificar = crear(
        'a',
        'btn-accion btn-modificar',
        '✎'
    );

    botonModificar.href =
        'citas.php?id=' + encodeURIComponent(c.id);

    botonModificar.title = 'Modificar cita';

    botonModificar.setAttribute(
        'aria-label',
        'Modificar cita'
    );


    // Formulario para cancelar
    const formularioCancelar = crear('form', '');

    formularioCancelar.action = 'cancelar_cita.php';
    formularioCancelar.method = 'POST';

    // ID de la cita
    const campoId = crear('input', '');

    campoId.type = 'hidden';
    campoId.name = 'id_cita';
    campoId.value = c.id;

    // Botón cancelar
    const botonCancelar = crear(
        'button',
        'btn-accion btn-cancelar',
        '×'
    );

    botonCancelar.type = 'submit';
    botonCancelar.title = 'Cancelar cita';

    botonCancelar.setAttribute(
        'aria-label',
        'Cancelar cita'
    );

    // Confirmación antes de cancelar
    formularioCancelar.addEventListener('submit', function(event) {

        const confirmar = window.confirm(
            '¿Estás seguro de que deseas cancelar esta cita?'
        );

        if (!confirmar) {
            event.preventDefault();
        }

    });

    formularioCancelar.append(campoId, botonCancelar);

    acciones.append(botonModificar, formularioCancelar);

}


// =====================================
// AGREGAR ELEMENTOS A LA TARJETA
// =====================================

tarjeta.append(
    fecha,
    info,
    estado,
    acciones
);

lista.appendChild(tarjeta);


    });

}


// =========================================
// CAMBIAR DE MES
// =========================================

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
    dibujarLista();

}


// =========================================
// EVENTOS DE NAVEGACIÓN
// =========================================

document
    .getElementById('mes-anterior')
    .addEventListener('click', () => cambiarMes(-1));

document
    .getElementById('mes-siguiente')
    .addEventListener('click', () => cambiarMes(1));


// =========================================
// INICIAR CALENDARIO
// =========================================

cargarCitas();
