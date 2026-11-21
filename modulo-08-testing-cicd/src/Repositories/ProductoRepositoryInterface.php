<?php

declare(strict_types=1);

namespace App\Repositories;

interface ProductoRepositoryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function obtenerTodos(): array;

    /** @return array<string, mixed>|null */
    public function obtenerPorId(int $id): ?array;
}