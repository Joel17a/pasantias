<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\PedidoRepository;
use App\Services\PedidoService;
use Throwable;

class PedidoController
{
    private PedidoService $service;

    public function __construct()
    {
        $this->service = new PedidoService(new PedidoRepository());
    }

    public function crear(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['usuario_id']) || !isset($input['items']) || !is_array($input['items'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos. Se requiere usuario_id y un array de items.']);
            return;
        }

        try {
            $pedido = $this->service->crearPedido((int) $input['usuario_id'], $input['items']);
            http_response_code(201);
            echo json_encode([
                'mensaje' => 'Pedido creado con éxito bajo una transacción ACID',
                'pedido' => $pedido->toArray(),
            ], JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}