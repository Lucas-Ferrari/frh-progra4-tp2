// Simulador del plazo fijo (cliente/plazos_fijos.php).
// Muestra cuánto se cobra y cuándo, mientras la persona completa el formulario.
// Es solo una ayuda: el servidor vuelve a calcular el interés al constituirlo.

document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector(".simulador-plazo");
    if (!form) {
        return;
    }

    var tasas = JSON.parse(form.dataset.tna);   // {"ARS": 32, "USD": 2}
    var cuenta = form.querySelector("#id_cuenta");
    var monto = form.querySelector("#monto");
    var salida = {};
    form.querySelectorAll("[data-sim]").forEach(function (el) {
        salida[el.dataset.sim] = el;
    });

    function formatoMonto(valor, moneda) {
        var simbolo = moneda === "USD" ? "US$ " : "$ ";
        return simbolo + valor.toLocaleString("es-AR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function actualizar() {
        var moneda = cuenta.options[cuenta.selectedIndex].dataset.moneda;
        var tna = tasas[moneda];
        var capital = parseFloat(String(monto.value).replace(",", "."));
        var dias = parseInt((form.querySelector("input[name=dias]:checked") || {}).value, 10);

        salida.tna.textContent = tna.toLocaleString("es-AR", { minimumFractionDigits: 2 }) + "%";

        if (!(capital > 0) || !dias) {
            salida.total.textContent = "—";
            salida.interes.textContent = "—";
            salida.vencimiento.textContent = "—";
            return;
        }

        // Misma fórmula que el servidor: capital × TNA × días / 36500, a centavos.
        var interes = Math.round(capital * tna * dias / 365) / 100;
        var vence = new Date();
        vence.setDate(vence.getDate() + dias);

        salida.total.textContent = formatoMonto(capital + interes, moneda);
        salida.interes.textContent = "+ " + formatoMonto(interes, moneda);
        salida.vencimiento.textContent = vence.toLocaleDateString("es-AR", { day: "2-digit", month: "2-digit", year: "numeric" });
    }

    form.addEventListener("input", actualizar);
    form.addEventListener("change", actualizar);
    actualizar();
});
