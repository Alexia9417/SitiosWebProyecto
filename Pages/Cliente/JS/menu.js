const resumen = {};
const listaResumen = document.getElementById('lista-resumen');
const totalSpan = document.getElementById('total-pedido');

// Selecciona todos los elementos de platillo
document.querySelectorAll('.platillo-item').forEach(item => {
    const id = item.dataset.id;
    const nombre = item.dataset.nombre;
    const precio = parseFloat(item.dataset.precio);
    const btnSumar = item.querySelector('.btn-sumar');
    const btnRestar = item.querySelector('.btn-restar');
    const cantidadSpan = item.querySelector('.cantidad');

    // Añade un evento para sumar la cantidad del platillo
    btnSumar.addEventListener('click', () => {
        resumen[id] = resumen[id] ? resumen[id] + 1 : 1;
        cantidadSpan.textContent = resumen[id];
        actualizarResumen();
        document.getElementById('btn-resumen').style.display = 'block';
    });

    // Añade un evento para restar la cantidad del platillo
    btnRestar.addEventListener('click', () => {
        if (resumen[id]) {
            resumen[id]--;
            if (resumen[id] <= 0) {
                delete resumen[id];
            }
            cantidadSpan.textContent = resumen[id] || 0;
            actualizarResumen();
        }
    });
});

// Función para actualizar el contenido del modal
function actualizarResumen() {
    listaResumen.innerHTML = '';
    let total = 0;

    // Itera sobre los platillos seleccionados y actualiza el resumen
    Object.keys(resumen).forEach(id => {
        const item = document.querySelector(`.platillo-item[data-id="${id}"]`);
        const nombre = item.dataset.nombre;
        const precio = parseFloat(item.dataset.precio);
        const cantidad = resumen[id];
        total += precio * cantidad;

        const li = document.createElement('li');
        li.textContent = `${nombre} x${cantidad} = ₡${(precio * cantidad).toFixed(2)}`;
        listaResumen.appendChild(li);
    });

    // Actualiza el total
    totalSpan.textContent = total.toFixed(2);
}
