# Banco — Plan de tareas para el 2º Parcial (Proyecto Integrador)

Objetivo: llevar el proyecto actual (login con roles + alta de clientes) a una app funcional que cumpla los 9 puntos de la consigna.

Leyenda de la columna "Consigna": número del punto del enunciado que cubre la tarea.

---

## Fase 0 — Preparación

- [x] **0.1** Crear estructura de carpetas:
  ```
  banco/
  ├── index.php
  ├── contacto.php
  ├── registro.php
  ├── login.php
  ├── logout.php
  ├── includes/      (conexion.php, auth.php, header.php, footer.php, funciones.php)
  ├── procesos/      (validar_login.php, procesar_*.php)
  ├── admin/         (panel + ABMs)
  ├── cliente/       (panel + movimientos)
  ├── operador/      (panel + alta de clientes)
  ├── css/estilos.css
  ├── js/validaciones.js
  └── sql/banco.sql
  ```
- [x] **0.2** Inicializar git y hacer un primer commit con el estado actual.
- [x] **0.3** Borrar archivos de prueba antes de entregar: `listar_usuarios_ejemplo.php`, `ver_sesion.php`.
- [x] **0.4** Renombrar `validar_loguin.php` → `validar_login.php` (y actualizar el `action` del form).

---

## Fase 1 — Base de datos (Consigna 2 y 3)

- [x] **1.1** Crear `sql/banco.sql` con `CREATE DATABASE`, tablas, claves foráneas y datos de prueba.
- [x] **1.2** Tabla `roles`
  - `id_rol` PK, `nombre` (Administrador, Operador, Cliente)
- [x] **1.3** Tabla `usuarios`
  - `id_usuario` PK, `nombre`, `apellido`, `dni` UNIQUE, `fecha_nacimiento`, `email` UNIQUE, `password VARCHAR(255)` (hash), `debe_cambiar_password TINYINT DEFAULT 0`, `activo TINYINT DEFAULT 1`, `fecha_registro`, `id_rol` FK → `roles`
- [x] **1.4** Tabla `cuentas` (contenido principal)
  - `id_cuenta` PK, `numero_cuenta` UNIQUE, `tipo` (Caja de ahorro / Cuenta corriente), `moneda` (ARS / USD), `saldo DECIMAL(12,2)`, `fecha_apertura`, `id_usuario` FK → `usuarios`
- [x] **1.5** Tabla `movimientos`
  - `id_movimiento` PK, `id_cuenta` FK → `cuentas`, `fecha`, `tipo` (Depósito / Extracción / Transferencia), `monto`, `descripcion`
- [x] **1.6** Tabla `contactos`
  - `id_contacto` PK, `nombre`, `email`, `asunto`, `mensaje`, `fecha`, `leido TINYINT DEFAULT 0`, `id_usuario` FK NULL → `usuarios` (si la consulta la hizo alguien logueado)
- [x] **1.7** Datos de prueba:
  - 2 administradores, 1 operador, 4+ clientes
  - 1–2 cuentas por cliente, varios movimientos por cuenta
  - 2–3 consultas de contacto
- [x] **1.8** Contraseñas de prueba **hasheadas**: generarlas con un script `sql/generar_hash.php` (`echo password_hash("1234", PASSWORD_DEFAULT);`) y pegar el resultado en el INSERT. Documentar las contraseñas en claro en el README para el profesor.
- [x] **1.9** Exportar el diagrama de relaciones para la entrega → `sql/diagrama_relaciones.svg`.

---

## Fase 2 — Infraestructura común y seguridad (Consigna 3 y 6)

- [x] **2.1** `includes/conexion.php`: dejar un solo punto de conexión, usar `require_once` en todos lados.
- [x] **2.2** `includes/auth.php` con funciones:
  - `requerirLogin()` → si no hay sesión, redirige a `login.php?error=sesion`
  - `requerirRol(array $roles)` → si el rol no está permitido, redirige con error
  - `requerirPasswordActualizada()` → si `debe_cambiar_password = 1`, fuerza `cambiar_password.php`
- [x] **2.3** Constantes de roles en `includes/funciones.php`: `ROL_ADMIN = 1`, `ROL_OPERADOR = 2`, `ROL_CLIENTE = 3`.
- [x] **2.4** Helper `e($texto)` = `htmlspecialchars(...)` para escapar todo lo que se imprime.
- [x] **2.5** Pasar **todas** las consultas a sentencias preparadas (`mysqli_prepare` + `bind_param`):
  - `validar_login.php`, `procesar_cliente.php`, `procesar_cambio_password.php`, y todo lo nuevo.
- [x] **2.6** Contraseñas encriptadas:
  - Alta / registro / cambio de contraseña → `password_hash()`
  - Login y verificación de contraseña actual → `password_verify()`
