function mostrarDetalleEstacion(estacionID) {
  const cont = document.getElementById("modalBody");
  cont.innerHTML = "<p class='text-gray-500'>Cargando...</p>";

  fetch(`/Sitios/Chef/models/detalle_estacion.php?id=${estacionID}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.error) {
        cont.innerHTML = `<p class="text-red-600">${data.error}</p>`;
      } else if (Array.isArray(data) && data.length > 0) {
        let html = '<ul class="list-disc pl-5 space-y-4">';

        data.forEach((plato, index) => {
          // Crear un span para el contador con id único
          const contadorId = `contador-${index}`;

          html += `
            <li>
              <strong>Orden #${plato.numero_orden}</strong><br>
              Plato: ${plato.nombre_plato}<br>
              Estado: ${plato.estado}<br>
              Tiempo estimado: ${plato.tiempo_estimado} seg <br>
              ${
                plato.estado === "Pendiente"
                  ? `<button class="btn-cocinar mt-2 bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 text-sm"
                      data-orden="${plato.numero_orden}">
                      <i data-lucide="flame" class="w-4 h-4 inline-block mr-1"></i> Cocinar
                    </button>`
                  : ""
              }
              ${
                plato.estado === "Cocinando" && plato.fin_preparacion
                  ? `<div>Tiempo restante: <span id="${contadorId}">--:--</span></div>`
                  : ""
              }
            </li>`;
        });

        html += "</ul>";
        cont.innerHTML = html;

        lucide.createIcons();

        // Función para actualizar el contador para cada plato cocinando
        data.forEach((plato, index) => {
          if (plato.estado === "Cocinando" && plato.fin_preparacion) {
            const contadorElem = document.getElementById(`contador-${index}`);
            const fin = new Date(plato.fin_preparacion);

            // Actualiza el contador cada segundo
            let llamado = false; // bandera para evitar múltiples llamadas

            const intervalId = setInterval(() => {
              const ahora = new Date();
              let diff = Math.floor((fin - ahora) / 1000); // diferencia en segundos

              if (diff < 0) {
                clearInterval(intervalId);
                contadorElem.textContent = "00:00";

                if (!llamado) {
                  llamado = true; // evita múltiples llamadas

                  // Validación previa (sanitiza antes de enviar)
                  const idOrden = Number(plato.numero_orden);
                  const idPlatillo = Number(plato.id_platillo);

                  if (!idOrden || !idPlatillo) {
                    console.error("IDs no válidos para marcar como listo");
                    return;
                  }

                  // Llamar al backend para cambiar estado a "Listo"
                  fetch("/Sitios/Chef/models/marcar_listo.php", {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                      id_orden: idOrden,
                      id_platillo: idPlatillo,
                    }),
                  })
                    .then((res) => {
                      if (!res.ok) {
                        throw new Error(`Error HTTP ${res.status}`);
                      }
                      return res.json();
                    })
                    .then((data) => {
                      if (data.success) {
                        console.log("✔️ Estado actualizado a Listo");
                        mostrarDetalleEstacion(estacionID); // refrescar la vista
                      } else {
                        console.warn("⚠️ Error en backend:", data.message);
                      }
                    })
                    .catch((err) => {
                      console.error("❌ Error de red o backend:", err.message);
                    });
                }
              } else {
                // Actualiza contador visual
                const min = String(Math.floor(diff / 60)).padStart(2, "0");
                const seg = String(diff % 60).padStart(2, "0");
                contadorElem.textContent = `${min}:${seg}`;
              }
            }, 1000);
          }
        });

        // Agregar evento para botones "Cocinar"
        document.querySelectorAll(".btn-cocinar").forEach((btn) => {
          btn.addEventListener("click", () => {
            const ordenID = btn.dataset.orden;

            fetch("/Sitios/Chef/models/tomar_orden.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                id: ordenID,
                opcion: "cocinar",
              }),
            })
              .then((res) => res.json())
              .then((data) => {
                if (data.success) {
                  alert("Preparación iniciada.");
                  mostrarDetalleEstacion(estacionID); // Refrescar modal para actualizar contador
                } else {
                  alert("Error: " + data.message);
                }
              })
              .catch((err) => {
                console.error("Error al iniciar cocción:", err);
                alert("Error inesperado.");
              });
          });
        });
      } else {
        cont.innerHTML =
          "<p class='text-gray-500'>Sin platillos asignados.</p>";
      }

      MicroModal.show("modal-orden");
    })
    .catch((error) => {
      console.error(error);
      cont.innerHTML = "<p class='text-red-600'>Error al cargar datos.</p>";
    });
}
