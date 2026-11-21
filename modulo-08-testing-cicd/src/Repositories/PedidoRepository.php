<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Conexion;
use PDO;
use Exception;

class PedidoRepository implements PedidoRepositoryInterface
{
    public function crear(int $usuarioId, array $items): int
    {
        $pdo = Conexion::obtenerInstancia();

        try {
            $pdo->beginTransaction();

            // 1. Calcular total y verificar stock de los productos
            $total = 0;
            foreach ($items as $item) {
                $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ? FOR UPDATE");
                $stmt->execute([$item['producto_id']]);
                $producto = $stmt->fetch();

                if (!$producto) {
                    throw new Exception("Producto con ID {$item['producto_id']} no encontrado.");
                }

                if ($producto['stock'] < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto ID {$item['producto_id']}. Stock disponible: {$producto['stock']}");
                }

                $total += $producto['precio'] * $item['cantidad'];
            }

            // 2. Insertar el pedido principal
            $stmtPedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, estado, total) VALUES (?, 'pagado', ?)");
            $stmtPedido->execute([$usuarioId, $total]);
            $pedidoId = (int) $pdo->lastInsertId();

            // 3. Insertar detalles del pedido y descontar stock
            $stmtDetalle = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, (SELECT precio FROM productos WHERE id = ?))");
            $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $stmtDetalle->execute([$pedidoId, $item['producto_id'], $item['cantidad'], $item['producto_id']]);
                $stmtStock->execute([$item['cantidad'], $item['producto_id']]);
            }

            $pdo->commit();
            return $pedidoId;

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}