<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductoRepository;

class ProductoController
{
    private ProductoRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductoRepository();
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $productos = $this->repository->obtenerTodos();
        echo json_encode($productos, JSON_PRETTY_PRINT);
    }

    public function verDetalle(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $producto = $this->repository->obtenerPorId($id);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }

        echo json_encode($producto, JSON_PRETTY_PRINT);
    }
}
