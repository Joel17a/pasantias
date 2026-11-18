<?php

declare(strict_types=1);

// =========================================================================
// 5. PATRÓN DECORATOR (Estructural)
// Problema que resuelve: Agregar comportamiento extra (ej. logs o caché) a un
// servicio dinámicamente sin modificar su código original.
// =========================================================================

interface CalculadorEnvioInterface
{
    public function calcular(float $pesoKg): float;
}

class CalculadorEnvioBase implements CalculadorEnvioInterface
{
    public function calcular(float $pesoKg): float
    {
        return $pesoKg * 10.0; // Bs. 10 por kilo
    }
}

class EnvioConLogDecorator implements CalculadorEnvioInterface
{
    public function __construct(private CalculadorEnvioInterface $envioEnvuelto) {}

    public function calcular(float $pesoKg): float
    {
        $costo = $this->envioEnvuelto->calcular($pesoKg);
        echo "[LOG DECORATOR] Se calculó envío para {$pesoKg}kg -> Bs. {$costo}\n";
        return $costo;
    }
}

// =========================================================================
// 6. PATRÓN FACADE (Estructural)
// Problema que resuelve: Proveer una interfaz simple y unificada sobre un
// subsistema complejo de múltiples clases (ej. CheckoutService).
// =========================================================================

class SubsistemaInventario
{
    public function reservarStock(string $producto): void
    {
        echo "[Inventario] Stock reservado para: {$producto}\n";
    }
}

class SubsistemaFacturacion
{
    public function generarFactura(string $cliente, float $monto): void
    {
        echo "[Facturación] Factura emitida a {$cliente} por Bs. {$monto}\n";
    }
}

class SubsistemaDespacho
{
    public function programarEntrega(string $cliente): void
    {
        echo "[Despacho] Guía de envío generada para {$cliente}\n";
    }
}

// Fachada que oculta la complejidad al cliente
class CheckoutFacade
{
    public function __construct(
        private SubsistemaInventario $inventario,
        private SubsistemaFacturacion $facturacion,
        private SubsistemaDespacho $despacho
    ) {}

    public function completarCompra(string $cliente, string $producto, float $monto): void
    {
        $this->inventario->reservarStock($producto);
        $this->facturacion->generarFactura($cliente, $monto);
        $this->despacho->programarEntrega($cliente);
    }
}

// =========================================================================
// 7. PATRÓN OBSERVER (Comportamiento)
// Problema que resuelve: Notificar automáticamente a múltiples módulos o
// suscriptores cuando ocurre un evento (ej. creación de un pedido).
// =========================================================================

interface ObservadorPedidoInterface
{
    public function actualizar(string $codigoPedido, float $total): void;
}

class AuditoriaObserver implements ObservadorPedidoInterface
{
    public function actualizar(string $codigoPedido, float $total): void
    {
        echo "[Auditoría] Registro de nuevo pedido {$codigoPedido} (Bs. {$total})\n";
    }
}

class AlertaVentasObserver implements ObservadorPedidoInterface
{
    public function actualizar(string $codigoPedido, float $total): void
    {
        echo "[Ventas] ¡Nueva venta confirmada! Pedido {$codigoPedido}\n";
    }
}

class GestorEventosPedido
{
    /** @var ObservadorPedidoInterface[] */
    private array $observadores = [];

    public function suscribir(ObservadorPedidoInterface $observador): void
    {
        $this->observadores[] = $observador;
    }

    public function crearPedido(string $codigoPedido, float $total): void
    {
        foreach ($this->observadores as $observador) {
            $observador->actualizar($codigoPedido, $total);
        }
    }
}

// =========================================================================
// 8. PATRÓN REPOSITORY (Arquitectónico)
// Problema que resuelve: Separar la lógica de acceso a datos (SQL/memoria)
// de la lógica de negocio de la aplicación.
// =========================================================================

class Producto
{
    public function __construct(public int $id, public string $nombre, public float $precio) {}
}

