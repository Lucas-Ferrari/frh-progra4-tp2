// =====================================================================
// Interfaz de todo el sitio
//   1. Botón de modo claro / oscuro (recuerda la elección).
//   2. Sombra del header cuando la página se desplaza.
//   3. Los números de las estadísticas (paneles) cuentan desde cero
//      cuando aparecen en pantalla.
//
// Es solo presentación: sin JS se ve el tema del sistema y los valores
// finales que imprime PHP.
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {
    iniciarBotonTema(document.querySelector(".boton-tema"));
    iniciarSombraEncabezado(document.querySelector(".encabezado"));
    iniciarContadores();
});

// ---------- 1. Modo claro / oscuro ----------
// data-tema="oscuro" | "claro" en <html> pisa la preferencia del sistema.
// El header lo aplica antes de pintar (script en el <head>) para que no parpadee.
function iniciarBotonTema(boton) {
    if (!boton) {
        return;
    }
    var raiz = document.documentElement;
    var sistemaOscuro = window.matchMedia("(prefers-color-scheme: dark)");

    function temaActual() {
        return raiz.dataset.tema || (sistemaOscuro.matches ? "oscuro" : "claro");
    }

    function actualizarEtiqueta() {
        boton.setAttribute("aria-label", temaActual() === "oscuro" ? "Cambiar a modo claro" : "Cambiar a modo oscuro");
    }

    boton.addEventListener("click", function () {
        raiz.dataset.tema = temaActual() === "oscuro" ? "claro" : "oscuro";
        try {
            localStorage.setItem("tema", raiz.dataset.tema);
        } catch (e) {
            // Navegación privada o almacenamiento bloqueado: el cambio vale solo para esta página
        }
        actualizarEtiqueta();
    });

    actualizarEtiqueta();
}

// ---------- 2. Sombra del header al bajar ----------
function iniciarSombraEncabezado(encabezado) {
    if (!encabezado) {
        return;
    }
    function revisar() {
        encabezado.classList.toggle("con-sombra", window.scrollY > 40);
    }
    // passive: el navegador no espera a este código para desplazar la página
    window.addEventListener("scroll", revisar, { passive: true });
    revisar();
}

// ---------- 3. Contadores de las estadísticas ----------
function iniciarContadores() {

    var valores = document.querySelectorAll(".estadistica .valor");
    var sinMovimiento = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (valores.length === 0 || sinMovimiento || !("IntersectionObserver" in window)) {
        return;
    }

    // Cada número arranca a contar recién cuando entra en pantalla (una sola vez)
    var observador = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                contarHasta(entrada.target);
                observador.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.4 });

    valores.forEach(function (valor) {
        observador.observe(valor);
    });
}

// Lee "128", "$ 1.234.567,89" o "US$ 500,00" y lo anima respetando su formato
function contarHasta(elemento) {
    var texto = elemento.textContent.trim();
    var primerDigito = texto.search(/[0-9]/);
    if (primerDigito === -1) {
        return;
    }

    var prefijo = texto.slice(0, primerDigito);      // "$ ", "US$ " o ""
    var conDecimales = texto.indexOf(",") !== -1;     // montos: 2 decimales
    var final = parseFloat(texto.slice(primerDigito).replace(/\./g, "").replace(",", ".")) || 0;

    var formato = new Intl.NumberFormat("es-AR", {
        minimumFractionDigits: conDecimales ? 2 : 0,
        maximumFractionDigits: conDecimales ? 2 : 0
    });

    var duracion = 1100;
    var inicio = null;

    function paso(ahora) {
        if (inicio === null) {
            inicio = ahora;
        }
        var avance = Math.min((ahora - inicio) / duracion, 1);
        var suavizado = 1 - Math.pow(1 - avance, 3); // rápido al principio, frena al final
        elemento.textContent = prefijo + formato.format(final * suavizado);
        if (avance < 1) {
            requestAnimationFrame(paso);
        } else {
            elemento.textContent = texto; // termina exactamente en el texto original
        }
    }
    requestAnimationFrame(paso);
}
