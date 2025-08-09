let todasLasResenas = [];

document.addEventListener("DOMContentLoaded", () => {
  cargarEstadisticasResenas();
  cargarClientes();
  document
    .getElementById("filtroResenas")
    .addEventListener("click", manejarFiltro);
  setInterval(cargarEstadisticasResenas, 1000);
});

function cargarEstadisticasResenas() {
  fetch("/Sitios/Gerente/models/Gerente/calificaciones.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.datos.length) return;

      const reseñas = data.datos;
      const total = reseñas.length;

      // Conteo por categoría
      const positivas = reseñas.filter((r) => r.Estrellas >= 4).length;
      const negativas = reseñas.filter((r) => r.Estrellas <= 2).length;

      // Calcular porcentaje
      const porcentajePos = total ? Math.round((positivas / total) * 100) : 0;
      const porcentajeNeg = total ? Math.round((negativas / total) * 100) : 0;

      // Actualizar DOM
      document.getElementById("totalResenas").textContent = total;
      document.getElementById(
        "porcentajePositivas"
      ).textContent = `${porcentajePos}%`;
      document.getElementById(
        "porcentajeNegativas"
      ).textContent = `${porcentajeNeg}%`;
    })
    .catch((err) =>
      console.error("Error al obtener estadísticas de reseñas:", err)
    );
}

function cargarClientes() {
  fetch("/Sitios/Gerente/models/Gerente/calificaciones.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.datos.length) return;

      // Guardar globalmente
      todasLasResenas = data.datos.sort(
        (a, b) => new Date(b.FechaHora) - new Date(a.FechaHora)
      );
      renderResenas("todas");
    })
    .catch((err) => console.error("Error al cargar reseñas:", err));
}

function manejarFiltro(e) {
  const btn = e.target.closest(".filtro-btn");
  if (!btn) return;

  // Reset estilos
  document.querySelectorAll(".filtro-btn").forEach((b) => {
    b.classList.remove("bg-[#4E5D83]", "text-white");
    b.classList.add("bg-[#BFBCE9]", "text-[#4E5D83]");
  });

  // Activar botón seleccionado
  btn.classList.add("bg-[#4E5D83]", "text-white");
  btn.classList.remove("bg-[#BFBCE9]", "text-[#4E5D83]");

  renderResenas(btn.dataset.filtro);
}

function renderResenas(filtro) {
  const cont = document.getElementById("contenedorClientes");
  cont.innerHTML = "";

  const reseñasFiltradas = todasLasResenas
    .filter((r) => {
      const estrellas = parseInt(r.Estrellas);
      if (filtro === "positivas") return estrellas >= 4;
      if (filtro === "neutras") return estrellas === 3;
      if (filtro === "negativas") return estrellas <= 2;
      return true; // "todas"
    })
    .slice(0, 5); // solo las 3 más recientes del tipo seleccionado

  reseñasFiltradas.forEach((item) => {
    const fecha = new Date(item.FechaHora).toLocaleDateString("es-CR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });

    const inicial = item.Nombre.charAt(0).toUpperCase();
    const estrellas = parseInt(item.Estrellas);
    const estrellasHTML = Array.from({ length: 5 }, (_, i) =>
      i < estrellas ? "★" : "☆"
    ).join("");

    const comentario =
      item.Comentario.length > 120
        ? item.Comentario.slice(0, 120).trim() + "…"
        : item.Comentario;

    const tarjeta = document.createElement("div");
    tarjeta.className = "flex gap-4 items-start";
    tarjeta.innerHTML = `
      <div class="w-10 h-10 rounded-full bg-[#E9C89A] text-[#000] flex items-center justify-center font-bold">
        ${inicial}
      </div>
      <div class="flex-1">
        <div class="flex justify-between">
          <span class="font-semibold text-[#4E5D83]">${item.Nombre}</span>
          <div class="text-yellow-400">${estrellasHTML}</div>
        </div>
        <p class="text-sm text-[#4E5D83] mt-1">${comentario}</p>
        <div class="flex justify-between text-xs text-[#967ED5] mt-2">
          <span>📅 ${fecha}</span>
          <a href="#" class="text-[#4E5D83] hover:underline">Ver detalles →</a>
        </div>
      </div>
    `;
    cont.appendChild(tarjeta);
  });
}
