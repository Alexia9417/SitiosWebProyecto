document.addEventListener("DOMContentLoaded", () => {
  cargarMeseros();
  cargarMesas();
  setInterval(cargarMesas, 2000); // Actualizar cada 2 segundos
});

/**
 * 1) Carga los meseros y permite seleccionar uno.
 */
let meseroSeleccionado = null;

// Mapa de nombres de área a sus IDs (ajústalo según tu BD)
const AREAS = [
  { id: 1, nombre: "Area Norte" },
  { id: 2, nombre: "Area Sur" },
  { id: 3, nombre: "Terraza" },
  { id: 4, nombre: "Area Vip" },
];

function cargarMeseros() {
  fetch("/Sitios/Gerente/models/Gerente/mostrar_empleado.php?tipo=2")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error cargando meseros:", data.error);
        return;
      }
      const cont = document.getElementById("lista-meseros");
      cont.innerHTML = "";

      data.datos.forEach((m) => {
        const div = document.createElement("div");
        div.className = "p-4 mb-4 bg-white rounded-lg shadow flex flex-col";

        // Cabecera con nombre y disponibilidad
        div.innerHTML = `
          <div class="flex items-center mb-2">
            <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
            <div class="flex-1">
              <p class="font-medium text-gray-800">${m.Nombre} ${m.Apellidos}</p>
              <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Disponible</span>
            </div>
          </div>
          <div class="flex space-x-2 mb-2" id="btns-areas-${m.IdUsuario}">
            <!-- Aquí irán los botones de área -->
          </div>
        `;
        cont.appendChild(div);

        // Generar botones de asignación para cada área
        const btnsCont = document.getElementById(`btns-areas-${m.IdUsuario}`);
        AREAS.forEach((a) => {
          const btn = document.createElement("button");
          btn.className =
            "px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs hover:bg-blue-200";
          btn.textContent = `Asignar a ${a.nombre}`;
          btn.onclick = () => {
            asignarMesero(a.id, m.IdUsuario);
          };
          btnsCont.appendChild(btn);
        });
      });
    })
    .catch((err) => console.error("Error de red al cargar meseros:", err));
}

/**
 * 2) Carga las mesas por área, inserta botón "Asignar mesero"
 */
