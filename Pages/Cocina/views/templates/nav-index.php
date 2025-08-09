<!-- Sidebar actualizado con íconos alternativos -->
<nav class="flex flex-wrap justify-center gap-4 lg:gap-6">

    <!-- Mis Panel -->
    <button class="bg-[#fddde6] hover:bg-[#f3cab8] rounded-full px-6 py-4 lg:px-8 lg:py-5 flex items-center gap-3 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl min-w-[140px] lg:min-w-[200px]"
        onclick="window.location.href='#'">
        <i data-lucide="layout-dashboard" class="w-5 h-5 lg:w-6 lg:h-6 text-[#6b30ff] flex-shrink-0"></i>
        <span class="text-gray-800 font-semibold text-sm lg:text-base whitespace-nowrap">Mis Panel</span>
    </button>

    <!-- Estaciones -->
    <button class="bg-[#fddde6] hover:bg-[#f3cab8] rounded-full px-6 py-4 lg:px-8 lg:py-5 flex items-center gap-3 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl min-w-[140px] lg:min-w-[200px]"
        onclick="window.location.href='views/estaciones.php'">
        <i data-lucide="clipboard-list" class="w-5 h-5 lg:w-6 lg:h-6 text-[#6b30ff] flex-shrink-0"></i>
        <span class="text-gray-800 font-semibold text-sm lg:text-base whitespace-nowrap">Estaciones</span>
    </button>

    <!-- Inventario -->
    <button class="bg-[#fddde6] hover:bg-[#f3cab8] rounded-full px-6 py-4 lg:px-8 lg:py-5 flex items-center gap-3 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl min-w-[140px] lg:min-w-[200px]"
        onclick="window.location.href='views/inventario.php'">
        <i data-lucide="boxes" class="w-5 h-5 lg:w-6 lg:h-6 text-[#6b30ff] flex-shrink-0"></i>
        <span class="text-gray-800 font-semibold text-sm lg:text-base whitespace-nowrap">Inventario</span>
    </button>

    <!-- Comunicaciones -->
    <button class="bg-[#fddde6] hover:bg-[#f3cab8] rounded-full px-6 py-4 lg:px-8 lg:py-5 flex items-center gap-3 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl min-w-[140px] lg:min-w-[200px]"
        onclick="window.location.href='views/comunicacion.php'">
        <i data-lucide="messages-square" class="w-5 h-5 lg:w-6 lg:h-6 text-[#6b30ff] flex-shrink-0"></i>
        <span class="text-gray-800 font-semibold text-sm lg:text-base whitespace-nowrap">Comunicaciones</span>
    </button>

    <!-- Persona -->
    <button class="bg-[#fddde6] hover:bg-[#f3cab8] rounded-full px-6 py-4 lg:px-8 lg:py-5 flex items-center gap-3 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl min-w-[140px] lg:min-w-[200px]">
        <i data-lucide="users" class="w-5 h-5 lg:w-6 lg:h-6 text-[#6b30ff] flex-shrink-0"></i>
        <span class="text-gray-800 font-semibold text-sm lg:text-base whitespace-nowrap">Persona</span>
    </button>

</nav>

<!-- Asegúrate de tener este script al final del body para que Lucide cargue los íconos -->
<script>
    lucide.createIcons();
</script>