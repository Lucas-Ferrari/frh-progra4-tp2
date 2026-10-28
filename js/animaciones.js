// =====================================================================
// Animaciones de todo el sitio
//   Los números de las estadísticas (paneles) cuentan desde cero
//   cuando aparecen en pantalla.
//
// Es solo presentación: sin JS, o con "reducir movimiento" activado,
// se ven los valores finales que imprime PHP.
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {

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
});

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
