<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use App\Controllers\UsuarioController;

$controller = new UsuarioController();
$controller->editarPerfil();