- [x] **2.7** Contraseña provisoria con `random_int()` en vez de `rand()`.
- [x] **2.8** `session_regenerate_id(true)` al loguearse.
- [x] **2.9** `logout.php`: `session_unset()`, `session_destroy()`, redirigir a `index.php`.

---

## Fase 3 — Layout, CSS y JavaScript (evaluación de HTML/CSS/JS)

- [x] **3.1** `includes/header.php`: `<head>`, link a CSS, logo/nombre del banco y **menú de navegación**:
  - Sin sesión: Inicio · Contacto · Registrarse · Ingresar
  - Con sesión: Inicio · Mi panel · Contacto · Cambiar contraseña · Salir (+ nombre del usuario)
- [x] **3.2** `includes/footer.php`: datos del banco, año, link a contacto.
- [x] **3.3** `css/estilos.css`: paleta, tipografía, menú responsive (flexbox), formularios, tablas, botones, mensajes de éxito/error, vista mobile.
- [x] **3.4** `js/validaciones.js`:
  - Registro / cambio de contraseña: que las contraseñas coincidan y tengan longitud mínima
  - DNI solo numérico (7–8 dígitos)
  - `confirm()` antes de cualquier **baja**
  - Validar que el monto de un movimiento sea > 0
- [x] **3.5** Mensajes de error/éxito con parámetros en la URL (`?error=...`, `?ok=...`) mostrados en una caja estilizada, en vez de `die()` / `echo` sueltos.

---

## Fase 4 — Página principal y registro (Consigna 4)

- [x] **4.1** `index.php` con header/footer comunes, que contenga:
  - Nombre del proyecto
  - Menú de navegación
  - Descripción del banco (quiénes somos, productos: cuentas, tarjetas, etc.)
  - Botón **"Registrarse"** (usuarios generales)
  - Botón **"Ingresar"** (acceso a contenido protegido)
- [x] **4.2** `registro.php`: formulario público (nombre, apellido, DNI, fecha de nacimiento, email, contraseña, repetir contraseña).
- [x] **4.3** `procesos/procesar_registro.php`:
  - Validar campos del lado del servidor
  - Verificar que email y DNI no existan
  - Insertar con `id_rol = ROL_CLIENTE` y contraseña hasheada
  - Opcional: crear automáticamente una caja de ahorro en $0 para el nuevo cliente
  - Redirigir a `login.php?ok=registro`

---

## Fase 5 — Contacto (Consigna 5)

- [x] **5.1** `contacto.php`:
  - Mapa de Google embebido (`<iframe>` de Google Maps con la dirección de la sucursal)
  - Datos de contacto (dirección, teléfono, horario)
  - Formulario: nombre, email, asunto, mensaje
- [x] **5.2** `procesos/procesar_contacto.php`:
  - Validar campos
  - `INSERT` en `contactos` (con `id_usuario` si hay sesión)
  - `mail("contacto@banco.local", $asunto, $cuerpo, $headers)` a dirección simulada
  - Redirigir a `contacto.php?ok=1` (o `?error=...`)
- [x] **5.3** Documentar en el README cómo probar `mail()` en local (ej. configurar `sendmail_path` / Mailpit / MailHog en XAMPP), y que el registro en la base funciona aunque el mail no salga.

---

## Fase 6 — Login y sesiones (Consigna 6)

- [x] **6.1** `login.php`: mostrar mensajes según `?error=` (credenciales, sin permisos, sesión expirada) y `?ok=`.
- [x] **6.2** `validar_login.php`:
  - Si falla → `header("Location: login.php?error=credenciales")` con **un único mensaje genérico** (no revelar si el email existe)
  - Si el rol no corresponde al tipo de acceso → `login.php?error=permisos`
  - Si el usuario está inactivo → `login.php?error=inactivo`
  - Si `tipo` no viene o es inválido → no saltear la validación de rol
  - Si `debe_cambiar_password = 1` → `cambiar_password.php`
  - Si todo bien → panel según rol
- [x] **6.3** `procesar_cambio_password.php`: hash + verify, y al terminar redirigir al panel correspondiente con `?ok=password`.
- [x] **6.4** Todas las páginas internas llaman a `requerirLogin()` / `requerirRol()` / `requerirPasswordActualizada()`.

---

## Fase 7 — Panel del usuario General / Cliente (Consigna 7)

- [x] **7.1** `cliente/panel.php`: listado de **sus cuentas** traído de la base (número, tipo, moneda, saldo).
- [x] **7.2** `cliente/movimientos.php?id_cuenta=X`: movimientos de una cuenta.
  - Verificar que la cuenta pertenezca al usuario logueado (no confiar en el id de la URL).
