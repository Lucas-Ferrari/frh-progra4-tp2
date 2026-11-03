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

    var carrusel = document.querySelector(".carrusel");
    if (carrusel) {
        iniciarCarrusel(carrusel);
        iniciarModalPromo(document.querySelector(".promo-modal"));
    }

    var visual = document.querySelector(".portada-visual");
    if (!visual || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        return;
    }

    animarTransferencia(visual);

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

// ---------- Carrusel de descuentos: infinito y automático ----------
// La lista está dos veces: al pasar la mitad se salta atrás exactamente esa mitad,
// y como las dos mitades son iguales el salto no se ve.
function iniciarCarrusel(carrusel) {
    var mitad = function () { return carrusel.scrollWidth / 2; };
    var pausado = false;
    var reducido = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function acomodar() {
        if (carrusel.scrollLeft >= mitad()) { carrusel.scrollLeft -= mitad(); }
        if (carrusel.scrollLeft <= 0) { carrusel.scrollLeft += mitad(); }
    }

    // Se detiene con el mouse encima o con el foco adentro (teclado)
    carrusel.addEventListener("mouseenter", function () { pausado = true; });
    carrusel.addEventListener("mouseleave", function () { pausado = false; });
    carrusel.addEventListener("focusin", function () { pausado = true; });
    carrusel.addEventListener("focusout", function () { pausado = false; });
    carrusel.addEventListener("scroll", acomodar);

    // Flechas: una tarjeta por clic
    document.querySelectorAll("[data-carrusel]").forEach(function (flecha) {
        flecha.addEventListener("click", function () {
            var paso = carrusel.querySelector("li").offsetWidth + 16; // tarjeta + gap
            carrusel.scrollBy({ left: paso * Number(flecha.dataset.carrusel), behavior: reducido ? "auto" : "smooth" });
        });
    });

    // Con "reducir movimiento" no avanza solo: queda el scroll manual y las flechas
    if (reducido) {
        return;
    }
    // La posición se acumula aparte: sumarle fracciones de píxel a scrollLeft se pierden al redondear
    var anterior = null;
    var posicion = carrusel.scrollLeft;
    function avanzar(ahora) {
        if (anterior !== null && !pausado && !document.hidden) {
            if (Math.abs(carrusel.scrollLeft - posicion) > 2) {
                posicion = carrusel.scrollLeft; // lo movió el usuario (flechas, dedo, rueda)
            }
            posicion += (ahora - anterior) * 0.04; // ~40 px por segundo
            if (posicion >= mitad()) { posicion -= mitad(); }
            carrusel.scrollLeft = posicion;
        }
        anterior = ahora;
        requestAnimationFrame(avanzar);
    }
    requestAnimationFrame(avanzar);
}

// ---------- Detalle de la promo en un <dialog> ----------
function iniciarModalPromo(modal) {
    if (!modal || !modal.showModal || !window.PROMOS) {
        return;
    }
    document.querySelectorAll(".descuento").forEach(function (tarjeta) {
        tarjeta.addEventListener("click", function () {
            var promo = window.PROMOS[tarjeta.dataset.promo];
            modal.querySelectorAll("[data-campo]").forEach(function (campo) {
                if (campo.dataset.campo === "logo") {
                    campo.innerHTML = promo.logo; // armado y escapado en PHP
                } else {
                    campo.textContent = promo[campo.dataset.campo];
                }
            });
            modal.showModal();
        });
    });
    modal.querySelector(".promo-cerrar").addEventListener("click", function () { modal.close(); });
    // Clic en el fondo oscuro (fuera de la caja) también cierra
    modal.addEventListener("click", function (evento) {
        if (evento.target === modal) { modal.close(); }
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
