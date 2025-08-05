// Obtener el número de mesa desde la URL
const params = new URLSearchParams(window.location.search);
const numeroMesa = params.get('mesa');

// Mostrar el número de mesa en el encabezado
if (numeroMesa) {
  document.querySelector('header h1').textContent = `Mesa #${numeroMesa}`;
}

// Mostrar la hora actual y actualizar cada minuto
function mostrarHoraActual() {
  const ahora = new Date();
  const hora = ahora.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  document.getElementById('hora').textContent = `Hora: ${hora}`;
}
mostrarHoraActual();
setInterval(mostrarHoraActual, 60000);

// Crear botones de navegación con parámetro de mesa incluido
const rutas = [
  { texto: 'Mi Orden', archivo: 'miOrden.php' },
  { texto: 'Vista Restaurante', archivo: 'VistaRestaurante.php' },
  { texto: 'Acciones', archivo: 'Acciones.php' },
  { texto: 'Redes', archivo: 'Redes.php' },
  { texto: 'Pagar', archivo: 'Pagar.php' }
];

const nav = document.getElementById('nav-mesas');
rutas.forEach(ruta => {
  const enlace = document.createElement('a');
  enlace.className = 'btn-nav';
  enlace.textContent = ruta.texto;
  enlace.href = `${ruta.archivo}?mesa=${numeroMesa}`;
  nav.appendChild(enlace);
});
