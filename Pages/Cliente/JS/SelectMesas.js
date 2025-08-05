const mesas = [
  { numero: 1, estado: 'disponible' },
  { numero: 2, estado: 'ocupada' },
  { numero: 3, estado: 'disponible' },
  { numero: 4, estado: 'ocupada' },
  { numero: 5, estado: 'disponible' },
  { numero: 6, estado: 'ocupada' },
  { numero: 7, estado: 'disponible' },
  { numero: 8, estado: 'ocupada' },
  { numero: 9, estado: 'disponible' },
  { numero: 10, estado: 'ocupada' },
  { numero: 11, estado: 'disponible' },
  { numero: 12, estado: 'disponible' }
];

const contenedorDisponibles = document.getElementById('mesas-disponibles');
const contenedorOcupadas = document.getElementById('mesas-ocupadas');
const contador = document.getElementById('contador-mesas');

function crearMesa(mesa) {
  const div = document.createElement('div');
  div.classList.add('mesa', mesa.estado);

  div.innerHTML = `
    <div class="mesa-numero">#${mesa.numero}</div>
    <br>
    <div class="estado">${mesa.estado === 'disponible' ? 'Disponible' : 'Ocupada'}</div>
    <p>Capacidad: 2 personas</p>
    <p>Ubicación: Ventana</p>
    ${mesa.estado === 'disponible'
      ? `<button class="btn-seleccionar" onclick="location.href='miOrden.php?mesa=${mesa.numero}'">Seleccionar Mesa</button>`
      : ''
    }

  `;

  return div;
}

function mostrarMesas() {
  let disponibles = 0;
  mesas.forEach(mesa => {
    const mesaHTML = crearMesa(mesa);
    if (mesa.estado === 'disponible') {
      contenedorDisponibles.appendChild(mesaHTML);
      disponibles++;
    } else {
      contenedorOcupadas.appendChild(mesaHTML);
    }
  });
  contador.textContent = `${disponibles} mesas disponibles`;
}

mostrarMesas();
