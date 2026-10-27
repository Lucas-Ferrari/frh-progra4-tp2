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
            botonMenu.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú");
        });
    }

    // ---------- Aparición al hacer scroll ----------
    // Solo se ocultan los bloques que están debajo de la pantalla al cargar,
    // así nada de lo que ya se ve "parpadea".
    var animar = "IntersectionObserver" in window &&
        !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (animar) {
        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    entrada.target.classList.add("visible");
                    observador.unobserve(entrada.target);
                }
            });
        }, { rootMargin: "0px 0px -10% 0px" });

        var bloques = document.querySelectorAll(
            ".contenido > *:not(.grilla):not(.menu-panel):not(.mensaje), .grilla > *, .menu-panel > *, .portada-visual > *"
        );

        bloques.forEach(function (bloque) {
            var enPantalla = bloque.getBoundingClientRect().top < window.innerHeight * 0.9;
            // La portada se anima siempre: es el primer momento de la página.
            if (enPantalla && !bloque.closest(".portada")) {
                return;
            }
            var hermanos = bloque.parentElement.children;
            var indice = Array.prototype.indexOf.call(hermanos, bloque);
            if (bloque.parentElement.matches(".grilla, .menu-panel, .portada-visual")) {
                bloque.style.setProperty("--retraso", (indice * 0.09) + "s");
            }
            bloque.classList.add("revelar");
            observador.observe(bloque);
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
