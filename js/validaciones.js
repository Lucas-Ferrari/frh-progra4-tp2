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

    // ---------- Toasts: se cierran con la X; los de éxito, solos a los 5 s ----------
    document.querySelectorAll(".toast").forEach(function (toast) {
        function cerrar() {
            toast.classList.add("saliendo");
            setTimeout(function () { toast.remove(); }, 300);
        }
        toast.querySelector(".toast-cerrar").addEventListener("click", cerrar);
        if (toast.classList.contains("mensaje-ok")) setTimeout(cerrar, 5000);
    });

    // ---------- Aparición al hacer scroll ----------
    // Solo se ocultan los bloques que están debajo de la pantalla al cargar,
    // así nada de lo que ya se ve "parpadea".
    var animar = "IntersectionObserver" in window &&
        !window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (animar) {
        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    var bloque = entrada.target;
                    bloque.classList.add("visible");
                    observador.unobserve(bloque);
                    // Terminada la entrada se quitan las clases: así el bloque y sus
                    // partes recuperan sus propias transiciones (hover de botones y tarjetas)
                    setTimeout(function () {
                        bloque.classList.remove("revelar", "visible");
                    }, 1600);
                }
            });
            // Entra cuando el bloque ya está bien adentro de la pantalla, no apenas asoma el borde:
            // si no, la animación termina antes de que se lo llegue a ver
        }, { rootMargin: "0px 0px -20% 0px", threshold: 0.15 });

        var bloques = document.querySelectorAll(
            ".contenido > *:not(.grilla):not(.menu-panel):not(.mensaje):not(.portada), .grilla > *, .menu-panel > *, .portada-visual > *"
        );

        bloques.forEach(function (bloque) {
            var enPantalla = bloque.getBoundingClientRect().top < window.innerHeight * 0.9;
            // La portada se anima siempre: es el primer momento de la página.
            if (enPantalla && !bloque.closest(".portada")) {
                return;
            }
            // Si el bloque ya tiene su propia animación de entrada (CSS), no se lo anima dos veces
            if (getComputedStyle(bloque).animationName !== "none") {
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
                // Lleva el foco al primer campo con error: el usuario no tiene que buscarlo
                var primerError = form.querySelector('[aria-invalid="true"]');
                if (primerError) {
                    primerError.focus();
                }
                return;
            }

            // Confirmación antes de cualquier baja
            var mensajeConfirmacion = form.getAttribute("data-confirmar");
            if (mensajeConfirmacion && !confirm(mensajeConfirmacion)) {
                evento.preventDefault();
                return;
            }

            // Envío confirmado: se bloquea el botón para evitar un doble envío.
            // En una transferencia, un doble clic podría duplicar la operación.
            // Solo en POST: los buscadores (GET) no modifican datos.
            if (form.method.toLowerCase() === "post") {
                var boton = form.querySelector('button[type="submit"]');
                if (boton) {
                    boton.setAttribute("data-texto-original", boton.textContent);
                    boton.disabled = true;
                    boton.textContent = "Procesando…";
                }
            }
        });
    });

    // Si el usuario vuelve con el botón "Atrás", el navegador puede restaurar
    // la página desde caché con el botón todavía bloqueado: se lo reactiva.
    window.addEventListener("pageshow", function (evento) {
        if (!evento.persisted) {
            return;
        }
        document.querySelectorAll("button[data-texto-original]").forEach(function (boton) {
            boton.disabled = false;
            boton.textContent = boton.getAttribute("data-texto-original");
        });
    });

    // ---------- Ayuda en vivo mientras se escribe ----------
    // No reemplaza la validación al enviar: solo muestra el avance
    // para que el usuario no tenga que adivinar si cumple o no.

    // Contraseña: la ayuda "Mínimo N caracteres" se pone en verde al cumplirse
    document.querySelectorAll("[data-password-min]").forEach(function (campo) {
        var ayuda = campo.parentNode.querySelector(".ayuda");
        if (!ayuda) {
            return;
        }
        var minimo = parseInt(campo.getAttribute("data-password-min"), 10);
        // La ayuda pasa a describir el campo: el lector de pantalla la lee al enfocarlo
        if (campo.id) {
            ayuda.id = campo.id + "-ayuda";
            agregarDescripcion(campo, ayuda.id);
        }
        campo.addEventListener("input", function () {
            ayuda.classList.toggle("cumple", campo.value.length >= minimo);
        });
    });

    // Repetir contraseña: avisa en cuanto las dos coinciden
    document.querySelectorAll("[data-igual-a]").forEach(function (campo) {
        var otro = document.getElementById(campo.getAttribute("data-igual-a"));
        if (!otro) {
            return;
        }
        var aviso = document.createElement("span");
        aviso.className = "ayuda coincide";
        aviso.setAttribute("aria-live", "polite"); // se anuncia cuando aparece el texto
        campo.parentNode.appendChild(aviso);

        function comparar() {
            var coinciden = campo.value !== "" && campo.value === otro.value;
            aviso.textContent = coinciden ? "Las contraseñas coinciden." : "";
            aviso.classList.toggle("cumple", coinciden);
        }
        campo.addEventListener("input", comparar);
        otro.addEventListener("input", comparar);
    });

    // Textos largos: contador de caracteres contra el maxlength del HTML
    document.querySelectorAll("textarea[maxlength]").forEach(function (area) {
        var maximo = parseInt(area.getAttribute("maxlength"), 10);
        var contador = document.createElement("span");
        contador.className = "ayuda contador";
        area.parentNode.appendChild(contador);
        if (area.id) {
            contador.id = area.id + "-contador";
            agregarDescripcion(area, contador.id);
        }

        function actualizar() {
            contador.textContent = area.value.length + " / " + maximo + " caracteres";
            // Aviso visual cuando falta poco para el límite
            contador.classList.toggle("cerca", area.value.length > maximo * 0.9);
        }
        area.addEventListener("input", actualizar);
        actualizar();
    });

    // aria-describedby admite varios ids separados por espacio:
    // estas dos funciones agregan o quitan uno sin borrar los demás.
    function agregarDescripcion(campo, id) {
        var ids = (campo.getAttribute("aria-describedby") || "").split(" ").filter(Boolean);
        if (ids.indexOf(id) === -1) {
            ids.push(id);
        }
        campo.setAttribute("aria-describedby", ids.join(" "));
    }

    function quitarDescripcion(campo, id) {
        var ids = (campo.getAttribute("aria-describedby") || "").split(" ").filter(function (otro) {
            return otro && otro !== id;
        });
        if (ids.length) {
            campo.setAttribute("aria-describedby", ids.join(" "));
        } else {
            campo.removeAttribute("aria-describedby");
        }
    }

    function mostrarError(campo, texto) {
        var contenedor = campo.closest(".campo") || campo.parentNode;
        contenedor.classList.add("campo-error");

        var error = document.createElement("span");
        error.className = "error-campo";
        // aria-live: el lector de pantalla anuncia el error apenas aparece
        error.setAttribute("aria-live", "polite");
        error.textContent = texto;
        contenedor.appendChild(error);

        // Se vincula el error con su campo para que se lea al enfocarlo
        campo.setAttribute("aria-invalid", "true");
        if (campo.id) {
            error.id = campo.id + "-error";
            agregarDescripcion(campo, error.id);
        }
    }

    function limpiarErrores(form) {
        form.querySelectorAll(".error-campo").forEach(function (el) { el.remove(); });
        form.querySelectorAll(".campo-error").forEach(function (el) { el.classList.remove("campo-error"); });
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (campo) {
            campo.removeAttribute("aria-invalid");
            quitarDescripcion(campo, campo.id + "-error");
        });
    }
});
