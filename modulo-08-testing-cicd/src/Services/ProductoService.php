<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Producto;
use App\Repositories\ProductoRepository;
use App\Repositories\ProductoRepositoryInterface;
use RuntimeException;

class ProductoService
{
    private ProductoRepositoryInterface $repository;

    public function __construct(?ProductoRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new ProductoRepository();
    }

    /** @return Producto[] */
    public function listarProductos(): array
    {
        $filas = $this->repository->obtenerTodos();
        return array_map(
            fn(array $row): Producto => new Producto(
                id: (int) $row['id'],
                sku: (string) $row['sku'],
                nombre: (string) $row['nombre'],
                precio: (float) $row['precio'],
                stock: (int) $row['stock'],
                categoria: (string) $row['categoria']
            ),
            $filas
        );
    }

    public function obtenerProductoPorId(int $id): Producto
    {
        if ($id <= 0) {
            throw new RuntimeException('El ID del producto debe ser mayor a cero.');
        }

        $row = $this->repository->obtenerPorId($id);
        if (!$row) {
            throw new RuntimeException('Producto no encontrado');
        }

        return new Producto(
            id: (int) $row['id'],
            sku: (string) $row['sku'],
            nombre: (string) $row['nombre'],
            precio: (float) $row['precio'],
            stock: (int) $row['stock'],
            categoria: (string) $row['categoria']
        );
    }
}