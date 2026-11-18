<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Conexion
{
    private static ?PDO $instancia = null;

    public static function obtenerInstancia(): PDO
    {
        if (self::$instancia === null) {
            $host = '127.0.0.1';
            $db   = 'pasantias_tienda';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instancia = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
                exit;
            }
        }

        self::$instancia; // Asegura retorno válido
        return self::$instancia;
    }
}