<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Conexion;
use PDO;

class ProductoRepository
{
    public function obtenerTodos(): array
    {
        $pdo = Conexion::obtenerInstancia();
        $stmt = $pdo->query("SELECT p.id, p.sku, p.nombre, p.precio, p.stock, c.nombre AS categoria 
                             FROM productos p 
                             INNER JOIN categorias c ON p.categoria_id = c.id");
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $pdo = Conexion::obtenerInstancia();
        $stmt = $pdo->prepare("SELECT p.id, p.sku, p.nombre, p.precio, p.stock, c.nombre AS categoria 
                               FROM productos p 
                               INNER JOIN categorias c ON p.categoria_id = c.id 
                               WHERE p.id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        return $producto ?: null;
    }
}