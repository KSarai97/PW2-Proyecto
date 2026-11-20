<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class Conexion {
    public static function conectar() {
        try {
            $client = new MongoDB\Client("mongodb://localhost:27017");
            return $client->PW2_Proyecto; 
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}