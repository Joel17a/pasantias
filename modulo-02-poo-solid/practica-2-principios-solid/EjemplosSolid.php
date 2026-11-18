<?php

declare(strict_types=1);

// =========================================================================
// 1. S - SINGLE RESPONSIBILITY PRINCIPLE (SRP)
// "Cada clase debe tener una única razón para cambiar."
// =========================================================================

// ❌ ROMPE SRP: Valida datos de la pieza, calcula el peso y genera el PDF/texto.
class PiezaFundidaMala
{
    public function __construct(public string $codigo, public float $pesoKg) {}

    public function validar(): bool
    {
        return $this->pesoKg > 0;
    }

    public function imprimirEtiqueta(): string
    {
        return "ETIQUETA: Pieza {$this->codigo} - {$this->pesoKg} kg";
    }
}

// ✅ CUMPLE SRP: Se separan los datos/validación de la presentación.
class PiezaFundida
{
    public function __construct(public string $codigo, public float $pesoKg) {}

    public function esValida(): bool
    {
        return $this->pesoKg > 0;
    }
}

class ImpresoraEtiquetaPieza
{
    public function generarTexto(PiezaFundida $pieza): string
    {
        return "ETIQUETA: Pieza {$pieza->codigo} - {$pieza->pesoKg} kg";
    }
}

// =========================================================================
// 2. O - OPEN/CLOSED PRINCIPLE (OCP)
// "Abierto para extensión, cerrado para modificación."
// =========================================================================

// ❌ ROMPE OCP: Cada vez que se agrega un nuevo metal, hay que editar este método.
class CalculadoraCostoMetalMala
{
    public function calcular(string $tipoMetal, float $kilos): float
    {
        if ($tipoMetal === 'BRONCE') {
            return $kilos * 12.5;
        } elseif ($tipoMetal === 'ALUMINIO') {
            return $kilos * 6.0;
        }
        return 0.0;
    }
}

// ✅ CUMPLE OCP: Nuevos metales se agregan creando nuevas clases sin tocar la calculadora.
interface TarifaMetalInterface
{
    public function calcularCosto(float $kilos): float;
}

class TarifaBronce implements TarifaMetalInterface
{
    public function calcularCosto(float $kilos): float { return $kilos * 12.5; }
}

class TarifaAluminio implements TarifaMetalInterface
{
    public function calcularCosto(float $kilos): float { return $kilos * 6.0; }
}

class CalculadoraCostoMetal
{
    public function calcular(TarifaMetalInterface $metal, float $kilos): float
    {
        return $metal->calcularCosto($kilos);
    }
}

// =========================================================================
// 3. L - LISKOV SUBSTITUTION PRINCIPLE (LSP)
// "Una subclase debe poder reemplazar a su clase padre sin romper el programa."
// =========================================================================

// ❌ ROMPE LSP: Una pieza de muestra no tiene precio, pero hereda un método que lanza error.
class ProductoBaseMalo
{
    public function obtenerPrecio(): float { return 50.0; }
}

class MuestraGratisMala extends ProductoBaseMalo
{
    public function obtenerPrecio(): float
    {
        throw new RuntimeException("Las muestras no tienen precio"); // Rompe el contrato esperado
    }
}

// ✅ CUMPLE LSP: Separamos lo que es cotizable de lo que es solo catálogo físico.
interface ItemCatalogoInterface
{
    public function obtenerNombre(): string;
}

interface CotizableInterface extends ItemCatalogoInterface
{
    public function obtenerPrecio(): float;
}

class PiezaComercial implements CotizableInterface
{
    public function obtenerNombre(): string { return "Buje de Bronce"; }
    public function obtenerPrecio(): float { return 50.0; }
}

class MuestraGratis implements ItemCatalogoInterface
{
    public function obtenerNombre(): string { return "Muestra de Aleación"; }
}

// =========================================================================
// 4. I - INTERFACE SEGREGATION PRINCIPLE (ISP)
// "Interfaces pequeñas y específicas; no obligar a implementar métodos que no se usan."
// =========================================================================

// ❌ ROMPE ISP: Un operario de horno no debería implementar métodos de facturación.
interface TrabajadorSistemaMalo
{
    public function registrarColada(): void;
    public function emitirFactura(): void;
}

// ✅ CUMPLE ISP: Se segregan las capacidades por rol.
interface OperarioProduccionInterface
{
    public function registrarColada(): void;
}

interface AdministradorFacturacionInterface
{
    public function emitirFactura(): void;
}

class Fundidor implements OperarioProduccionInterface
{
    public function registrarColada(): void
    {
        echo "[Producción] Colada registrada correctamente.\n";
    }
}

// =========================================================================
// 5. D - DEPENDENCY INVERSION PRINCIPLE (DIP)
// "Depender de abstracciones (interfaces), no de implementaciones concretas."
// =========================================================================

// ❌ ROMPE DIP: El reporte crea internamente su propia conexión a MySQL.
class ReporteProduccionMalo
{
    public function generar(): void
    {
        // Acoplado fuertemente a MySQL
        echo "[MySQL Directo] Leyendo datos...\n";
    }
}

// ✅ CUMPLE DIP: El reporte recibe una abstracción de fuente de datos por constructor.
interface FuenteDatosProduccionInterface
{
    public function obtenerResumen(): string;
}

class MySqlFuenteDatos implements FuenteDatosProduccionInterface
{
    public function obtenerResumen(): string { return "120 piezas fundidas hoy (MySQL)"; }
}

class ReporteProduccion
{
    public function __construct(private FuenteDatosProduccionInterface $fuente) {}

    public function generar(): void
    {
        echo "[Reporte] " . $this->fuente->obtenerResumen() . "\n";
    }
}

// =========================================================================
// EJECUCIÓN DE VERIFICACIÓN
// =========================================================================
$pieza = new PiezaFundida("PZ-01", 14.5);
echo (new ImpresoraEtiquetaPieza())->generarTexto($pieza) . "\n";

$calc = new CalculadoraCostoMetal();
echo "Costo Bronce (10kg): $" . $calc->calcular(new TarifaBronce(), 10) . "\n";

(new Fundidor())->registrarColada();
(new ReporteProduccion(new MySqlFuenteDatos()))->generar();