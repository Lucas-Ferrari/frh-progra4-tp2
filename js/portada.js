// =====================================================================
// Vida de la portada (solo index.php)
//   1. Llega una transferencia y el saldo de la tarjeta sube contando.
//   2. Con mouse, las tarjetas se inclinan siguiendo el puntero.
//
// Es decoración (la ilustración tiene aria-hidden): sin JS, o con
// "reducir movimiento" activado, la portada se ve completa con los
// valores finales que imprime el HTML.
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {

    var visual = document.querySelector(".portada-visual");
    if (!visual || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        return;
    }

    animarTransferencia(visual);
    controlarCinta(document.querySelector(".portada-cinta"));

    // En pantallas táctiles no hay puntero que seguir
    if (window.matchMedia("(pointer: fine)").matches) {
        inclinarConPuntero(visual.closest(".portada"), visual);
    }
});

// ---------- 1. Transferencia que llega ----------
function animarTransferencia(visual) {
    var valor = visual.querySelector(".muestra-saldo .valor");
    var movimiento = visual.querySelector(".muestra-saldo .movimiento");
    var acreditado = movimiento ? movimiento.querySelector(".positivo") : null;
    if (!valor || !acreditado) {
        return;
    }

    // Los números se leen del HTML (no se repiten acá): si alguien cambia
    // el saldo de ejemplo en index.php, la animación sigue siendo correcta.
    var saldoFinal = leerMonto(valor.textContent);
    var monto = leerMonto(acreditado.textContent);
    var saldoInicial = saldoFinal - monto;

    // Mismo formato que formatoMonto() en PHP: $ 245.380,50
    var formato = new Intl.NumberFormat("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    function mostrar(numero) {
        valor.textContent = "$ " + formato.format(numero);
    }

    // Estado "antes de la transferencia"
    mostrar(saldoInicial);
    movimiento.classList.add("llegando");

    // Espera a que las tarjetas terminen de entrar
    setTimeout(function () {
        movimiento.classList.remove("llegando");
        contar(saldoInicial, saldoFinal, 1300, mostrar);
    }, 1500);
}

// "$ 245.380,50" → 245380.5
function leerMonto(texto) {
    var limpio = texto.replace(/[^0-9,]/g, "").replace(",", ".");
    return parseFloat(limpio) || 0;
}

// Cuenta de "desde" a "hasta", rápido al principio y frenando al final
function contar(desde, hasta, duracion, mostrar) {
    var inicio = null;
    function paso(ahora) {
        if (inicio === null) {
            inicio = ahora;
        }
        var avance = Math.min((ahora - inicio) / duracion, 1);
        var suavizado = 1 - Math.pow(1 - avance, 3); // ease-out cúbico
        mostrar(desde + (hasta - desde) * suavizado);
        if (avance < 1) {
            requestAnimationFrame(paso);
        }
    }
    requestAnimationFrame(paso);
}

// ---------- Cinta de productos: más lenta con el mouse encima ----------
// playbackRate cambia la velocidad sin saltos (cambiar animation-duration la haría saltar)
function controlarCinta(cinta) {
    var pista = cinta && cinta.querySelector(".cinta-pista");
    if (!pista || !pista.getAnimations) {
        return;
    }
    cinta.addEventListener("mouseenter", function () {
        pista.getAnimations().forEach(function (a) { a.playbackRate = 0.3; });
    });
    cinta.addEventListener("mouseleave", function () {
        pista.getAnimations().forEach(function (a) { a.playbackRate = 1; });
    });
}

// ---------- 2. Inclinación con el puntero ----------
function inclinarConPuntero(zona, visual) {
    var ultimoEvento = null;
    var pendiente = false;

    zona.addEventListener("pointermove", function (evento) {
        ultimoEvento = evento;
        if (pendiente) {
            return;
        }
        // Una sola actualización por cuadro: mover el mouse dispara
        // muchos eventos y no hace falta procesarlos todos.
        pendiente = true;
        requestAnimationFrame(function () {
            var caja = zona.getBoundingClientRect();
            var x = (ultimoEvento.clientX - caja.left) / caja.width - 0.5;  // -0.5 a 0.5
            var y = (ultimoEvento.clientY - caja.top) / caja.height - 0.5;
            visual.style.setProperty("--inclinar-y", (x * 10).toFixed(2) + "deg");
            visual.style.setProperty("--inclinar-x", (y * -8).toFixed(2) + "deg");
            pendiente = false;
        });
    });

    // Al salir, vuelve a la posición de reposo
    zona.addEventListener("pointerleave", function () {
        visual.style.setProperty("--inclinar-y", "0deg");
        visual.style.setProperty("--inclinar-x", "0deg");
    });
}
