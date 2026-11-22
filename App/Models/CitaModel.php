<?php
namespace App\Models;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class CitaModel {
    private $citasCollection;
    private $usuariosCollection;

    public function __construct() {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->PW2_Proyecto;
        $this->citasCollection = $db->Citas;
        $this->usuariosCollection = $db->Usuarios;
    }

    public function obtenerEspecialistas(): array {
        $cursor = $this->usuariosCollection->find([
            'rol' => ['$in' => ['1', 1]]
        ]);

        $especialistas = [];
        foreach ($cursor as $doc) {
            $especialistas[] = $doc;
        }

        return $especialistas;
    }

    public function obtenerEspecialistaPorId(string $id) {
        try {
            return $this->usuariosCollection->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function crearCita(array $datos): bool {
        $datos['creado_en'] = new UTCDateTime();
        $resultado = $this->citasCollection->insertOne($datos);
        return $resultado->getInsertedCount() > 0;
    }

    public function obtenerCitasPorMes(string $anioMes, ?string $usuarioId = null, ?string $rol = null): array {
        // $anioMes tiene formato "YYYY-MM", por ejemplo "2026-11"
        $filtro = [
            'fecha' => ['$regex' => '^' . preg_quote($anioMes, '/')]
        ];

        // Si es cliente ('0'), ve sus citas; si es especialista ('1'), ve citas asignadas a él; admin ('2') ve todas
        if ($rol === '0' && $usuarioId) {
            $filtro['cliente_id'] = $usuarioId;
        } elseif ($rol === '1' && $usuarioId) {
            $filtro['especialista_id'] = $usuarioId;
        }

        $cursor = $this->citasCollection->find(
            $filtro,
            ['sort' => ['fecha' => 1, 'hora' => 1]]
        );

        $citas = [];
        foreach ($cursor as $cita) {
            $citas[] = $cita;
        }

        return $citas;
    }
}