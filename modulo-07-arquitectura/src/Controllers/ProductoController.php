<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Producto;
use App\Services\ProductoService;
use RuntimeException;

class ProductoController
{
    private ProductoService $service;

    public function __construct()
    {
        $this->service = new ProductoService(
            new \App\Repositories\ProductoRepository()
        );
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $productos = array_map(
            fn(Producto $p): array => $p->toArray(),
            $this->service->listarProductos()
        );
        echo json_encode($productos, JSON_PRETTY_PRINT);
    }

    public function verDetalle(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $producto = $this->service->obtenerProductoPorId($id);
            echo json_encode($producto->toArray(), JSON_PRETTY_PRINT);
        } catch (RuntimeException $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}