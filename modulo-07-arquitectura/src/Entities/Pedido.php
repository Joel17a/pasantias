<?php

declare(strict_types=1);

namespace App\Entities;

class Pedido
{
    public function __construct(
        public readonly int $id,
        public readonly int $usuarioId,
        public readonly string $estado,
        public readonly float $total
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuarioId,
            'estado' => $this->estado,
            'total' => $this->total,
        ];
    }
}