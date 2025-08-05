document.addEventListener("DOMContentLoaded", function () {
  cargarEmpleados();
});

function cargarEmpleados() {
  const contenedor = document.getElementById("lista-empleados");

  fetch("/Sitios/Gerente/models/Gerente/mostrar_empleado.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        console.error("Error al obtener empleados:", data.error);
        return;
      }

      contenedor.innerHTML = "";

      data.datos.forEach((employee) => {
        const div = document.createElement("div");
        div.className = "flex items-center justify-between mb-4";

        const avatarUrl = `https://api.dicebear.com/7.x/initials/svg?seed=${employee.Nombre}+${employee.Apellidos}`;

        let roleClass = "bg-gray-100 text-gray-500";
        if (employee.TipoUsuario === "Mesero") {
          roleClass = "bg-green-100 text-green-800";
        } else if (employee.TipoUsuario === "Chef") {
          roleClass = "bg-yellow-100 text-yellow-800";
        }

        div.innerHTML = `
          <div class="flex items-center">
            <img class="w-10 h-10 rounded-full mr-4 object-cover" src="${avatarUrl}" alt="Avatar de ${
          employee.Nombre
        }">
            <div>
              <p class="font-medium">${employee.Nombre} ${
          employee.Apellidos
        }</p>
              <span class="text-xs px-2 py-0.5 rounded-full ${roleClass}">
                ${employee.TipoUsuario || "Sin rol"}
              </span>
            </div>
          </div>
          <div class="flex space-x-2">
            <button
              class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-1 rounded text-xs"
              onclick="assignRole(${employee.IdUsuario}, 'Chef')"
              ${employee.TipoUsuario === "Chef" ? "disabled" : ""}>
              Asignar Chef
            </button>
            <button
              class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded text-xs"
              onclick="assignRole(${employee.IdUsuario}, 'Mesero')"
              ${employee.TipoUsuario === "Mesero" ? "disabled" : ""}>
              Asignar Mesero
            </button>
            <button
              class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded text-xs"
              onclick="removeRole(${employee.IdUsuario})"
              ${!employee.TipoUsuario ? "disabled" : ""}>
              Quitar rol
            </button>
          </div>
        `;

        contenedor.appendChild(div);
      });
    })
    .catch((err) => {
      console.error("Error de red:", err);
    });
}

function assignRole(idUsuario, rolNombre) {
  const rolMap = {
    Chef: 4,
    Mesero: 2,
  };

  const nuevoRol = rolMap[rolNombre];
  if (!nuevoRol) {
    alert("Rol inválido");
    return;
  }

  fetch("/Sitios/Gerente/models/Gerente/cambiar_rol.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      id_usuario: idUsuario,
      nuevo_rol: nuevoRol,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        //alert("Rol asignado correctamente");
        cargarEmpleados();
      } else {
        alert("Error: " + data.error);
      }
    })
    .catch((err) => {
      console.error("Error de red:", err);
      alert("Error de red");
    });
}

function removeRole(idUsuario) {
  fetch("/Sitios/Gerente/models/Gerente/cambiar_rol.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      id_usuario: idUsuario,
      nuevo_rol: 6, // 6 = Empleado sin rol especial
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        //alert("Rol eliminado correctamente");
        cargarEmpleados();
      } else {
        alert("Error: " + data.error);
      }
    });
}
