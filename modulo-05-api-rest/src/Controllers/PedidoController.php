<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\PedidoRepository;
use Exception;

class PedidoController
{
    private PedidoRepository $repository;

    public function __construct()
    {
        $this->repository = new PedidoRepository();
    }

    public function crear(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        // Leer el cuerpo de la petición JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['usuario_id']) || !isset($input['items']) || !is_array($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos. Se requiere usuario_id y un array de items.']);
            return;
        }

        try {
            $pedidoId = $this->repository->crear((int) $input['usuario_id'], $input['items']);
            http_response_code(201);
            echo json_encode([
                'mensaje' => 'Pedido creado con éxito bajo una transacción ACID',
                'pedido_id' => $pedidoId
            ], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}