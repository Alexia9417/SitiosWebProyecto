document.addEventListener("DOMContentLoaded", () => {
  cargarPersonal();
});

function cargarPersonal() {
  fetch("/Sitios/Gerente/models/Gerente/mostrar_empleado.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al cargar empleados:", data.error);
        return;
      }

      // Guardar TODOS los rows (incluye mensajes) para usar al mostrar solicitudes
      const filas = data.datos;
      window._empleadosData = filas;

      // --- Crear lista de empleados UNICOS (por IdUsuario) para renderizar tarjetas ---
      const mapaUnicos = filas.reduce((acc, fila) => {
        const id = fila.IdUsuario;
        // Si aún no existe ese usuario, lo guardamos (primer aparición)
        if (!acc[id]) {
          acc[id] = {
            IdUsuario: fila.IdUsuario,
            Usuario: fila.Usuario,
            Nombre: fila.Nombre,
            Apellidos: fila.Apellidos,
            IdTipoUsuario: fila.IdTipoUsuario,
            TipoUsuario: fila.TipoUsuario,
            // puedes añadir más campos 'perfil' si los necesitas
          };
        }
        return acc;
      }, {});
      const empleadosUnicos = Object.values(mapaUnicos);

      // Filtrar sobre la lista única (ya no habrá duplicados)
      const meseros = empleadosUnicos.filter((e) => e.IdTipoUsuario === 2);
      const chefs = empleadosUnicos.filter((e) => e.IdTipoUsuario === 4);

      const contenedor = document.getElementById("contenedorPersonal");
      const btnMeseros = document.getElementById("btnMeseros");
      const btnChefs = document.getElementById("btnChefs");
      const badgeMeseros = btnMeseros.querySelector("span");
      const badgeChefs = btnChefs.querySelector("span");

      // Usar los conteos de la lista única
      badgeMeseros.textContent = meseros.length;
      badgeChefs.textContent = chefs.length;

      // Renderizar tarjetas con la lista sin duplicados
      renderListaPersonal(meseros, contenedor);

      btnMeseros.onclick = () => {
        activarBoton(btnMeseros, btnChefs);
        renderListaPersonal(meseros, contenedor);
      };
      btnChefs.onclick = () => {
        activarBoton(btnChefs, btnMeseros);
        renderListaPersonal(chefs, contenedor);
      };

      // NOTA: las solicitudes se muestran cuando haces click en el empleado (mostrarDetalleEmpleado)
    })
    .catch((err) => console.error("Fetch error:", err));
}

function activarBoton(activo, inactivo) {
  activo.classList.add(
    "border-b-2",
    "border-[#967ED5]",
    "text-[#967ED5]",
    "font-semibold"
  );
  inactivo.classList.remove(
    "border-b-2",
    "border-[#967ED5]",
    "text-[#967ED5]",
    "font-semibold"
  );
}

function renderListaPersonal(lista, contenedor) {
  contenedor.innerHTML = "";
  lista.forEach((e) => {
    const div = document.createElement("div");
    div.className =
      "flex items-center justify-between p-4 rounded-lg bg-[#BFBCE9] hover:bg-[#967ED5] text-[#000] hover:text-white cursor-pointer transition";
    div.innerHTML = `
      <div class="flex items-center gap-4">
        <div class="w-10 h-10 bg-[#4E5D83] rounded-full"></div>
        <div>
          <p class="font-medium">${e.Nombre} ${e.Apellidos}</p>
          <p class="text-xs text-green-600">En servicio</p>
        </div>
      </div>
      <div class="flex gap-1">
        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
      </div>
    `;
    div.onclick = () => mostrarDetalleEmpleado(e);
    contenedor.appendChild(div);
  });

  if (window.lucide) lucide.createIcons();
}

