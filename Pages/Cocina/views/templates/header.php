<!-- Notificaciones (Izquierda) -->
<button id="btn-notificaciones" class="flex items-center justify-center w-10 h-10 bg-[#BFBCE9] text-[#4E5D83] rounded-full hover:scale-105 transition">
    <i data-lucide="bell" class="w-5 h-5"></i>
</button>

<!-- Datos del Chef (Centro) -->
<div class="flex items-center gap-4 text-[#4E5D83]">
    <div class="w-14 h-14 bg-black flex items-center justify-center rounded-full">
        <i data-lucide="user" class="text-white w-6 h-6"></i>
    </div>
    <div>
        <h2 class="text-lg font-semibold text-black" id="chef-nombre">Cargando...</h2>
        <p class="text-sm text-black">Chef</p>
    </div>
</div>

<!-- Cerrar sesión (Derecha) -->
<button id="btn-logout" class="flex items-center gap-2 bg-[#4E5D83] text-white px-4 py-2 rounded-lg hover:bg-[#7c67bd] transition">
    <i data-lucide="log-out" class="w-5 h-5"></i>
    <span>Cerrar sesión</span>
</button>