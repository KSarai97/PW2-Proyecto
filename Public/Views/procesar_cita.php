<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\CitaController;

$controller = new CitaController();
$controller->crearCita();