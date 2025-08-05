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

      const empleados = data.datos;
      const meseros = empleados.filter((e) => e.IdTipoUsuario === 2);
      const chefs = empleados.filter((e) => e.IdTipoUsuario === 4);

      const contenedor = document.getElementById("contenedorPersonal");
      const btnMeseros = document.getElementById("btnMeseros");
      const btnChefs = document.getElementById("btnChefs");
      const badgeMeseros = btnMeseros.querySelector("span");
      const badgeChefs = btnChefs.querySelector("span");

      badgeMeseros.textContent = meseros.length;
      badgeChefs.textContent = chefs.length;

      renderListaPersonal(meseros, contenedor);

      btnMeseros.onclick = () => {
        activarBoton(btnMeseros, btnChefs);
        renderListaPersonal(meseros, contenedor);
      };
      btnChefs.onclick = () => {
        activarBoton(btnChefs, btnMeseros);
        renderListaPersonal(chefs, contenedor);
      };
    });
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
    });
}
