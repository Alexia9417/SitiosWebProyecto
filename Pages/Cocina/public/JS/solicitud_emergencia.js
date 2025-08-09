let solicitudesEmergenciaData = [];
let solicitudEmergenciaSeleccionada = null;

function cargarSolicitudesEmergencia() {
  fetch("/Sitios/Chef/models/mostrar_solicitudes.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return console.error(data.error);

      const emergencias = data.datos.filter(
        (item) => item.Tipo === "emergencia"
      );
      solicitudesEmergenciaData = emergencias;

      const contenedor = document.getElementById("contenedor-emergencias");
      contenedor.innerHTML = "";

      const seccion = document.createElement("section");
      seccion.className = "bg-[#324064] rounded-xl shadow p-6 space-y-4";
      seccion.innerHTML = `
        <h2 class="text-lg font-bold text-white mb-4">Acciones de Emergencia</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="grid-emergencias"></div>
      `;
      contenedor.appendChild(seccion);

      const grid = seccion.querySelector("#grid-emergencias");

      emergencias.forEach((item) => {
        let icon = "alert-circle";
        if (item.Nombre.toLowerCase().includes("descanso")) icon = "coffee";
        else if (item.Nombre.toLowerCase().includes("salida")) icon = "log-out";
        else if (item.Nombre.toLowerCase().includes("asistencia"))
          icon = "phone-call";
        else if (item.Nombre.toLowerCase().includes("ayudante")) icon = "users";

        // Guardamos tanto el id de la acción como el nombre en data-*
        const card = document.createElement("a");
        card.href = "#";
        card.dataset.id = item.IdAccion; // Id de la acción (solicitud)
        card.dataset.nombre = item.Nombre; // Nombre de la acción
        card.className =
          "emergencia-btn bg-[#BFBCE9] hover:bg-[#E9C89A] transition rounded-lg p-4 text-center space-y-1 cursor-pointer";
        card.innerHTML = `
          <i data-lucide="${icon}" class="w-6 h-6 mx-auto text-[#4E5D83]"></i>
          <h3 class="font-semibold text-[#4E5D83]">${item.Nombre}</h3>
        `;
        grid.appendChild(card);
      });

      lucide.createIcons();
    })
    .catch((err) => {
      console.error("Error al cargar emergencias:", err);
    });
}

document.addEventListener("click", function (e) {
  // Click sobre una tarjeta de emergencia
  const btn = e.target.closest(".emergencia-btn");
  if (btn) {
    e.preventDefault();
    const id = btn.dataset.id;
    const solicitud = solicitudesEmergenciaData.find(
      (s) => String(s.IdAccion) === String(id)
    );
    if (!solicitud) return;

    // Guardamos la solicitud
    solicitudEmergenciaSeleccionada = solicitud;

    // 🔹 Verificamos estado
    if (solicitud.Estado === "Activo") {
      // Mostrar modal con detalles
      const html = `
        <p class="text-base">⏳ <strong>Duración:</strong> ${
          solicitud.Duracion
        } segundos</p>
        <ul class="space-y-1 text-sm">
          <li>💚 <strong>Energía:</strong> ${formatearImpacto(
            solicitud.Energia
          )}</li>
          <li>😤 <strong>Estrés:</strong> ${formatearImpacto(
            solicitud.Estres
          )}</li>
          <li>🎯 <strong>Concentración:</strong> ${formatearImpacto(
            solicitud.Concentracion
          )}</li>
        </ul>
      `;
      document.getElementById("modal-title").innerText = solicitud.Nombre;
      document.getElementById("modal-content").innerHTML = html;
      MicroModal.show("modal-solicitud");
    } else {
      // 🔹 Si está inactivo → enviar al gerente directamente
      fetch("/Sitios/Chef/models/solicitar_gerente.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          id_empleado: 1, // tu ID de empleado real
          id_accion_chef: solicitud.IdAccion,
          estado: "Pendiente",
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert(`Solicitud enviada al gerente: ${solicitud.Nombre}`);
          } else {
            alert("Error al enviar solicitud al gerente: " + data.error);
          }
        })
        .catch(() => alert("Error de red al enviar solicitud al gerente"));
    }
  }

  // Botón ejecutar acción (solo si modal está abierto)
  if (e.target && e.target.id === "btn-iniciar-solicitud") {
    if (!solicitudEmergenciaSeleccionada)
      return alert("Seleccione una emergencia.");
    iniciarSolicitudEmergencia(
      1,
      solicitudEmergenciaSeleccionada.IdAccion,
      solicitudEmergenciaSeleccionada.Nombre
    );
  }
});

function iniciarSolicitudEmergencia(chefId, solicitudId, nombre) {
  // Deshabilitar el botón para evitar doble envío (opcional)
  const btn = document.getElementById("btn-iniciar-solicitud");
  if (btn) btn.setAttribute("disabled", "disabled");

  // Enviamos también solicitud_id para que el backend pueda registrar cuál acción se inició
  fetch("/Sitios/Chef/models/iniciar_solicitud.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      chef_id: chefId,
      nombre: nombre,
      solicitud_id: solicitudId,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        if (btn) btn.removeAttribute("disabled");
        return alert("Error: " + (data.error || "respuesta inválida"));
      }

      // Cerrar modal existente (modal-solicitud)
      MicroModal.close("modal-solicitud");

      // Obtener el tiempo/ts fin de la solicitud. Si tu backend soporta buscar por solicitud_id sería ideal:
      // fetch(`/Sitios/Chef/models/tiempo_solicitud.php?chef_id=${chefId}&solicitud_id=${solicitudId}`)
      // pero si actualmente usa 'accion' (nombre), dejamos la llamada como antes:
      fetch(
        `/Sitios/Chef/models/tiempo_solicitud.php?chef_id=${chefId}&accion=${encodeURIComponent(
          nombre
        )}`
      )
        .then((res) => res.json())
        .then((detail) => {
          if (!detail.success || !detail.datos.length) {
            console.error(
              "No hay detalle de tiempo para la solicitud iniciada"
            );
            // reactivar botón y recargar lista para mantener UI consistente
            if (btn) btn.removeAttribute("disabled");
            cargarSolicitudesEmergencia();
            return;
          }
          const sol = detail.datos[0];
          // Iniciar contador con ts_fin y solicitud_id. pasar nombre para seleccionar gif.
          iniciarContador(sol.ts_fin_accion, sol.solicitud_id, nombre);

          // reactivar botón y recargar lista (opcional: recarga cuando sea necesario)
          if (btn) btn.removeAttribute("disabled");
          cargarSolicitudesEmergencia();
        })
        .catch((err) => {
          console.error("Error al obtener tiempo:", err);
          if (btn) btn.removeAttribute("disabled");
          cargarSolicitudesEmergencia();
        });
    })
    .catch((err) => {
      console.error("Error al iniciar solicitud:", err);
      if (btn) btn.removeAttribute("disabled");
      alert("Error de red al iniciar solicitud");
    });
}

// Utilidad que ya tenías
function formatearImpacto(valor) {
  const clase =
    valor > 0 ? "text-green-600" : valor < 0 ? "text-red-600" : "text-gray-500";
  const signo = valor > 0 ? "+" : "";
  return `<span class="${clase} font-semibold">${signo}${valor}</span>`;
}

// Inicial
cargarSolicitudesEmergencia();
