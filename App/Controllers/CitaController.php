<?php
namespace App\Controllers;

use App\Models\CitaModel;

class CitaController {
    private function asegurarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function formatearEspecialista($doc): array {
        $nombreCompleto = trim(
            (string) ($doc['nombre'] ?? '') . ' ' .
            (string) ($doc['apellido_paterno'] ?? '') . ' ' .
            (string) ($doc['apellido_materno'] ?? '')
        );

        return [
            'id' => (string) $doc['_id'],
            'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : ((string) ($doc['usuario'] ?? 'Especialista')),
            'especialidad' => !empty($doc['especialidad']) ? (string) $doc['especialidad'] : 'Psicología Clínica',
            'horarios' => !empty($doc['horarios']) ? (string) $doc['horarios'] : 'Lunes a Viernes: 9:00 AM - 6:00 PM',
            'modalidad' => !empty($doc['modalidad']) ? (string) $doc['modalidad'] : 'Presencial y En línea',
        ];
    }

    public function cargarFormularioCita(): array {
        $this->asegurarSesion();
        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $modelo = new CitaModel();
        $docs = $modelo->obtenerEspecialistas();
        $especialistas = [];

        foreach ($docs as $doc) {
            $especialistas[] = $this->formatearEspecialista($doc);
        }

        return [
            'especialistas' => $especialistas,
            'especialistaSeleccionado' => trim($_GET['especialista_id'] ?? ''),
        ];
    }

    public function crearCita(): void {
        $this->asegurarSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: citas.php");
            exit();
        }

        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $especialistaId = trim($_POST['id_especialista'] ?? '');
        $modalidad = trim($_POST['modalidad'] ?? '');
        $fecha = trim($_POST['fecha'] ?? ''); // YYYY-MM-DD
        $hora = trim($_POST['hora'] ?? '');
        $motivo = trim($_POST['motivo'] ?? '');

        if ($especialistaId === '' || $modalidad === '' || $fecha === '' || $hora === '') {
            echo "<script>alert('Completa todos los campos obligatorios de la cita.'); window.history.back();</script>";
            exit();
        }

        $modelo = new CitaModel();
        $espDoc = $modelo->obtenerEspecialistaPorId($especialistaId);
        if (!$espDoc) {
            echo "<script>alert('El especialista seleccionado no existe.'); window.history.back();</script>";
            exit();
        }

        $espInfo = $this->formatearEspecialista($espDoc);

        $ok = $modelo->crearCita([
            'cliente_id' => (string) ($_SESSION['id'] ?? ''),
            'cliente_correo' => (string) ($_SESSION['correo'] ?? ''),
            'cliente_nombre' => (string) ($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario'),
            'especialista_id' => $espInfo['id'],
            'especialista_nombre' => $espInfo['nombre_completo'],
            'especialidad' => $espInfo['especialidad'],
            'modalidad' => $modalidad,
            'fecha' => $fecha,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => 'Pendiente',
        ]);

        if ($ok) {
            $mes = substr($fecha, 0, 7);
            header("Location: calendario.php?mes=" . urlencode($mes) . "&cita=creada");
            exit();
        }

        echo "<script>alert('No se pudo agendar la cita.'); window.history.back();</script>";
        exit();
    }

    public function cargarCalendario(?string $mesParam): array {
        $this->asegurarSesion();
        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $mesActual = ($mesParam && preg_match('/^\d{4}-\d{2}$/', $mesParam))
            ? $mesParam
            : date('Y-m');

        $inicioMes = \DateTimeImmutable::createFromFormat('Y-m-d', $mesActual . '-01');
        if (!$inicioMes) {
            $inicioMes = new \DateTimeImmutable('first day of this month');
            $mesActual = $inicioMes->format('Y-m');
        }

        $mesAnterior = $inicioMes->modify('-1 month')->format('Y-m');
        $mesSiguiente = $inicioMes->modify('+1 month')->format('Y-m');

        $mesesEs = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
        $mesCortoEs = [
            '01' => 'ENE', '02' => 'FEB', '03' => 'MAR', '04' => 'ABR',
            '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AGO',
            '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DIC'
        ];

        $anioStr = $inicioMes->format('Y');
        $mesNumStr = $inicioMes->format('m');
        $tituloMes = ($mesesEs[$mesNumStr] ?? $mesNumStr) . ' ' . $anioStr;

        $diasEnMes = (int) $inicioMes->format('t');
        $diaSemanaInicio = (int) $inicioMes->format('w'); // 0 = Dom, 6 = Sáb

        $modelo = new CitaModel();
        $citasDocs = $modelo->obtenerCitasPorMes(
            $mesActual,
            (string) ($_SESSION['id'] ?? ''),
            (string) ($_SESSION['rol'] ?? '0')
        );

        $citasPorDia = [];
        $listadoCitas = [];

        foreach ($citasDocs as $c) {
            $fechaStr = (string) ($c['fecha'] ?? ''); // YYYY-MM-DD
            $diaNum = (int) substr($fechaStr, 8, 2);
            $espId = (string) ($c['especialista_id'] ?? '');
            $espDoc = $espId !== '' ? $modelo->obtenerEspecialistaPorId($espId) : null;
            $espDatos = $espDoc
                ? $this->formatearEspecialista($espDoc)
                : [
                    'nombre_completo' => (string) ($c['especialista_nombre'] ?? 'Especialista'),
                    'especialidad' => (string) ($c['especialidad'] ?? 'Psicología Clínica'),
                    'horarios' => 'Lunes a Viernes: 9:00 AM - 6:00 PM',
                    'modalidad' => (string) ($c['modalidad'] ?? 'Presencial'),
                ];

            $item = [
                'id' => (string) $c['_id'],
                'fecha' => $fechaStr,
                'dia_dos_digitos' => str_pad((string) $diaNum, 2, '0', STR_PAD_LEFT),
                'mes_corto' => $mesCortoEs[$mesNumStr] ?? strtoupper($mesNumStr),
                'hora' => (string) ($c['hora'] ?? ''),
                'modalidad' => (string) ($c['modalidad'] ?? $espDatos['modalidad']),
                'motivo' => (string) ($c['motivo'] ?? ''),
                'estado' => (string) ($c['estado'] ?? 'Pendiente'),
                'especialista_nombre' => $espDatos['nombre_completo'],
                'especialidad' => $espDatos['especialidad'],
                'horarios_especialista' => $espDatos['horarios'],
            ];

            $listadoCitas[] = $item;
            if ($diaNum >= 1 && $diaNum <= $diasEnMes) {
                $citasPorDia[$diaNum][] = $item;
            }
        }

        return [
            'mesActual' => $mesActual,
            'mesAnterior' => $mesAnterior,
            'mesSiguiente' => $mesSiguiente,
            'tituloMes' => $tituloMes,
            'diasEnMes' => $diasEnMes,
            'diaSemanaInicio' => $diaSemanaInicio,
            'hoyFecha' => date('Y-m-d'),
            'citasPorDia' => $citasPorDia,
            'listadoCitas' => $listadoCitas,
        ];
    }
}