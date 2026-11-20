// =========================================
// DATOS DE EJEMPLO
// Reemplaza este arreglo por los datos reales
// (base de datos / servidor) cuando los tengas.
// fecha: AAAA-MM-DD | estado: 'confirmada' o 'pendiente'
// =========================================
const citas = [
    { id: 1, fecha: '2026-10-08', hora: '11:00 AM', especialista: 'Dra. María Fernández', modalidad: 'Presencial', estado: 'confirmada' },
    { id: 2, fecha: '2026-10-22', hora: '04:00 PM', especialista: 'Lic. Roberto Sánchez', modalidad: 'En línea', estado: 'pendiente' },
    { id: 3, fecha: '2026-11-12', hora: '10:00 AM', especialista: 'Dra. María Fernández', modalidad: 'En línea', estado: 'confirmada' }
];

const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
               'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

const grid = document.getElementById('calendario-grid');
const titulo = document.getElementById('mes-titulo');
const lista = document.getElementById('citas-lista');
const tituloCitas = document.getElementById('citas-titulo');

const hoy = new Date();
let anio = hoy.getFullYear();
let mes = hoy.getMonth(); // 0 = enero

function pad(n) {
    return String(n).padStart(2, '0');
}

function crear(etiqueta, clase, texto) {
    const el = document.createElement(etiqueta);
    if (clase) el.className = clase;
    if (texto !== undefined) el.textContent = texto;
    return el;
}

function citasDelMes() {
    const prefijo = anio + '-' + pad(mes + 1) + '-';
    return citas
        .filter(c => c.fecha.startsWith(prefijo))
        .sort((a, b) => a.fecha.localeCompare(b.fecha));
}

function dibujarCalendario() {
    titulo.textContent = MESES[mes] + ' ' + anio;

    // Quita los días del mes anterior (deja las etiquetas Dom–Sáb)
    grid.querySelectorAll('.calendar-day').forEach(d => d.remove());

    const primerDia = new Date(anio, mes, 1).getDay();
    const diasEnMes = new Date(anio, mes + 1, 0).getDate();
    const delMes = citasDelMes();

    for (let i = 0; i < primerDia; i++) {
        grid.appendChild(crear('div', 'calendar-day empty'));
    }

    for (let dia = 1; dia <= diasEnMes; dia++) {
        const fecha = anio + '-' + pad(mes + 1) + '-' + pad(dia);
        const celda = crear('div', 'calendar-day');
        celda.appendChild(crear('span', '', dia));

        const esHoy = dia === hoy.getDate() && mes === hoy.getMonth() && anio === hoy.getFullYear();
        if (esHoy) {
            celda.classList.add('today');
            celda.setAttribute('aria-label', 'Hoy, ' + dia);
        }

        delMes.filter(c => c.fecha === fecha).forEach(c => {
            celda.classList.add('has-event');
            const pill = crear('a', 'event-pill ' + c.estado, c.hora);
            pill.href = 'Gestion_citas.html?id=' + c.id;
            pill.title = c.especialista + ' – ' + c.modalidad;
            pill.setAttribute('aria-label', 'Cita ' + c.hora + ' con ' + c.especialista);
            celda.appendChild(pill);
        });

        grid.appendChild(celda);
    }
}

function dibujarLista() {
    lista.replaceChildren();
    tituloCitas.textContent = 'Citas de ' + MESES[mes].toLowerCase();

    const delMes = citasDelMes();

    if (delMes.length === 0) {
        const vacio = crear('p', 'no-citas', 'No tienes citas este mes. ');
        const enlace = crear('a', '', 'Agendar una cita');
        enlace.href = 'citas.html';
        vacio.appendChild(enlace);
        lista.appendChild(vacio);
        return;
    }

    delMes.forEach(c => {
        const tarjeta = crear('div', 'appointment-card');

        const fecha = crear('div', 'appointment-date');
        fecha.appendChild(crear('span', 'day', c.fecha.slice(8, 10)));
        fecha.appendChild(crear('span', 'month', MESES[mes].slice(0, 3).toUpperCase()));

        const info = crear('div', 'appointment-info');
        info.appendChild(crear('strong', '', c.especialista));
        info.appendChild(crear('p', '', c.hora + ' · Modalidad: ' + c.modalidad));

        const confirmada = c.estado === 'confirmada';
        const estado = crear(
            'a',
            'appointment-status ' + (confirmada ? 'status-confirmed' : 'status-pending'),
            confirmada ? 'Confirmada' : 'Pendiente'
        );
        estado.href = 'Gestion_citas.html?id=' + c.id;
        estado.title = 'Ver detalles de la cita';

        tarjeta.append(fecha, info, estado);
        lista.appendChild(tarjeta);
    });
}

function cambiarMes(delta) {
    mes += delta;
    if (mes < 0) { mes = 11; anio--; }
    if (mes > 11) { mes = 0; anio++; }
    dibujarCalendario();
    dibujarLista();
}

document.getElementById('mes-anterior').addEventListener('click', () => cambiarMes(-1));
document.getElementById('mes-siguiente').addEventListener('click', () => cambiarMes(1));

dibujarCalendario();
dibujarLista();