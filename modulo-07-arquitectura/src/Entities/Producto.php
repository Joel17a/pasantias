<?php

declare(strict_types=1);

namespace App\Entities;

class Producto
{
    public function __construct(
        public readonly int $id,
        public readonly string $sku,
        public readonly string $nombre,
        public readonly float $precio,
        public readonly int $stock,
        public readonly string $categoria
    ) {}

    public function tieneStockSuficiente(int $cantidad): bool
    {
        return $cantidad > 0 && $this->stock >= $cantidad;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'categoria' => $this->categoria,
        ];
    }
}