- [x] **7.3** **Buscador** de movimientos por descripción y rango de fechas (Consigna 9).
- [x] **7.4** Operación propia del cliente (opcional, suma puntos): transferencia entre cuentas propias o a otro CBU/número de cuenta, que inserta movimientos y actualiza saldos dentro de una **transacción** (`mysqli_begin_transaction`).
- [x] **7.5** Ver y editar sus datos personales (email) — "M" sobre su propio usuario.

---

## Fase 8 — Panel del Administrador con ABMs (Consigna 8)

- [x] **8.1** `admin/panel.php`: resumen (cantidad de clientes, cuentas, saldo total, consultas sin leer) + menú a cada ABM.
- [x] **8.2** **ABM de Cuentas** (contenido principal):
  - Listado con datos del titular (JOIN con `usuarios`)
  - Alta: elegir cliente, tipo, moneda, saldo inicial
  - Modificación: tipo, moneda
  - Baja: con `confirm()` en JS; definir si es física o lógica (no borrar cuentas con movimientos → baja lógica)
- [x] **8.3** **ABM de Movimientos**:
  - Alta de depósito / extracción sobre una cuenta (actualiza saldo, en transacción)
  - Validar que una extracción no deje saldo negativo
  - Modificación / baja de movimientos (recalcular saldo)
- [x] **8.4** **ABM de Usuarios**:
  - Listado de todos los usuarios con su rol
  - Alta (cualquier rol) con contraseña provisoria y `debe_cambiar_password = 1`
  - Modificación de datos y rol
  - Baja lógica (`activo = 0`), impedir que un admin se borre a sí mismo
  - Resetear contraseña (genera nueva provisoria)
- [x] **8.5** **Gestión de Contactos**:
  - Listado de consultas, marcar como leída, eliminar
- [x] **8.6** **Buscador** de usuarios/clientes por apellido, DNI o email, y de cuentas por número (Consigna 9).

---

## Fase 9 — Panel del Operador (rol extra, opcional pero ya existe)

- [x] **9.1** `operador/panel.php` con menú.
- [x] **9.2** Mover `operador_cargar_cliente.php` → `operador/alta_cliente.php`, protegido con `requerirRol([ROL_ADMIN, ROL_OPERADOR])`.
- [x] **9.3** `procesar_cliente.php`: quitar el `include` duplicado, insertar con `debe_cambiar_password = 1`, validar duplicados de DNI/email.
- [x] **9.4** Permitir al operador registrar depósitos/extracciones y buscar clientes (reutilizando las páginas del admin con permisos acotados).

---

## Fase 10 — Pruebas y entrega

- [x] **10.1** Probar el flujo completo con cada rol:
  - Registro → login → panel cliente → buscar movimientos → logout
  - Login admin → alta/modificación/baja en cada ABM → buscador
  - Operador da de alta cliente → cliente entra con provisoria → cambio obligatorio
  - Formulario de contacto → registro en tabla → mail
- [x] **10.2** Pruebas de seguridad básicas:
  - Entrar a `admin/panel.php` siendo cliente → debe redirigir con error
  - Entrar a páginas internas sin sesión → redirige a login
  - Ver movimientos de una cuenta ajena cambiando el id en la URL → debe bloquear
  - Probar `' OR '1'='1` en el login → no debe entrar
  - Cargar `<script>alert(1)</script>` como nombre → debe mostrarse como texto
- [x] **10.3** Revisar en celular (responsive).
- [x] **10.4** `README.md` con:
  - Descripción del proyecto
  - Requisitos (XAMPP/PHP/MySQL) y pasos de instalación (importar `sql/banco.sql`, ajustar `conexion.php`)
  - Usuarios de prueba con sus contraseñas en claro
  - Configuración de `mail()`
  - Mapa de qué archivo cubre cada punto de la consigna
- [x] **10.5** Comprimir y entregar (código + `.sql` + README) → `banco-entrega.zip` (generado con `git archive`; falta subirlo al campus).

---

## Checklist final contra la consigna

| # | Requisito | Tareas |
|---|---|---|
| 1 | Temática libre | Banco ✅ |
| 2 | MySQL, ≥3 tablas (contactos, usuarios, contenido) relacionadas | Fase 1 |
| 3 | ≥2 niveles de usuario, usuarios de prueba, contraseñas encriptadas | 1.7, 1.8, 2.6 |
| 4 | Index con nombre, menú, descripción, registro y login | Fase 3, Fase 4 |
| 5 | Contacto con mapa, formulario, `mail()` y guardado en la base | Fase 5 |
| 6 | Login con sesión; error → redirige con mensaje | Fase 2, Fase 6 |
| 7 | Usuario general ve contenido de la base | Fase 7 |
| 8 | Admin ve contenido + ABM (más ABMs suman) | Fase 8 |
| 9 | Buscador sobre la base | 7.3, 8.6 |
| — | HTML + CSS + JavaScript | Fase 3 |
