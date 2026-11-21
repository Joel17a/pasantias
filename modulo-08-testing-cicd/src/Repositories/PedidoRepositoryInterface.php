<?php

declare(strict_types=1);

namespace App\Repositories;

interface PedidoRepositoryInterface
{
    /** @param array<int, array<string, mixed>> $items */
    public function crear(int $usuarioId, array $items): int;
}