interface ProductoRepositoryInterface
{
    public function guardar(Producto $producto): void;
    public function buscarPorId(int $id): ?Producto;
}

class EnMemoriaProductoRepository implements ProductoRepositoryInterface
{
    /** @var array<int, Producto> */
    private array $productos = [];

    public function guardar(Producto $producto): void
    {
        $this->productos[$producto->id] = $producto;
    }

    public function buscarPorId(int $id): ?Producto
    {
        return $this->productos[$id] ?? null;
    }
}

// =========================================================================
// 9. PATRÓN STRATEGY (Comportamiento — Refactorización de pagos)
// Problema que resuelve: Eliminar un if/else gigante intercambiando
// algoritmos de pago en tiempo de ejecución.
// =========================================================================

// ❌ ANTES (Lo que evitamos):
// if ($metodo === 'tarjeta') { ... } elseif ($metodo === 'qr') { ... } elseif ($metodo === 'transferencia') { ... }

// ✅ DESPUÉS (Con Strategy):
interface MetodoPagoStrategyInterface
{
    public function pagar(float $monto): string;
}

class PagoTarjetaStrategy implements MetodoPagoStrategyInterface
{
    public function pagar(float $monto): string
    {
        $comision = $monto * 0.03;
        return "Pagado Bs. " . ($monto + $comision) . " con Tarjeta (incluye 3% comisión)";
    }
}

class PagoQrSimpleStrategy implements MetodoPagoStrategyInterface
{
    public function pagar(float $monto): string
    {
        return "Pagado Bs. {$monto} vía QR Simple (sin comisión)";
    }
}

class PagoTransferenciaStrategy implements MetodoPagoStrategyInterface
{
    public function pagar(float $monto): string
    {
        return "Pagado Bs. {$monto} por Transferencia Bancaria";
    }
}

class ProcesadorPagos
{
    public function __construct(private MetodoPagoStrategyInterface $estrategia) {}

    public function cambiarEstrategia(MetodoPagoStrategyInterface $nuevaEstrategia): void
    {
        $this->estrategia = $nuevaEstrategia;
    }

    public function ejecutarPago(float $monto): string
    {
        return $this->estrategia->pagar($monto);
    }
}

// =========================================================================
// EJECUCIÓN Y PRUEBA DE LOS 5 PATRONES
// =========================================================================

// 5. Prueba Decorator
echo "--- 5. DECORATOR ---\n";
$envio = new EnvioConLogDecorator(new CalculadorEnvioBase());
$envio->calcular(3.5);

// 6. Prueba Facade
echo "\n--- 6. FACADE ---\n";
$checkout = new CheckoutFacade(new SubsistemaInventario(), new SubsistemaFacturacion(), new SubsistemaDespacho());
$checkout->completarCompra("Joel Alanes", "Laptop Lenovo", 5200.0);

// 7. Prueba Observer
echo "\n--- 7. OBSERVER ---\n";
$gestorPedidos = new GestorEventosPedido();
$gestorPedidos->suscribir(new AuditoriaObserver());
$gestorPedidos->suscribir(new AlertaVentasObserver());
$gestorPedidos->crearPedido("ORD-2026-01", 350.0);

// 8. Prueba Repository
echo "\n--- 8. REPOSITORY ---\n";
$repo = new EnMemoriaProductoRepository();
$repo->guardar(new Producto(1, "Monitor 24 pulgadas", 1400.0));
$encontrado = $repo->buscarPorId(1);
echo "[Repository] Producto recuperado: {$encontrado?->nombre} (Bs. {$encontrado?->precio})\n";

// 9. Prueba Strategy (Pagos)
echo "\n--- 9. STRATEGY (PAGOS) ---\n";
$procesador = new ProcesadorPagos(new PagoQrSimpleStrategy());
echo $procesador->ejecutarPago(200.0) . "\n";

$procesador->cambiarEstrategia(new PagoTarjetaStrategy());
echo $procesador->ejecutarPago(200.0) . "\n";