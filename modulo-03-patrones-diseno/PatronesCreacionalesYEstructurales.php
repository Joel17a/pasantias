<?php

declare(strict_types=1);

// =========================================================================
// 1. PATRÓN SINGLETON (Creacional)
// Problema que resuelve: Garantizar que una clase tenga una única instancia
// global en toda la aplicación (ej. configuración del sistema o logger).
// =========================================================================

class ConfiguracionSistema
{
    private static ?ConfiguracionSistema $instancia = null;
    private array $ajustes = [
        'entorno' => 'produccion',
        'moneda' => 'BOB',
        'impuesto_iva' => 0.13
    ];

    // Constructor privado para evitar que hagan "new ConfiguracionSistema()"
    private function __construct() {}

    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function obtener(string $clave): mixed
    {
        return $this->ajustes[$clave] ?? null;
    }

    public function establecer(string $clave, mixed $valor): void
    {
        $this->ajustes[$clave] = $valor;
    }
}

// =========================================================================
// 2. PATRÓN FACTORY METHOD (Creacional)
// Problema que resuelve: Crear objetos sin acoplar el código cliente a la
// clase concreta que se instancia (ej. notificaciones por Email, SMS o Push).
// =========================================================================

interface NotificacionInterface
{
    public function enviar(string $mensaje): string;
}

class NotificacionEmail implements NotificacionInterface
{
    public function enviar(string $mensaje): string
    {
        return "[EMAIL] Enviando correo: {$mensaje}";
    }
}

class NotificacionSms implements NotificacionInterface
{
    public function enviar(string $mensaje): string
    {
        return "[SMS] Enviando mensaje de texto: {$mensaje}";
    }
}

class NotificacionWhatsApp implements NotificacionInterface
{
    public function enviar(string $mensaje): string
    {
        return "[WHATSAPP] Enviando alerta: {$mensaje}";
    }
}

class NotificacionFactory
{
    public static function crear(string $canal): NotificacionInterface
    {
        return match (strtolower($canal)) {
            'email' => new NotificacionEmail(),
            'sms' => new NotificacionSms(),
            'whatsapp' => new NotificacionWhatsApp(),
            default => throw new InvalidArgumentException("Canal de notificación no soportado: {$canal}"),
        };
    }
}

// =========================================================================
// 3. PATRÓN BUILDER (Creacional)
// Problema que resuelve: Construir objetos complejos paso a paso evitando
// constructores gigantes con decenas de parámetros opcionales.
// =========================================================================

class CotizacionPedido
{
    public string $cliente = '';
    public array $items = [];
    public float $descuento = 0.0;
    public bool $incluyeEnvio = false;
    public string $notas = 'Sin observaciones';

    public function mostrarResumen(): string
    {
        $subtotal = array_sum($this->items);
        $total = ($subtotal - $this->descuento) + ($this->incluyeEnvio ? 25.0 : 0.0);
        return "Cotización para {$this->cliente} | Items: " . count($this->items) .
               " | Envío: " . ($this->incluyeEnvio ? 'Sí' : 'No') .
               " | Total: Bs. {$total} | Notas: {$this->notas}";
    }
}

class CotizacionBuilder
{
    private CotizacionPedido $cotizacion;

    public function __construct()
    {
        $this->cotizacion = new CotizacionPedido();
    }

    public function paraCliente(string $cliente): self
    {
        $this->cotizacion->cliente = $cliente;
        return $this;
    }

    public function agregarItem(float $precio): self
    {
        $this->cotizacion->items[] = $precio;
        return $this;
    }

    public function conDescuento(float $monto): self
    {
        $this->cotizacion->descuento = $monto;
        return $this;
    }

    public function conEnvioADomicilio(): self
    {
        $this->cotizacion->incluyeEnvio = true;
        return $this;
    }

    public function conNotas(string $notas): self
    {
        $this->cotizacion->notas = $notas;
        return $this;
    }

    public function construir(): CotizacionPedido
    {
        return $this->cotizacion;
    }
}

// =========================================================================
// 4. PATRÓN ADAPTER (Estructural)
// Problema que resuelve: Hacer compatibles dos interfaces distintas para
// integrar una librería externa sin modificar nuestro código base.
// =========================================================================

// Interfaz estándar que usa nuestra tienda en línea
interface PasarelaPagoInterface
{
    public function cobrar(float $montoDolares): string;
}

// Librería externa antigua o de terceros con métodos incompatibles
class SdkPagoExternoLegacy
{
    public function realizarTransaccionEnCentavos(int $centavos, string $divisa): string
    {
        return "[SDK Externo] Cobro procesado por {$centavos} centavos en {$divisa}";
    }
}

// Adaptador que traduce nuestra interfaz hacia el SDK externo
class PagoExternoAdapter implements PasarelaPagoInterface
{
    public function __construct(private SdkPagoExternoLegacy $sdkExterno) {}

    public function cobrar(float $montoDolares): string
    {
        $centavos = (int) round($montoDolares * 100);
        return $this->sdkExterno->realizarTransaccionEnCentavos($centavos, 'USD');
    }
}

// =========================================================================
// EJECUCIÓN Y PRUEBA DE LOS 4 PATRONES
// =========================================================================

// 1. Prueba Singleton
$config1 = ConfiguracionSistema::obtenerInstancia();
$config2 = ConfiguracionSistema::obtenerInstancia();
$config1->establecer('moneda', 'USD');
echo "1. SINGLETON -> ¿Misma instancia?: " . ($config1 === $config2 ? 'Sí' : 'No') .
     " | Moneda en config2: " . $config2->obtener('moneda') . "\n";

// 2. Prueba Factory Method
$notificador = NotificacionFactory::crear('whatsapp');
echo "2. FACTORY -> " . $notificador->enviar("Tu orden #405 está lista.") . "\n";

// 3. Prueba Builder
$cotizacion = (new CotizacionBuilder())
    ->paraCliente('Empresa Industrial SRL')
    ->agregarItem(450.0)
    ->agregarItem(300.0)
    ->conDescuento(50.0)
    ->conEnvioADomicilio()
    ->conNotas('Entregar en almacén central')
    ->construir();
echo "3. BUILDER -> " . $cotizacion->mostrarResumen() . "\n";

// 4. Prueba Adapter
$pasarela = new PagoExternoAdapter(new SdkPagoExternoLegacy());
echo "4. ADAPTER -> " . $pasarela->cobrar(49.99) . "\n";