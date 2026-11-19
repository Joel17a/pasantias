<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Controllers\ProductoController;
use App\Controllers\PedidoController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Rutas de Productos
if (preg_match('#^/api/productos/?$#', $uri) && $method === 'GET') {
    (new ProductoController())->listar();
    exit;
}

if (preg_match('#^/api/productos/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $id = (int) $matches[1];
    (new ProductoController())->verDetalle($id);
    exit;
}

// Rutas de Pedidos
if (preg_match('#^/api/pedidos/?$#', $uri) && $method === 'POST') {
    (new PedidoController())->crear();
    exit;
}

// Ruta no encontrada
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'Ruta no encontrada o método no permitido']);