// =====================================================================
// Validaciones del lado del cliente.
// Son una ayuda para el usuario: el servidor vuelve a validar todo.
//
// Se activan con atributos en el HTML:
//   data-dni                 → el campo debe tener 7 u 8 dígitos
//   data-password-min="6"    → longitud mínima de una contraseña
//   data-igual-a="idCampo"   → debe coincidir con otro campo (repetir contraseña)
//   data-monto               → número mayor a 0
//   data-confirmar="texto"   → (en un <form>) pide confirm() antes de enviar (bajas)
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {

    // ---------- Menú responsive ----------
    var botonMenu = document.querySelector(".menu-boton");
    var menu = document.getElementById("menu-principal");

    if (botonMenu && menu) {
        botonMenu.addEventListener("click", function () {
            var abierto = menu.classList.toggle("abierto");
            botonMenu.setAttribute("aria-expanded", abierto ? "true" : "false");
        });
    }

    // ---------- Validación de formularios ----------
    var formularios = document.querySelectorAll("form");

    formularios.forEach(function (form) {
        form.addEventListener("submit", function (evento) {

            limpiarErrores(form);
            var valido = true;

            form.querySelectorAll("[data-dni]").forEach(function (campo) {
                if (!/^[0-9]{7,8}$/.test(campo.value.trim())) {
                    mostrarError(campo, "El DNI debe tener 7 u 8 dígitos, sin puntos.");
                    valido = false;
                }
            });

            form.querySelectorAll("[data-password-min]").forEach(function (campo) {
                var minimo = parseInt(campo.getAttribute("data-password-min"), 10);
                if (campo.value.length < minimo) {
                    mostrarError(campo, "La contraseña debe tener al menos " + minimo + " caracteres.");
                    valido = false;
                }
            });

            form.querySelectorAll("[data-igual-a]").forEach(function (campo) {
                var otro = document.getElementById(campo.getAttribute("data-igual-a"));
                if (otro && campo.value !== otro.value) {
                    mostrarError(campo, "Las contraseñas no coinciden.");
                    valido = false;
                }
            });

            form.querySelectorAll("[data-monto]").forEach(function (campo) {
                var monto = parseFloat(campo.value.replace(",", "."));
                if (isNaN(monto) || monto <= 0) {
                    mostrarError(campo, "El monto debe ser mayor a 0.");
                    valido = false;
                }
            });

            if (!valido) {
                evento.preventDefault();
                return;
            }

            // Confirmación antes de cualquier baja
            var mensajeConfirmacion = form.getAttribute("data-confirmar");
            if (mensajeConfirmacion && !confirm(mensajeConfirmacion)) {
                evento.preventDefault();
            }
        });
    });

    function mostrarError(campo, texto) {
        var contenedor = campo.closest(".campo") || campo.parentNode;
        contenedor.classList.add("campo-error");

        var error = document.createElement("span");
        error.className = "error-campo";
        error.textContent = texto;
        contenedor.appendChild(error);
    }

    function limpiarErrores(form) {
        form.querySelectorAll(".error-campo").forEach(function (el) { el.remove(); });
        form.querySelectorAll(".campo-error").forEach(function (el) { el.classList.remove("campo-error"); });
    }
});
