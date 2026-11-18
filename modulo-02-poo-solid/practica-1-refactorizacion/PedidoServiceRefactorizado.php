<?php

declare(strict_types=1);

// ==========================================
// 1. ABSTRACCIONES (Interfaces - DIP & ISP)
// ==========================================

interface EstrategiaDescuentoInterface
{
    public function aplicar(float $monto): float;
}

interface PedidoRepositoryInterface
{
    public function guardar(string $clienteEmail, float $total): void;
}

interface NotificadorInterface
{
    public function enviar(string $destinatario, string $mensaje): void;
}

// ==========================================
// 2. IMPLEMENTACIONES DE DESCUENTO (OCP)
// Si mañana hay un descuento "BlackFriday", creamos una clase nueva sin tocar las existentes.
// ==========================================

class DescuentoRegular implements EstrategiaDescuentoInterface
{
    public function aplicar(float $monto): float
    {
        return $monto;
    }
}

class DescuentoVip implements EstrategiaDescuentoInterface
{
    public function aplicar(float $monto): float
    {
        return $monto * 0.85;
    }
}

// ==========================================
// 3. SERVICIO DE CÁLCULO (SRP)
// ==========================================

class CalculadoraPedido
{
    public function calcularTotal(array $productos, EstrategiaDescuentoInterface $descuento): float
    {
        $subtotal = 0.0;
        foreach ($productos as $producto) {
            $subtotal += $producto['precio'] * $producto['cantidad'];
        }

        return $descuento->aplicar($subtotal);
    }
}

// ==========================================
// 4. PERSISTENCIA Y NOTIFICACIÓN (SRP)
// ==========================================

class MySqlPedidoRepository implements PedidoRepositoryInterface
{
    public function guardar(string $clienteEmail, float $total): void
    {
        echo "[MySQL] Guardando pedido de {$clienteEmail} por \${$total}\n";
    }
}

class EmailNotificador implements NotificadorInterface
{
    public function enviar(string $destinatario, string $mensaje): void
    {
        echo "[Email] Para {$destinatario}: {$mensaje}\n";
    }
}

// ==========================================
// 5. ORQUESTADOR (Depende de abstracciones - DIP)
// ==========================================

class PedidoService
{
    public function __construct(
        private CalculadoraPedido $calculadora,
        private PedidoRepositoryInterface $repository,
        private NotificadorInterface $notificador
    ) {}

    public function procesarPedido(
        array $productos,
        string $emailCliente,
        EstrategiaDescuentoInterface $descuento
    ): void {
        $total = $this->calculadora->calcularTotal($productos, $descuento);
        $this->repository->guardar($emailCliente, $total);
        $this->notificador->enviar($emailCliente, "Tu pedido por \${$total} ha sido confirmado.");
    }
}

// ==========================================
// EJECUCIÓN DE PRUEBA
// ==========================================

$productos = [
    ['nombre' => 'Teclado Mecánico', 'precio' => 80.0, 'cantidad' => 1],
    ['nombre' => 'Mouse Gamer', 'precio' => 40.0, 'cantidad' => 2],
];

$pedidoService = new PedidoService(
    new CalculadoraPedido(),
    new MySqlPedidoRepository(),
    new EmailNotificador()
);

$pedidoService->procesarPedido($productos, 'joel@ejemplo.com', new DescuentoVip());