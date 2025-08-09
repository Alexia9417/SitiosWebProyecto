async function actualizarResumenOrdenes() {
  try {
    const res = await fetch("/Sitios/Chef/models/ordenes_num.php");
    const data = await res.json();
    if (!data.success) {
      console.error("Error al obtener resumen:", data.error);
      return;
    }

    // Inicializamos en cero
    let pendientes = 0,
      listo = 0,
      total = 0;

    data.datos.forEach((row) => {
      const estado = row.estado;
      const cnt = parseInt(row.cantidad, 10);
      if (estado === "Pendiente") pendientes = cnt;
      else if (estado === "Listo") listo = cnt;
      else if (estado === "TOTAL") total = cnt;
    });

    // Actualizamos el DOM
    document.querySelector("#card-pendientes .text-2xl").textContent =
      pendientes;
    document.querySelector("#card-listo      .text-2xl").textContent = listo;
    document.querySelector("#card-total      .text-2xl").textContent = total;
  } catch (err) {
    console.error("Error de red al actualizar resumen:", err);
  }
}

// Llama al cargar la página y cada cierto intervalo
document.addEventListener("DOMContentLoaded", () => {
  actualizarResumenOrdenes();
  setInterval(actualizarResumenOrdenes, 2000); // cada 2 segundos
});
