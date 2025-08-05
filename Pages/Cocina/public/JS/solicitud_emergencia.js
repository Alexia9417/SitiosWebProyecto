function cargarSolicitudesEmergencia() {
  fetch("/Sitios/Chef/models/mostrar_solicitudes.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error cargando solicitudes:", data.error);
        return;
      }

      // Filtrar solo emergencias
      const emergencias = data.datos.filter(
        (item) => item.Tipo === "emergencia"
      );

      const contenedor = document.getElementById("contenedor-emergencias");
      contenedor.innerHTML = "";

      // Crear sección de emergencias
      const seccion = document.createElement("section");
      seccion.className = "bg-[#F5DDD3] rounded-xl shadow p-6 space-y-4";

      seccion.innerHTML = `
        <h2 class="text-lg font-bold text-[#4E5D83] mb-4">Acciones de Emergencia</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="grid-emergencias"></div>
      `;

      contenedor.appendChild(seccion);

      const grid = seccion.querySelector("#grid-emergencias");

      emergencias.forEach((item) => {
        // Elegir icono según el nombre, o default "alert-circle"
        let icon = "alert-circle";
        if (item.Nombre.toLowerCase().includes("descanso")) icon = "coffee";
        else if (item.Nombre.toLowerCase().includes("salida")) icon = "log-out";
        else if (item.Nombre.toLowerCase().includes("asistencia"))
          icon = "phone-call";
        else if (item.Nombre.toLowerCase().includes("ayudante")) icon = "users";

        // Texto descriptivo ejemplo (puedes personalizar)
        let descripcion = "";
        switch (item.Nombre) {
          case "Pedir Descanso":
            descripcion = "+20 Energía";
            break;
          case "Salida Anticipada":
            descripcion = "Notificar gerente";
            break;
          case "Asistencia Medica":
            descripcion = "Emergencia";
            break;
          case "Ayudante Adicional":
            descripcion = "Solicitar ayuda extra";
            break;
          default:
            descripcion = "";
        }

        const card = document.createElement("div");
        card.className =
          "bg-[#BFBCE9] hover:bg-[#E9C89A] transition rounded-lg p-4 text-center space-y-1 cursor-pointer";

        card.innerHTML = `
          <i data-lucide="${icon}" class="w-6 h-6 mx-auto text-[#4E5D83]"></i>
          <h3 class="font-semibold text-[#4E5D83]">${item.Nombre}</h3>
          <p class="text-sm text-[#4E5D83]">${descripcion}</p>
        `;

        grid.appendChild(card);
      });

      lucide.createIcons();
    })
    .catch((err) => {
      console.error("Error al cargar solicitudes:", err);
    });
}

cargarSolicitudesEmergencia();