function mostrarDetalleEmpleado(e) {
  const detalle = document.getElementById("detalleEmpleado");
  detalle.classList.remove("hidden");

  // Cargar métricas (tu flujo anterior)
  fetch(`/Sitios/Gerente/models/Gerente/metrica_empleado.php?id=${e.IdUsuario}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al cargar métricas");
        return;
      }

      const metricas = {};
      data.datos.forEach((m) => {
        metricas[m.Metrica] = parseFloat(m.Valor);
      });

      const energia = metricas.energia ?? 0;
      const estres = metricas.estres ?? 0;
      const eficiencia = metricas.concentracion ?? 0;

      const iconoRol = e.TipoUsuario === "Mesero" ? "users" : "chef-hat";
      const badgeRol =
        e.TipoUsuario === "Mesero"
          ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">En servicio</span>'
          : '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">En cocina</span>';

      detalle.innerHTML = `
        <div class="flex items-center gap-4 pb-4 border-b border-[#BFBCE9]">
          <div class="w-16 h-16 bg-[#4E5D83] rounded-full"></div>
          <div>
            <h3 class="text-xl font-semibold">${e.Nombre} ${e.Apellidos}</h3>
            <div class="flex items-center gap-3 text-sm text-[#4E5D83]">
              <i data-lucide="${iconoRol}" class="w-4 h-4"></i><span>${e.TipoUsuario}</span>
              ${badgeRol}
            </div>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-green-50 p-4 rounded-lg flex flex-col items-center">
            <div class="flex justify-between w-full mb-2 text-green-700 font-medium">
              <span>Energía</span><i data-lucide="zap" class="w-5 h-5"></i>
            </div>
            <p class="text-3xl font-bold text-green-600">${energia}%</p>
          </div>
          <div class="bg-red-50 p-4 rounded-lg flex flex-col items-center">
            <div class="flex justify-between w-full mb-2 text-red-700 font-medium">
              <span>Estrés</span><i data-lucide="brain" class="w-5 h-5"></i>
            </div>
            <p class="text-3xl font-bold text-red-600">${estres}%</p>
          </div>
          <div class="bg-[#E9C89A] text-[#4E5D83] p-4 rounded-lg flex flex-col items-center">
            <div class="flex justify-between w-full mb-2 font-medium">
              <span>Concentración</span><i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <p class="text-3xl font-bold">${eficiencia}%</p>
          </div>
        </div>
      `;

      if (window.lucide) lucide.createIcons();

      // --- NUEVO: mostrar solo las solicitudes de ESTE empleado ---
      renderSolicitudesForEmployee(e.IdUsuario);
    });
}

/* ----------------- Mostrar solicitudes SOLO del empleado seleccionado ----------------- */

function renderSolicitudesForEmployee(idUsuario) {
  const contenedorSolicitudes = document.getElementById("solicitudesEmpleado");
  const lista = document.getElementById("listaSolicitudes");
  lista.innerHTML = "";

  // Intentar usar los datos ya cargados
  const empleados = window._empleadosData;

  const procesar = (rows) => {
    // Filtrar filas que coinciden con el usuario y que tengan IdMensaje + Estado 'Pendiente'
    const pendientes = rows.filter(
      (r) =>
        r.IdUsuario === idUsuario &&
        r.IdMensaje &&
        r.Estado &&
        r.Estado.toString().toLowerCase() === "pendiente"
    );

    if (pendientes.length === 0) {
      contenedorSolicitudes.classList.add("hidden");
      return;
    }

    contenedorSolicitudes.classList.remove("hidden");

    pendientes.forEach((p) => {
      const card = document.createElement("div");
      card.className =
        "p-4 rounded-lg bg-white shadow flex flex-col md:flex-row justify-between items-start md:items-center gap-4";

      const fecha = p.FechaHora ? new Date(p.FechaHora).toLocaleString() : "";

      card.innerHTML = `
        <div class="flex-1">
          <p class="font-semibold text-[#4E5D83]">${p.Nombre} ${
        p.Apellidos
      } <span class="text-sm text-gray-500">(${p.TipoUsuario})</span></p>
          <p class="text-sm text-gray-600">Acción: <strong>${
            p.Accion ?? "-"
          }</strong> <span class="ml-2 text-xs text-gray-400">[${
        p.Tipo ?? "-"
      }]</span></p>
          <p class="text-xs text-gray-400 mt-1">${fecha}</p>
        </div>
        <div class="flex gap-2">
          <button class="aprobar-btn px-3 py-1 rounded-md text-sm font-medium border" data-id="${
            p.IdMensaje
          }">Aprobar</button>
          <button class="rechazar-btn px-3 py-1 rounded-md text-sm font-medium border" data-id="${
            p.IdMensaje
          }">Rechazar</button>
        </div>
      `;

      const btnA = card.querySelector(".aprobar-btn");
      const btnR = card.querySelector(".rechazar-btn");

      btnA.onclick = () =>
        actualizarEstadoMensaje(p.IdMensaje, "Aprobado", card);
      btnR.onclick = () =>
        actualizarEstadoMensaje(p.IdMensaje, "Rechazado", card);

      lista.appendChild(card);
    });

    if (window.lucide) lucide.createIcons();
  };

  if (Array.isArray(empleados) && empleados.length > 0) {
    procesar(empleados);
  } else {
    // Si por alguna razón no tenemos datos guardados, pedimos al servidor
    fetch("/Sitios/Gerente/models/Gerente/mostrar_empleado.php")
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          console.error(
            "Error al cargar empleados para solicitudes:",
            data.error
          );
          contenedorSolicitudes.classList.add("hidden");
          return;
        }
        // actualizar cache local
        window._empleadosData = data.datos;
        procesar(data.datos);
      })
      .catch((err) => {
        console.error("Fetch error:", err);
        contenedorSolicitudes.classList.add("hidden");
      });
  }
}

/* ----------------- actualizarEstadoMensaje (ya existente) ----------------- */

function actualizarEstadoMensaje(idMensaje, nuevoEstado, elementoCard) {
  const botones = elementoCard.querySelectorAll("button");
  botones.forEach((b) => (b.disabled = true));

  fetch("/Sitios/Gerente/models/Gerente/actualizar_estado_mensaje.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id_mensaje: idMensaje, estado: nuevoEstado }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // quitar card del DOM
        elementoCard.remove();
        const lista = document.getElementById("listaSolicitudes");
        if (lista.children.length === 0) {
          document
            .getElementById("solicitudesEmpleado")
            .classList.add("hidden");
        }
        // Opcional: actualizar cache local para reflejar el cambio sin recargar
        if (Array.isArray(window._empleadosData)) {
          window._empleadosData = window._empleadosData.map((r) =>
            r.IdMensaje === idMensaje ? { ...r, Estado: nuevoEstado } : r
          );
        }
      } else {
        console.error("Error al actualizar estado:", data.error);
        alert("No se pudo actualizar la solicitud. Revisa la consola.");
        botones.forEach((b) => (b.disabled = false));
      }
    })
    .catch((err) => {
      console.error("Fetch error:", err);
      alert("Error de red al actualizar estado.");
      botones.forEach((b) => (b.disabled = false));
    });
}
