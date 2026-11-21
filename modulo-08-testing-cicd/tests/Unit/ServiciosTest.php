<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\PedidoRepositoryInterface;
use App\Repositories\ProductoRepositoryInterface;
use App\Services\PedidoService;
use App\Services\ProductoService;
use InvalidArgumentException;
use RuntimeException;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../../src/';
    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

function assertTrue(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException("FALLÓ PRUEBA: $mensaje");
    }
    echo "[PASS] $mensaje\n";
}

function assertThrows(callable $fn, string $claseExcepcion, string $mensaje): void
{
    try {
        $fn();
    } catch (\Throwable $e) {
        if ($e instanceof $claseExcepcion) {
            echo "[PASS] $mensaje\n";
            return;
        }
        throw new RuntimeException("FALLÓ PRUEBA ($mensaje): se esperaba $claseExcepcion, llegó " . get_class($e));
    }
    throw new RuntimeException("FALLÓ PRUEBA ($mensaje): no se lanzó excepción $claseExcepcion");
}

$mockProductoRepo = new class implements ProductoRepositoryInterface {
    public function obtenerTodos(): array
    {
        return [
            [
                'id' => 1,
                'sku' => 'LAP-001',
                'nombre' => 'Laptop Pro',
                'precio' => 4500.0,
                'stock' => 10,
                'categoria' => 'Computación',
            ],
        ];
    }

    public function obtenerPorId(int $id): ?array
    {
        if ($id === 1) {
            return $this->obtenerTodos()[0];
        }
        return null;
    }
};

$mockPedidoRepo = new class implements PedidoRepositoryInterface {
    public int $vecesLlamado = 0;

    public function crear(int $usuarioId, array $items): int
    {
        $this->vecesLlamado++;
        return 99;
    }
};

$productoService = new ProductoService($mockProductoRepo);
$productos = $productoService->listarProductos();
assertTrue(count($productos) === 1 && $productos[0]->nombre === 'Laptop Pro', 'ProductoService::listarProductos retorna entidades válidas');

assertThrows(
    fn() => $productoService->obtenerProductoPorId(999),
    RuntimeException::class,
    'ProductoService::obtenerProductoPorId lanza excepción si no existe'
);

$pedidoService = new PedidoService($mockPedidoRepo);
$pedido = $pedidoService->crearPedido(1, [['producto_id' => 1, 'cantidad' => 2]]);
assertTrue($pedido->id === 99 && $mockPedidoRepo->vecesLlamado === 1, 'PedidoService::crearPedido crea pedido usando mock del repositorio');

assertThrows(
    fn() => $pedidoService->crearPedido(1, []),
    InvalidArgumentException::class,
    'PedidoService::crearPedido rechaza lista de items vacía'
);

echo "\nTodas las pruebas unitarias pasaron correctamente.\n";