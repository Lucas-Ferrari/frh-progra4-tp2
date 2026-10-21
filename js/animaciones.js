// =====================================================================
// Animaciones de la interfaz (sin librerías).
// Todo se apaga si la persona pidió reducir el movimiento.
// =====================================================================

document.addEventListener("DOMContentLoaded", function () {

    var reducir = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var raiz = document.documentElement;

    // ---------- Modo claro / oscuro ----------
    var botonTema = document.querySelector(".boton-tema");

    function actualizarBotonTema() {
        var oscuro = raiz.dataset.tema === "oscuro";
        botonTema.setAttribute("aria-label", oscuro ? "Cambiar a modo claro" : "Cambiar a modo oscuro");
    }

    if (botonTema) {
        actualizarBotonTema();
        botonTema.addEventListener("click", function () {
            if (raiz.dataset.tema === "oscuro") {
                delete raiz.dataset.tema;
            } else {
                raiz.dataset.tema = "oscuro";
            }
            try { localStorage.setItem("tema", raiz.dataset.tema || "claro"); } catch (e) {}
            actualizarBotonTema();
        });
    }

    // ---------- Sombra del encabezado al hacer scroll ----------
    var encabezado = document.querySelector(".encabezado");
    if (encabezado) {
        var revisarScroll = function () {
            encabezado.classList.toggle("con-sombra", window.scrollY > 40);
        };
        window.addEventListener("scroll", revisarScroll, { passive: true });
        revisarScroll();
    }

    // ---------- Aparición de secciones al entrar en pantalla ----------
    var aparecer = document.querySelectorAll("[data-aparecer]");

    if (reducir || !("IntersectionObserver" in window)) {
        aparecer.forEach(function (el) { el.classList.add("visible"); });
    } else {
        // Los productos de una misma grilla entran escalonados.
        document.querySelectorAll(".productos").forEach(function (grilla) {
            grilla.querySelectorAll("[data-aparecer]").forEach(function (el, i) {
                el.style.setProperty("--retraso", (i % 2) * 0.1 + Math.floor(i / 2) * 0.08 + "s");
            });
        });

        var observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (entrada.isIntersecting) {
                    entrada.target.classList.add("visible");
                    observador.unobserve(entrada.target);
                    entrada.target.querySelectorAll("[data-contar]").forEach(contar);
                }
            });
        }, { threshold: 0.15 });

        aparecer.forEach(function (el) { observador.observe(el); });
    }

    // ---------- Números que cuentan hasta su valor ----------
    function contar(el) {
        if (reducir) return;
        var final = parseInt(el.dataset.contar, 10);
        var sufijo = el.dataset.sufijo || "";
        var desde = 0;
        var inicio = null;
        var duracion = 1200;

        function paso(t) {
            if (!inicio) inicio = t;
            var avance = Math.min((t - inicio) / duracion, 1);
            var suave = 1 - Math.pow(1 - avance, 3);
            el.textContent = Math.round(desde + (final - desde) * suave) + sufijo;
            if (avance < 1) requestAnimationFrame(paso);
        }
        requestAnimationFrame(paso);
    }

    // ---------- Vista previa de la portada: llega un movimiento nuevo ----------
    var lista = document.querySelector(".vista-previa ul");
    var saldo = document.querySelector("[data-saldo]");

    if (lista && saldo && !reducir) {
        var llegadas = [
            { texto: "Transferencia de Martín R.", monto: 18500 },
            { texto: "Cobro de sueldo", monto: 412000 },
            { texto: "Transferencia de Sofía P.", monto: 9200 }
        ];
        var valor = parseInt(saldo.textContent.replace(/\./g, ""), 10);
        var indice = 0;
        var formato = new Intl.NumberFormat("es-AR");

        setInterval(function () {
            if (document.hidden) return;
            var mov = llegadas[indice++ % llegadas.length];

            var li = document.createElement("li");
            li.className = "nuevo";
            li.innerHTML = "<span></span><span class=\"ingreso\"></span>";
            li.firstChild.textContent = mov.texto;
            var hora = document.createElement("small");
            hora.textContent = "Recién";
            li.firstChild.appendChild(hora);
            li.lastChild.textContent = "+ $ " + formato.format(mov.monto) + ",00";

            lista.insertBefore(li, lista.firstChild);
            if (lista.children.length > 3) lista.removeChild(lista.lastElementChild);

            // El saldo sube de a poco hasta el nuevo valor.
            var desde = valor;
            valor += mov.monto;
            var inicio = null;
            requestAnimationFrame(function paso(t) {
                if (!inicio) inicio = t;
                var avance = Math.min((t - inicio) / 900, 1);
                saldo.textContent = formato.format(Math.round(desde + (valor - desde) * (1 - Math.pow(1 - avance, 3))));
                if (avance < 1) requestAnimationFrame(paso);
            });
        }, 4500);
    }
});
