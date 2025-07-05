document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("cotizacionForm");

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add("was-validated");
            return;
        }

        const formData = new FormData(form);
        const jsonData = {};
        formData.forEach((value, key) => {
            if (jsonData[key]) {
                if (!Array.isArray(jsonData[key])) {
                    jsonData[key] = [jsonData[key]];
                }
                jsonData[key].push(value);
            } else {
                jsonData[key] = value;
            }
        });

        document.getElementById("spinnerSection").style.display = "block";
        fetch("/api/catering/estimate", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(jsonData)
        })
            .then(res => res.json())
            .then(data => {
                document.getElementById("spinnerSection").style.display = "none";
                document.getElementById("resultadoTexto").innerText = data.mensaje;
                document.getElementById("resultadoSection").style.display = "block";
                form.reset();
                form.classList.remove("was-validated");
            })
            .catch(err => {
                console.error("Error en la solicitud:", err);
            });
    });
});