// =====================================================================
// Interfaz de todo el sitio
//   1. Botón de modo claro / oscuro (recuerda la elección).
//   2. Sombra del header cuando la página se desplaza.
//
// Es solo presentación: sin JS se ve el tema del sistema.
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {
    iniciarBotonTema(document.querySelector(".boton-tema"));
    iniciarSombraEncabezado(document.querySelector(".encabezado"));
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

