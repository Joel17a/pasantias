<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Pedido;
use App\Repositories\PedidoRepository;
use InvalidArgumentException;

class PedidoService
{
    public function __construct(
        private PedidoRepository $repository
    ) {}

    public function crearPedido(int $usuarioId, array $items): Pedido
    {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException('usuario_id debe ser un entero positivo.');
        }

        if (empty($items)) {
            throw new InvalidArgumentException('El pedido debe contener al menos un producto.');
        }

        foreach ($items as $item) {
            if (!isset($item['producto_id'], $item['cantidad']) || (int) $item['cantidad'] <= 0) {
                throw new InvalidArgumentException('Cada item requiere producto_id y cantidad mayor a 0.');
            }
        }

        $pedidoId = $this->repository->crear($usuarioId, $items);

        return new Pedido(
            id: $pedidoId,
            usuarioId: $usuarioId,
            estado: 'pagado',
            total: 0.0
        );
    }
}