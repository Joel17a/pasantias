<?php

// ❌ VIOLA SOLID: Una sola clase calcula, persiste y notifica (God Object)
class PedidoServiceMalo
{
    public function procesarPedido(array $productos, string $tipoCliente, string $emailCliente): void
    {
        // 1. Responsabilidad de cálculo y reglas de negocio
        $total = 0;
        foreach ($productos as $producto) {
            $total += $producto['precio'] * $producto['cantidad'];
        }

        if ($tipoCliente === 'VIP') {
            $total *= 0.85; // 15% descuento
        } elseif ($tipoCliente === 'FRECUENTE') {
            $total *= 0.95; // 5% descuento
        }

        // 2. Responsabilidad de persistencia (acoplada directamente)
        echo "[MySQL] INSERT INTO pedidos (cliente, total) VALUES ('$emailCliente', $total)\n";

        // 3. Responsabilidad de notificación (acoplada directamente)
        echo "[SMTP] Enviando correo a $emailCliente: Tu pedido por $$total fue confirmado.\n";
    }
}