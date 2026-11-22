<?php
namespace App\Models;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ForoModel {
    private $temasCollection;
    private $respuestasCollection;

    public function __construct() {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->PW2_Proyecto;
        $this->temasCollection = $db->Temas;
        $this->respuestasCollection = $db->Respuestas;
    }

    public function obtenerTemas(?string $categoria = null): array {
        $filtro = [];
        if ($categoria !== null && $categoria !== '' && $categoria !== 'Todas las discusiones') {
            $filtro['categoria'] = $categoria;
        }

        $cursor = $this->temasCollection->find(
            $filtro,
            ['sort' => ['creado_en' => -1]]
        );

        $temas = [];
        foreach ($cursor as $tema) {
            $temaIdStr = (string) $tema['_id'];
            $totalRespuestas = $this->respuestasCollection->countDocuments(['tema_id' => $temaIdStr]);

            $temas[] = [
                'id' => $temaIdStr,
                'titulo' => (string) ($tema['titulo'] ?? ''),
                'categoria' => (string) ($tema['categoria'] ?? 'General'),
                'contenido' => (string) ($tema['contenido'] ?? ''),
                'autor_usuario' => (string) ($tema['autor_usuario'] ?? 'Anónimo'),
                'autor_rol' => (string) ($tema['autor_rol'] ?? '0'),
                'vistas' => (int) ($tema['vistas'] ?? 0),
                'respuestas_count' => (int) $totalRespuestas,
                'creado_en' => $tema['creado_en'] ?? null,
            ];
        }

        return $temas;
    }

    public function crearTema(array $datos): bool {
        $datos['creado_en'] = new UTCDateTime();
        $datos['vistas'] = 0;
        $resultado = $this->temasCollection->insertOne($datos);
        return $resultado->getInsertedCount() > 0;
    }

    public function obtenerTemaPorId(string $id, bool $incrementarVista = false): ?array {
        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return null;
        }

        if ($incrementarVista) {
            $this->temasCollection->updateOne(
                ['_id' => $objectId],
                ['$inc' => ['vistas' => 1]]
            );
        }

        $tema = $this->temasCollection->findOne(['_id' => $objectId]);
        if (!$tema) {
            return null;
        }

        return [
            'id' => (string) $tema['_id'],
            'titulo' => (string) ($tema['titulo'] ?? ''),
            'categoria' => (string) ($tema['categoria'] ?? 'General'),
            'contenido' => (string) ($tema['contenido'] ?? ''),
            'autor_usuario' => (string) ($tema['autor_usuario'] ?? 'Anónimo'),
            'autor_rol' => (string) ($tema['autor_rol'] ?? '0'),
            'vistas' => (int) ($tema['vistas'] ?? 0),
            'creado_en' => $tema['creado_en'] ?? null,
        ];
    }

    public function obtenerRespuestas(string $temaId): array {
        $cursor = $this->respuestasCollection->find(
            ['tema_id' => $temaId],
            ['sort' => ['creado_en' => 1]]
        );

        $respuestas = [];
        foreach ($cursor as $resp) {
            $respuestas[] = [
                'id' => (string) $resp['_id'],
                'tema_id' => (string) ($resp['tema_id'] ?? ''),
                'autor_usuario' => (string) ($resp['autor_usuario'] ?? 'Usuario'),
                'autor_rol' => (string) ($resp['autor_rol'] ?? '0'),
                'comentario' => (string) ($resp['comentario'] ?? ''),
                'creado_en' => $resp['creado_en'] ?? null,
            ];
        }

        return $respuestas;
    }

    public function agregarRespuesta(array $datos): bool {
        $datos['creado_en'] = new UTCDateTime();
        $resultado = $this->respuestasCollection->insertOne($datos);
        return $resultado->getInsertedCount() > 0;
    }

    public function eliminarRespuesta(string $respuestaId): bool {
        try {
            $objectId = new ObjectId($respuestaId);
        } catch (\Exception $e) {
            return false;
        }

        $resultado = $this->respuestasCollection->deleteOne(['_id' => $objectId]);
        return $resultado->getDeletedCount() > 0;
    }

}

