function cargarOrdenes() {
  fetch("models/detalle_orden.php")
    .then((res) => res.json())
    .then((ordenes) => {
      const lista = document.getElementById("listaOrdenes");
      lista.innerHTML = "";

      ordenes.forEach((orden) => {
        const tr = document.createElement("tr");
        tr.className = "border-t even:bg-white hover:bg-[#BFBCE9] transition";
        tr.innerHTML = `
          <td class="px-4 py-3 font-semibold">#${orden.NOrden}</td>
          <td class="px-4 py-3">${orden.NMesa}</td>
          <td class="px-4 py-3">${orden.Llego}</td>
          <td class="px-4 py-3">${orden.Estado}</td>
          <td class="px-4 py-3">${orden.TiempoTotal}</td>
          <td class="px-4 py-3 space-x-2 whitespace-nowrap">
            ${
              orden.Estado === "Pendiente"
                ? `<button class="btn-tomar bg-[#967ED5] text-white px-3 py-1.5 rounded-md hover:bg-[#BFBCE9] text-sm inline-flex items-center"
                    data-id="${orden.NOrden}">
                    <i data-lucide='play' class='w-4 h-4 mr-1'></i> Tomar
                  </button>`
                : ""
            }
            <button class="btn-detalle text-sm text-blue-400 underline" data-id="${
              orden.NOrden
            }">
              Ver detalles
            </button>
          </td>
        `;
        lista.appendChild(tr);
      });

      lucide.createIcons();

      // Botones "Ver detalles"
      document.querySelectorAll(".btn-detalle").forEach((btn) => {
        btn.addEventListener("click", () => {
          mostrarDetalleOrden(btn.dataset.id);
        });
      });

      // Botones "Tomar"
      document.querySelectorAll(".btn-tomar").forEach((btn) => {
        btn.addEventListener("click", () => {
          const ordenID = btn.dataset.id;
          fetch("models/tomar_orden.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              id: ordenID,
              opcion: "asignar", // <-- aquí indicamos asignar
            }),
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.success) {
                alert("Orden asignada correctamente.");
                cargarOrdenes();
              } else {
                alert("Error: " + data.message);
              }
            })
            .catch((err) => {
              console.error("Error al tomar la orden:", err);
              alert("Error inesperado");
            });
        });
      });
    })
    .catch((err) => console.error("Error cargando órdenes:", err));
}

// 2) Muestra datos de una sola persona en el modal
function mostrarDetalleOrden(id) {
  const cont = document.getElementById("modalBody");
  cont.innerHTML = "Cargando...";

  fetch(`models/detalle_orden.php?id=${id}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.error) {
        cont.innerHTML = `<p class="text-red-600">${data.error}</p>`;
      } else if (Array.isArray(data)) {
        // Mostrar todos los platos en lista
        let html = '<ul class="list-disc pl-5 space-y-2">';
        data.forEach((plato) => {
          html += `
            <li>
              <strong>${plato.NombrePlato}</strong><br>
              Cantidad: ${plato.Cantidad}<br>
              Estado: ${plato.EstadoPlato}<br>
              Tiempo estimado por unidad: ${plato.TiempoEstimadoPorUnidad} min
            </li>
          `;
        });
        html += "</ul>";
        cont.innerHTML = html;
      } else {
        cont.innerHTML = `<p class="text-red-600">Datos inesperados</p>`;
      }
      MicroModal.show("modal-orden");
    })
    .catch(() => {
      cont.innerHTML = `<p class="text-red-600">Error al cargar datos.</p>`;
    });
}
setInterval(() => {
  cargarOrdenes();
}, 3000); // cada 10 segundos

// Ejecuta al cargar la página
window.addEventListener("DOMContentLoaded", cargarOrdenes);
