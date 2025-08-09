document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("modal-alerta");
  const titulo = document.getElementById("modal-titulo");
  const detCat = document.getElementById("modal-detalle-categoria");
  const detStock = document.getElementById("modal-detalle-stock");
  const btnCerrar = document.getElementById("cerrar-alerta");
  const btnResolver = document.getElementById("accion-modal");

  document.querySelectorAll(".btn-alerta").forEach((btn) => {
    btn.addEventListener("click", () => {
      // Leer datos de la alerta
      titulo.textContent = btn.dataset.titulo;
      detCat.textContent = "Categoría: " + btn.dataset.categoria;
      detStock.textContent = "Stock restante: " + btn.dataset.stock;

      // Muestra el modal
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    });
  });

  btnCerrar.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  });

  // Ejemplo: marcar como resuelta (puedes reemplazar con llamada AJAX)
  btnResolver.addEventListener("click", () => {
    alert("Alerta marcada como resuelta");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  });
});

document.addEventListener('DOMContentLoaded', () => {
    let stockNormal = 4;
    let stockCritico = 3;
    let salud = 57;

    const labelNormal = document.getElementById('stock-normal-label');
    const labelCritico = document.getElementById('stock-critico-label');
    const barNormal = document.getElementById('stock-normal-bar');
    const barCritico = document.getElementById('stock-critico-bar');
    const saludStock = document.getElementById('salud-stock');

    const alertaTomate = document.querySelector('[data-producto="Tomates Cherry"]');

    // Simula el deterioro del inventario cada 5 segundos
    setInterval(() => {
        if (stockNormal > 0) {
            stockNormal--;
            stockCritico++;
            salud -= 8;

            // Actualiza texto y barra
            labelNormal.textContent = `${stockNormal} productos`;
            labelCritico.textContent = `${stockCritico} productos`;
            barNormal.style.width = `${stockNormal * 20}%`;
            barCritico.style.width = `${stockCritico * 20}%`;
            saludStock.textContent = `${salud}%`;

            // Color cambia si salud es baja
            if (salud <= 40) {
                saludStock.classList.remove('text-[#4E5D83]');
                saludStock.classList.add('text-red-600', 'animate-pulse');
            }

            // Mostrar alerta si no está visible
            if (stockCritico >= 4 && alertaTomate) {
                alertaTomate.parentElement.parentElement.classList.add('bg-red-100');
                alertaTomate.parentElement.parentElement.classList.add('border-red-400');
                alertaTomate.parentElement.parentElement.classList.add('animate-pulse');
            }
        }
    }, 5000);
});

