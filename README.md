# Banco Andino — Proyecto Integrador (Programación IV, 2º Parcial)

Sitio web de un banco hecho en **PHP + MySQL + HTML/CSS/JavaScript** (sin frameworks).

- Los visitantes ven la información del banco, pueden **registrarse** como clientes y enviar **consultas de contacto**.
- Los **clientes** ven sus cuentas y movimientos (con buscador), transfieren dinero y editan su email.
- Los **operadores** dan de alta clientes, buscan clientes y registran depósitos y extracciones.
- Los **administradores** tienen un panel con resumen y ABMs de cuentas, movimientos, usuarios y consultas.

---

## Requisitos

- XAMPP (o similar) con **PHP 8.0 o superior** y **MySQL 5.7+ / MariaDB 10.4+**
- Extensión `mysqli` habilitada (viene activada en XAMPP)

## Instalación

1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\` (por ejemplo `C:\xampp\htdocs\banco`).
2. Iniciar **Apache** y **MySQL** desde el panel de XAMPP.
3. Importar la base de datos: entrar a <http://localhost/phpmyadmin>, pestaña **Importar**, elegir `sql/banco.sql` y ejecutar.
   (El script crea la base `banco` desde cero, con tablas, claves foráneas y datos de prueba.)
4. Revisar los datos de conexión en `includes/conexion.php`. En XAMPP por defecto el usuario es `root` **sin contraseña** y el puerto 3306:
   ```php
   $servidor = "127.0.0.1:3306";
   $usuario = "root";
   $password = "";
   ```
5. Abrir <http://localhost/banco/>.

> El proyecto funciona en cualquier subcarpeta: los links se arman con rutas relativas (constante `RAIZ` en `includes/funciones.php`).

---

## Usuarios de prueba

Todas las contraseñas de prueba son **`123456`**. En la base están guardadas hasheadas con `password_hash()`
(el hash se generó con `sql/generar_hash.php`).

| Rol | Email | Acceso en el login | Observaciones |
|---|---|---|---|
| Administrador | `admin@banco.local` | Empleados | |
| Administrador | `admin2@banco.local` | Empleados | |
| Operador | `operador@banco.local` | Empleados | |
| Cliente | `diego@mail.com` | Clientes | 2 cuentas (ARS y USD) con varios movimientos |
| Cliente | `elena@mail.com` | Clientes | 1 cuenta |
| Cliente | `federico@mail.com` | Clientes | Cuenta corriente + caja de ahorro en USD |
| Cliente | `gabriela@mail.com` | Clientes | **Debe cambiar la contraseña** al ingresar |
| Cliente | `hernan@mail.com` | Clientes | **Dado de baja** (`activo = 0`): no puede ingresar |

En el login se elige el **tipo de acceso**: *Clientes* (rol Cliente) o *Empleados* (roles Administrador y Operador).
Si el rol no coincide con el acceso elegido, el login se rechaza.

---

## Configuración de `mail()` (formulario de contacto)

El formulario de contacto **siempre guarda la consulta en la tabla `contactos`** y además intenta avisar por mail a
`contacto@banco.local` (dirección simulada) con la función `mail()`.

En un XAMPP recién instalado no hay servidor de correo, así que `mail()` devuelve `false`: el sitio lo detecta y muestra
*"Recibimos tu consulta y quedó registrada (el aviso por mail no pudo enviarse desde este servidor)"*. La consulta igual
aparece en **Panel admin → Consultas**.

Para ver los mails en local se puede usar **Mailpit** (o MailHog), que atrapa todos los correos sin enviarlos de verdad:

1. Descargar Mailpit desde <https://github.com/axllent/mailpit/releases> y ejecutar `mailpit.exe`
   (queda escuchando SMTP en el puerto **1025** y la bandeja web en <http://localhost:8025>).
2. Editar `C:\xampp\php\php.ini`, sección `[mail function]`:
   ```ini
   SMTP = 127.0.0.1
   smtp_port = 1025
   sendmail_from = no-responder@banco.local
   ```
3. Reiniciar Apache, enviar una consulta y verla en <http://localhost:8025>.

En Linux/macOS se configura `sendmail_path` en su lugar, por ejemplo `sendmail_path = "/usr/local/bin/mailpit sendmail"`.

---

## Qué archivo cubre cada punto de la consigna

| # | Requisito | Dónde |
|---|---|---|
| 1 | Temática libre | Banco (homebanking) |
| 2 | MySQL con ≥ 3 tablas relacionadas (contactos, usuarios, contenido) | `sql/banco.sql`: `roles`, `usuarios`, `cuentas` (contenido), `movimientos`, `contactos` con claves foráneas. Diagrama: `sql/diagrama_relaciones.svg` |
| 3 | ≥ 2 niveles de usuario, usuarios de prueba, contraseñas encriptadas | 3 roles (Administrador, Operador, Cliente). `password_hash()` en `procesos/procesar_registro.php`, `procesar_cambio_password.php`, `includes/funciones.php` (`crearUsuarioConProvisoria`, reset); `password_verify()` en `procesos/validar_login.php` |
| 4 | Index con nombre, menú, descripción, registro y login | `index.php`, `includes/header.php` (menú), `registro.php`, `login.php` |
| 5 | Contacto con mapa, formulario, `mail()` y guardado en la base | `contacto.php` (iframe de Google Maps), `procesos/procesar_contacto.php` (`INSERT` + `mail()`) |
| 6 | Login con sesión; si falla redirige con mensaje | `procesos/validar_login.php`, `includes/auth.php` (`requerirLogin`, `requerirRol`, `requerirPasswordActualizada`), `logout.php`, mensajes en `includes/funciones.php` (`mostrarMensajes`) |
| 7 | Usuario general ve contenido de la base | `cliente/panel.php` (cuentas), `cliente/movimientos.php`, `cliente/transferir.php`, `cliente/mis_datos.php` |
| 8 | Administrador ve contenido + ABM | `admin/panel.php` y ABMs: `admin/cuentas.php` + `cuenta_form.php` + `procesos/procesar_cuenta.php`; `admin/movimientos.php` + `movimiento_form.php` + `procesos/procesar_movimiento.php`; `admin/usuarios.php` + `usuario_form.php` + `procesos/procesar_usuario.php`; `admin/contactos.php` + `procesos/procesar_contacto_admin.php` |
| 9 | Buscador sobre la base | Movimientos por descripción y fechas (`cliente/movimientos.php`, `admin/movimientos.php`), usuarios por apellido/DNI/email (`admin/usuarios.php`), cuentas por número/titular (`admin/cuentas.php`), consultas (`admin/contactos.php`) |
| — | HTML + CSS + JavaScript | `css/estilos.css` (responsive, modo claro/oscuro), `js/validaciones.js` (DNI, contraseñas, montos, `confirm()` en bajas, menú mobile) |

Extra: rol **Operador** (`operador/panel.php`, `operador/alta_cliente.php`, `procesos/procesar_cliente.php`).

---

## Estructura

```
├── index.php, contacto.php, registro.php, login.php, logout.php, cambiar_password.php
├── includes/   conexion.php, auth.php, funciones.php, header.php, footer.php
├── procesos/   validar_login.php y procesar_*.php (reciben los formularios y redirigen)
├── admin/      panel y ABMs (cuentas, movimientos, usuarios, consultas)
├── cliente/    panel, movimientos, transferencias, mis datos
├── operador/   panel y alta de clientes
├── css/estilos.css
├── js/validaciones.js
└── sql/        banco.sql, generar_hash.php, diagrama_relaciones.svg
```

## Decisiones de diseño y seguridad

- **Sentencias preparadas** en todas las consultas (`mysqli_prepare` + `bind_param`, helpers `consultar()` / `ejecutar()`).
- **Escape de salida** con `e()` (`htmlspecialchars`) en todo lo que se imprime: un nombre como `<script>alert(1)</script>` se muestra como texto.
- **Login**: un único mensaje genérico ante email o contraseña incorrectos, `session_regenerate_id(true)` al ingresar, cierre de sesión por inactividad (30 min).
- **Control de acceso** en cada página interna y en cada script de `procesos/` (no solo en los formularios).
- **Contraseñas provisorias** con `random_int()`; el usuario queda con `debe_cambiar_password = 1` y no puede usar el sitio hasta cambiarla.
- **Saldos**: el monto de cada movimiento se guarda con signo y el saldo siempre es la suma de los movimientos. Depósitos, extracciones, transferencias y correcciones se hacen dentro de **transacciones** con `SELECT ... FOR UPDATE`. Ninguna operación puede dejar saldo negativo.
- **Bajas**: usuarios con baja lógica (`activo = 0`; un admin no puede darse de baja a sí mismo). Cuentas: si tienen movimientos, baja lógica (`activa = 0`); si no, se borran. Solo se puede dar de baja una cuenta con saldo 0. Las transferencias no se pueden editar ni borrar porque afectan a dos cuentas.
- La cuenta del cliente se verifica contra la sesión: cambiar `id_cuenta` en la URL no permite ver cuentas ajenas.

## Pruebas realizadas

Se probó el flujo completo con cada rol y las pruebas de seguridad del plan (acceso sin sesión, acceso con rol incorrecto,
cuenta ajena por URL, `' OR '1'='1` en el login, XSS en el nombre), además de altas/bajas/modificaciones en cada ABM,
validaciones de formularios, consistencia de saldos y la vista mobile (menú desplegable y tablas con scroll horizontal).