function cargarMesas() {
  fetch("/Sitios/Gerente/models/Gerente/mesas.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al cargar mesas:", data.error);
        return;
      }

      const cont = document.getElementById("contenedor-areas");
      cont.innerHTML = "";

      // Agrupar mesas por área
      const porArea = data.datos.reduce((acc, mesa) => {
        (acc[mesa.AreaNombre] = acc[mesa.AreaNombre] || []).push(mesa);
        return acc;
      }, {});

      // Colores de fondo para cada área
      const colores = {
        "Area Norte": "bg-[#967ED5]",
        "Area Sur": "bg-[#4E5D83]",
        Terraza: "bg-[#E9C89A]",
        "Area Vip": "bg-[#BFBCE9]",
      };

      Object.entries(porArea).forEach(([area, mesas]) => {
        const areaKey = area.replace(/\s+/g, "");
        const esVip = /vip/i.test(area);
        const section = document.createElement("section");

        // Clase de fondo y texto
        section.className = `
          ${colores[area] || "bg-gray-100"} p-6 rounded-lg shadow-md 
          ${esVip ? "text-gray-800" : "text-white"}
        `
          .trim()
          .replace(/\s+/g, " ");

        // Detectar mesero asignado (todas las mesas comparten el mismo)
        const meseroId = mesas[0].IdMesero;
        const meseroName = mesas[0].Nombre;

        // Construir el HTML de la sección
        section.innerHTML = `
          <!-- Header: Título, Mesero y Estado -->
          <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
              <h3 class="text-xl font-semibold mr-4">${area}</h3>
              ${
                meseroId
                  ? `<div class="flex items-center bg-white bg-opacity-20 px-3 py-1 rounded">
                       <span class="italic mr-2">Mesero: ${meseroName}</span>
                       <i data-lucide="user-x" class="w-5 h-5 cursor-pointer" 
                          title="Quitar mesero"
                          onclick="desasignarMesero(${obtenerIdAreaPorNombre(
                            area
                          )}, ${meseroId})">
                       </i>
                     </div>`
                  : `<span class="text-sm italic text-red-200">Sin mesero</span>`
              }
            </div>
            <span class="text-xs px-2 py-0.5 ${
              esVip
                ? "bg-gray-300 text-gray-700"
                : "bg-green-200 text-green-900"
            } rounded-full">
              ${esVip ? "Cerrada" : "Activa"}
            </span>
          </div>

          <!-- Controles -->
          <div class="space-y-4 mb-4">
            ${
              !meseroId
                ? `<button 
                     id="btnAsignar${areaKey}"
                     class="w-full px-4 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors"
                     onclick="(meseroSeleccionado 
                               ? asignarMesero(${obtenerIdAreaPorNombre(
                                 area
                               )}, meseroSeleccionado) 
                               : alert('Primero seleccioná un mesero del listado.'))">
                     Asignar mesero
                   </button>`
                : ``
            }
            ${
              !esVip
                ? `<button 
                     id="btnCerrar${areaKey}"
                     class="w-full flex items-center justify-center px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition-colors">
                     <i data-lucide="lock" class="w-5 h-5 mr-2"></i>
                     Cerrar área
                   </button>`
                : `<button 
                     id="btnAbrir${areaKey}"
                     class="w-full flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition-colors">
                     <i data-lucide="lock-open" class="w-5 h-5 mr-2"></i>
                     Abrir área
                   </button>`
            }
          </div>

          <!-- Lista de Mesas -->
          <div class="space-y-2" id="mesas-${areaKey}"></div>
        `;

        cont.appendChild(section);

        // Renderizar cada mesa
        const contMesas = document.getElementById(`mesas-${areaKey}`);
        mesas.forEach((m) => {
          const estadoInfo = {
            0: {
              txt: "Libre",
              icon: "check",
              color: "bg-green-100 text-green-700",
            },
            1: {
              txt: "Ocupada",
              icon: "users",
              color: "bg-red-100 text-red-700",
            },
            2: {
              txt: "En limpieza",
              icon: "sparkles",
              color: "bg-yellow-100 text-yellow-700",
            },
            3: {
              txt: "En orden",
              icon: "clipboard-list",
              color: "bg-blue-100 text-blue-700",
            },
          }[m.MesaEstado] || {
            txt: "Desconocido",
            icon: "help-circle",
            color: "bg-gray-100 text-gray-700",
          };

          contMesas.innerHTML += `
            <div class="flex justify-between items-center p-2 rounded-lg border border-gray-200">
              <span class="text-gray-700">Mesa ${m.Numero}</span>
              <span class="text-xs px-2 py-0.5 ${estadoInfo.color} rounded-full flex items-center">
                <i data-lucide="${estadoInfo.icon}" class="w-3 h-3 mr-1"></i> ${estadoInfo.txt}
              </span>
            </div>
          `;
        });

        // Inicializar Lucide Icons
        if (window.lucide) lucide.createIcons();
      });
    })
    .catch((err) => console.error("Error de red al cargar mesas:", err));
}

/**
 * Llama al SP para asignar un mesero a un área.
 */
function asignarMesero(idArea, idUsuario) {
  fetch("/Sitios/Gerente/models/Gerente/asignar_mesa.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      id_area: idArea,
      id_usuario: idUsuario,
      asignar: true,
    }),
  })
    .then((res) => res.json())
    .then((r) => {
      if (r.success) {
        //alert("Mesero asignado correctamente");
        cargarMesas();
      } else {
        alert("Error: " + r.error);
      }
    })
    .catch((err) => console.error("Error de red al asignar mesero:", err));
}

/**
 * Llama al SP para desasignar un mesero de un área.
 */
function desasignarMesero(idArea, idUsuario) {
  fetch("/Sitios/Gerente/models/Gerente/asignar_mesa.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      id_area: idArea,
      id_usuario: idUsuario,
      asignar: false,
    }),
  })
    .then((res) => res.json())
    .then((r) => {
      if (r.success) {
        //alert("Mesero removido correctamente");
        cargarMesas();
      } else {
        alert("Error: " + r.error);
      }
    })
    .catch((err) => console.error("Error de red al quitar mesero:", err));
}

/**
 * Mapea nombre de área a su ID. Ajusta según tu BD real.
 */
function obtenerIdAreaPorNombre(nombre) {
  const map = {
    "Area Norte": 1,
    "Area Sur": 2,
    "Terraza": 3,
    "Area Vip": 4,
  };
  return map[nombre] || 0;
}
