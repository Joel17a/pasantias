# Arquitectura de Sistema: Acortador de URLs

## Componentes principales
- **Cliente (Web / Móvil / API)** -> **Balanceador de carga (Load Balancer)** -> **Instancias stateless de API / Servicio de URLs**
- **Almacenamiento principal**: Base de datos relacional o NoSQL (`short_code`, `long_url`, `created_at`, `expires_at`).
- **Caché (Redis)**: Almacena pares `short_code -> long_url` para lecturas de baja latencia[cite: 7].
- **Cola de mensajes / Eventos**: Recibe eventos de clic de forma asíncrona para analítica sin frenar la redirección[cite: 7, 8].

## Flujo de escritura (`POST /api/shorten`)
1. El cliente envía una URL larga.
2. El servicio valida la URL y genera un código corto único (p. ej., codificación Base62).
3. Persiste el registro en base de datos y opcionalmente guarda en caché[cite: 7].
4. Devuelve la URL corta generada.

## Flujo de lectura (`GET /{short_code}`)
1. El cliente solicita el enlace corto.
2. Se consulta primero **Redis**; si existe (*cache hit*), responde redirección (`301` o `302`) de inmediato[cite: 7].
3. Si no está en caché (*cache miss*), lee de base de datos, actualiza Redis y redirige.
4. En segundo plano, publica el evento de clic hacia la cola para métricas.