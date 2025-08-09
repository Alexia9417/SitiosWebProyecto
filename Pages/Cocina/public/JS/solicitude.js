function iniciarContador(tsFinAccion, solicitudId, tipoAccion) {
  const fin = new Date(tsFinAccion).getTime();
  const span = document.getElementById("contador-overlay");
  const overlay = document.getElementById("bloqueo-overlay");
  const gif = document.getElementById("gif-accion");
  const mensaje = document.getElementById("mensaje-accion");

  // Diccionario de acciones → gif y mensaje
  const accionesConfig = {
    "Tomar agua": {
      gif: "https://i.makeagif.com/media/8-01-2019/56KeVI.gif",
      mensaje: "Tomando agua...",
    },
    "Comer algo": {
      gif: "https://media3.giphy.com/media/v1.Y2lkPTc5MGI3NjExZTlrbnZtZjU1d3hqcjl0b25qYmxjZ21yYzNqemJucnF2eDNydWpkYSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/l0MYwONBGDS7aPGOk/giphy.gif",
      mensaje: "Comiendo algo...",
    },
    "Descanso corto": {
      gif: "https://media.tenor.com/6RBtqpZc3iMAAAAM/break-pizza.gif",
      mensaje: "En un descanso...",
    },
    "Estiramiento físico": {
      gif: "https://media.tenor.com/6RBtqpZc3iMAAAAM/break-pizza.gif",
      mensaje: "Estirando músculos...",
    },
    "Respirar profundo": {
      gif: "https://media.tenor.com/6RBtqpZc3iMAAAAM/break-pizza.gif",
      mensaje: "Respirando profundamente...",
    },
  };

  // Si existe config para esa acción, aplicarla
  if (accionesConfig[tipoAccion]) {
    gif.src = accionesConfig[tipoAccion].gif;
    mensaje.textContent = accionesConfig[tipoAccion].mensaje;
  } else {
    gif.src = "/Sitios/Chef/public/images/procesando.gif";
    mensaje.textContent = "Procesando acción...";
  }

  overlay.classList.remove("hidden");
  document
    .querySelectorAll("[data-micromodal-close]")
    .forEach((btn) => btn.setAttribute("disabled", true));

  const intervalo = setInterval(() => {
    const diff = fin - Date.now();
    if (diff <= 0) {
      clearInterval(intervalo);
      span.innerText = "00:00";

      fetch("/Sitios/Chef/models/completar_solicitud.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ solicitud_id: solicitudId }),
      })
        .then((r) => r.json())
        .then((r) => {
          overlay.classList.add("hidden");
          document
            .querySelectorAll("[data-micromodal-close]")
            .forEach((btn) => btn.removeAttribute("disabled"));
          MicroModal.close("modal-solicitud");

          if (r.success) {
            alert("Solicitud completada y KPIs actualizados.");
            cargarSolicitudes();
          } else {
            alert("Error en backend: " + (r.error || "desconocido"));
          }
        })
        .catch((err) => {
          console.error("Error al llamar a completar_solicitud.php:", err);
          alert("Error de red");
        });
      return;
    }

    const m = String(Math.floor(diff / 60000)).padStart(2, "0");
    const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, "0");
    span.innerText = `${m}:${s}`;
  }, 1000);
}

let solicitudesData = [];
let solicitudSeleccionada = null; // 🔄 Variable global

