function cargarKpis(chefId = 1) {
  fetch(`/Sitios/Chef/models/kpis_datos.php?chef_id=${chefId}`)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error:", data.error);
        return;
      }
      // Mostrar nombre del chef en algún elemento con ID "chef-nombre"
      const nombreChef = data.chef?.nombre_completo ?? "Sin nombre";
      const spanChef = document.getElementById("chef-nombre");
      if (spanChef) {
        spanChef.textContent = nombreChef;
      }

      const contenedor = document.getElementById("contenedor-kpis");
      contenedor.innerHTML = ""; // limpia

      for (const [kpi, metrica] of Object.entries(data.metricas)) {
        const div = document.createElement("div");
        div.className =
          "bg-[#F5DDD3] text-gray-900 p-4 rounded-xl shadow space-y-2";

        div.innerHTML = `
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2 font-medium">
              ${
                kpi === "estres"
                  ? "<i data-lucide='alert-triangle'></i> Estrés"
                  : kpi === "energia"
                  ? "<i data-lucide='battery-medium'></i> Energía"
                  : kpi === "concentracion"
                  ? "<i data-lucide='target'></i> Concentración"
                  : "<i data-lucide='star'></i> Calidad"
              }
            </div>
            <span class="${
              metrica.estado === "Normal" ? "text-yellow-600" : "text-green-600"
            } font-semibold text-sm">
              ${metrica.estado}
            </span>
          </div>
          <div class="text-2xl font-bold">${metrica.Valor}%</div>
          <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
            <div class="h-full bg-[#967ED5]" style="width: ${
              metrica.Valor
            }%"></div>
          </div>
          <div class="text-xs flex justify-between text-gray-600">
            <span>${
              kpi === "estres"
                ? "Nivel de estrés del chef"
                : kpi === "energia"
                ? "Nivel de energía disponible"
                : kpi === "concentracion"
                ? "Capacidad de concentración"
                : "Calidad del trabajo realizado"
            }</span>
            <span>${
              metrica.max ? `Máx: ${metrica.max}%` : `Mín: ${metrica.min}%`
            }</span>
          </div>
        `;

        contenedor.appendChild(div);
      }

      lucide.createIcons(); // Actualiza íconos si usas Lucide
    });
}

// Actualiza cada 1 segundo
setInterval(() => cargarKpis(1), 1000);
