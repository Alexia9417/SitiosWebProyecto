document.addEventListener("DOMContentLoaded", () => {
  // Llamadas iniciales
  cargarDatosGenerales();
  cargarCalificaciones();
  cargarResena();

  // Refrescar cada 1 segundo
  setInterval(() => {
    cargarDatosGenerales();
    cargarCalificaciones();
    cargarResena();
  }, 1000);
});

/** Datos generales de meseros/chefs y KPIs */
function cargarDatosGenerales() {
  fetch("/Sitios/Gerente/models/Gerente/datos_general.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.datos.length) return;
      const info = data.datos[0];
      document.getElementById("totalMeseros").textContent = info.Mesero || 0;
      document.getElementById("totalChefs").textContent = info.Chef || 0;
      document.getElementById("kpiEnergia").textContent = `${Math.round(
        info.PromedioEnergia
      )}%`;
      document.getElementById("kpiEstres").textContent = `${Math.round(
        info.PromedioEstres
      )}%`;
      document.getElementById("kpiEficiencia").textContent = `${Math.round(
        info.PromedioConcentracion
      )}%`;
      if (window.lucide) lucide.createIcons();
    })
    .catch((err) => console.error("Error al obtener datos generales:", err));
}

/** Promedio de calificaciones y total comentarios */
function cargarCalificaciones() {
  fetch("/Sitios/Gerente/models/Gerente/promedio_calificaciones.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.datos.length) return;
      const info = data.datos[0];
      // actualizar HTML
      document.getElementById("promedioEstrellas").textContent = parseFloat(
        info.PromedioEstrellas
      ).toFixed(2);
      document.getElementById("totalResenasHoy").textContent =
        info.TotalComentarios;
      if (window.lucide) lucide.createIcons();
    })
    .catch((err) =>
      console.error("Error al obtener promedio de calificaciones:", err)
    );
}

/** Última reseña */
function cargarResena() {
  fetch("/Sitios/Gerente/models/Gerente/calificaciones.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success || !data.datos.length) return;
      // ordenar y tomar la más reciente
      const ultima = data.datos
        .sort((a, b) => new Date(b.FechaHora) - new Date(a.FechaHora))
        .shift();
      const contenedor = document.getElementById("contenedorResena");
      const hora = new Date(ultima.FechaHora).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
      const stars = parseInt(ultima.Estrellas);
      const starsHTML = Array.from(
        { length: 5 },
        (_, i) =>
          `<i data-lucide="star" class="w-4 h-4 ${
            i < stars ? "fill-current text-yellow-500" : "text-yellow-500"
          }"></i>`
      ).join("");
      contenedor.innerHTML = `
        <div class="bg-[#BFBCE9] p-4 rounded-lg">
          <div class="flex items-center text-sm text-[#4E5D83] mb-2">
            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
            <span>Última reseña</span>
            <span class="ml-auto font-medium">${hora}</span>
          </div>
          <p class="font-medium text-black">${ultima.Nombre}</p>
          <div class="flex space-x-1 mt-1">${starsHTML}</div>
          <p class="text-sm mt-2 text-[#4E5D83]">${ultima.Comentario}</p>
        </div>
      `;
      if (window.lucide) lucide.createIcons();
    })
    .catch((err) => console.error("Error al cargar reseñas:", err));
}
