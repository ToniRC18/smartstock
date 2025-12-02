# SmartStock – Prototipo Hackatón

## 1. Nombre del Equipo
SmartStock Hackers (actualiza con el nombre oficial de tu equipo si cambia).

## 2. Integrantes del Equipo
- Nombre Integrante 1 — Carrera — Rol (Ej. Backend)
- Nombre Integrante 2 — Carrera — Rol (Ej. Frontend/UX)
- Nombre Integrante 3 — Carrera — Rol (Ej. Datos/Producto)
*(Reemplaza con los nombres reales de tu equipo.)*

## 3. Objetivo del Proyecto (El “Por Qué”)
Reducir acaparamiento y quiebres de stock de tarjetas corporativas conectando límites de contrato, uso real e inventario para autorizar o rechazar solicitudes con datos.

## 4. Solución Propuesta (El “Qué”)
Prototipo web que centraliza contratos, solicitudes y envíos: valida límites por producto/contrato, descuenta stock, genera tracking, muestra dashboards para cliente y admin, y proyecta stock (demo) según demanda y vencimientos.

## 5. Stack Tecnológico
- Backend: PHP 8.2, Laravel 10/11
- Frontend: Blade + Tailwind CDN
- Base de datos: SQLite (dev) / migraciones Laravel
- Semillas: CSV proporcionados (clientes, productos, contratos)

## 6. Uso de IA
No se usa IA en backend; la “proyección inteligente” de stock es una demo estática en UI. Se puede integrar un modelo (pronóstico de demanda) en futuro usando los mismos puntos de validación.

Prototipo funcional para controlar tarjetas corporativas (Combustible, Despensa, Premios) con reglas de límite por contrato, control de inactivas y stock, solicitudes y envíos con tracking. Está pensado para demo/hackatón: sin roles ni autenticación; datos cargados vía seed/CSV.

## Requisitos rápidos
- PHP 8.2+, Composer
- SQLite (archivo `database/database.sqlite`)

## Instalación y datos
```bash
composer install
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```
Semillas: carga clientes/productos/contratos desde CSV (`database/seeders/data/*.csv`) y genera solicitudes/envíos de ejemplo.

## Rutas clave
- Landing: `/`
- Dashboard empresa: `/dashboard?client_id=1` (selector de cliente en UI)
- Dashboard admin: `/admin/dashboard`
- Tracking: `/tracking/{code}` (ej. `SS-DEMO-1`)

## Funcionalidad principal
- **Contratos**: un contrato agrupa los 3 productos; cada contrato tiene allocations por producto (límite, uso, inactivas, vencidas).
- **Solicitudes/Órdenes**: motivos `expired`, `lost`, `new_employee`; validan límite total y disponible por producto (considerando inactivas). Simulación `/simular-pedido` responde aprobado/advertencia/rechazado con máximo permitido.
- **Pedidos**: creación `/crear-pedido` genera Order con tracking, fecha estimada (hoy +2), descuenta stock y suma tarjetas en uso; estados `pendiente → preparando → en_ruta → entregado` o `rechazado`.
- **Aprobación**: descuenta stock, genera envío con tracking y ETA; bloqueo si deja stock insuficiente o excede disponible.
- **Envíos**: estados `pendiente_envio`, `preparacion`, `en_ruta`, `entregado`; editable en admin; tracking visible para cliente.
- **Inventario admin**: stock actual, pendientes, proyección (stock - pendientes) y mínimos; badges de crítico y proyección baja.
- **CRUD básico admin**:
  - Crear empresa + contrato base (modal).
  - Crear contrato adicional para empresa (modal en detalle).
  - Ajustar stock y mínimo por producto (inline en inventario).
  - Filtrar solicitudes por estado/empresa; filtros se mantienen con localStorage.
- **UX**: mensajes flash se ocultan a los 1s; tabs recuerdan la última vista; warnings en formularios; botones de regreso en detalle de empresa.

## Cómo probar rápido
1) `php artisan serve`
2) Admin: `/admin/dashboard` → pestaña Empresas (agrega empresa/contrato), Solicitudes (filtros), Envíos (edita tracking), Inventario (ajusta stock).
3) Cliente: `/dashboard?client_id=1` → pestaña Solicitudes (form con disponible por producto) y Envíos; cambia de cliente con el selector.
4) Tracking: `/tracking/SS-DEMO-1`.

## Limitaciones conocidas (prototipo)
- Sin autenticación/roles.
- Tailwind via CDN (warning de producción esperado; aceptable para demo).
- Sin reservas de stock ni saldo real por tarjeta (solo conteo).
- Seeds mezclan CSV + datos sintéticos para demo.
- Sin tests automatizados.

## Próximos pasos sugeridos
- Auth y roles (admin/cliente).
- Limpieza de seeds y datos 100% alineados a CSV reales.
- Reserva de stock/pipeline y alertas automáticas.
- Logs y auditoría de aprobaciones/rechazos.
- UI: timeline en envíos, toasts, duplicar solicitudes, exportación.