function cargarSolicitudes() {
  fetch("/Sitios/Chef/models/mostrar_solicitudes.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error cargando solicitudes:", data.error);
        return;
      }
      // 🔍 Filtrar solo las acciones de tipo 'rapido'
      const accionesRapidas = data.datos.filter(
        (item) => item.Tipo === "rapido"
      );
      solicitudesData = accionesRapidas;

      const contenedor = document.getElementById("contenedor-solicitudes");
      contenedor.innerHTML = "";

      const seccion = document.createElement("section");
      seccion.className = "bg-[#324064] rounded-xl shadow p-6 space-y-4";
      seccion.innerHTML = `
        <div class="flex justify-between items-center">
          <h2 class="flex items-center gap-2 text-lg font-bold text-white">
            <i data-lucide="hand-heart" class="w-5 h-5 text-[#967ED5]"></i> Acciones Rápidas
          </h2>
        </div>
        <div class="text-sm font-semibold text-white flex items-center gap-2">
          <i data-lucide="user" class="w-4 h-4 text-[#967ED5]"></i> PERSONAL
        </div>
        <div id="grid-solicitudes" class="grid grid-cols-2 sm:grid-cols-2 gap-3"></div>
      `;
      contenedor.appendChild(seccion);
      const grid = seccion.querySelector("#grid-solicitudes");
      accionesRapidas.forEach((item) => {
        const card = document.createElement("a");
        card.href = "#";
        card.dataset.id = item.IdAccion;
        card.className =
          "solicitud-btn flex flex-col items-center justify-center bg-[#BFBCE9] text-[#4E5D83] p-4 rounded-lg text-sm font-semibold hover:bg-[#E9C89A] transition";
        // Iconos personalizados
        let icon = "coffee";
        if (item.Nombre.includes("Tomar agua")) icon = "droplet";
        else if (item.Nombre.includes("Snack") || item.Nombre.includes("Comer"))
          icon = "utensils-crossed";
        else if (item.Nombre.includes("Descanso")) icon = "bed-double";
        else if (item.Nombre.includes("Estiramiento")) icon = "move-diagonal";
        else if (item.Nombre.includes("Respirar")) icon = "wind";
        card.innerHTML = `
          <i data-lucide="${icon}" class="w-5 h-5 mb-1"></i>
          ${item.Nombre}
        `;
        grid.appendChild(card);
      });
      if (window.lucide) lucide.createIcons();
    });
}

function iniciarSolicitudRapida(chefId, nombre) {
  fetch("/Sitios/Chef/models/iniciar_solicitud.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ chef_id: chefId, nombre: nombre }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        alert("Error al iniciar solicitud: " + data.error);
        return;
      }
      // 1) Cerrar inmediatamente el modal
      MicroModal.close("modal-solicitud");

      // Ahora pedimos el detalle con ts_fin_accion y solicitud_id
      fetch(
        `/Sitios/Chef/models/tiempo_solicitud.php?chef_id=${chefId}&accion=${encodeURIComponent(
          nombre
        )}`
      )
        .then((res) => res.json())
        .then((detail) => {
          if (!detail.success || !detail.datos.length) {
            console.error("No se encontró detalle de la solicitud");
            return;
          }
          const sol = detail.datos[0];
          // sol debe tener { solicitud_id, ts_fin_accion, … }
          iniciarContador(sol.ts_fin_accion, sol.solicitud_id);
        })
        .catch((err) => console.error("Error al obtener tiempo:", err));
    })
    .catch((err) => {
      console.error("Error en iniciar solicitud:", err);
      alert("Error de red al iniciar solicitud");
    });
}

// Manejo de clics en solicitudes
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".solicitud-btn");
  if (btn) {
    e.preventDefault();
    const id = btn.dataset.id;
    const solicitud = solicitudesData.find((s) => s.IdAccion == id);
    if (!solicitud) return;

    solicitudSeleccionada = solicitud; // ✅ Guardamos la seleccionada

    const html = `
      <p class="text-base">⏳ <strong>Duración:</strong> ${
        solicitud.Duracion
      } segundos</p>
      <ul class="space-y-1 text-sm">
        <li>💚 <strong>Energía:</strong> ${formatearImpacto(
          solicitud.Energia
        )}</li>
        <li>😤 <strong>Estres:</strong> ${formatearImpacto(
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
  }

  // Escucha de clic en botón de "Iniciar Solicitud"
  if (e.target && e.target.id === "btn-iniciar-solicitud") {
    if (!solicitudSeleccionada) {
      alert("Error: No se ha seleccionado una solicitud.");
      return;
    }
    //close modal
    iniciarSolicitudRapida(1, solicitudSeleccionada.Nombre);
  }
});

// Utilidad
function formatearImpacto(valor) {
  const clase =
    valor > 0 ? "text-green-600" : valor < 0 ? "text-red-600" : "text-gray-500";
  const signo = valor > 0 ? "+" : "";
  return `<span class="${clase} font-semibold">${signo}${valor}</span>`;
}

// Inicial
cargarSolicitudes();
