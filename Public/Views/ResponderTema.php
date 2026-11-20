<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\ForoController;

$controller = new ForoController();
$controller->guardarRespuesta();