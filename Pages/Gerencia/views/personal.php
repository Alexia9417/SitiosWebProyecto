<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Ejecutivo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-[#000] text-white font-sans leading-relaxed flex min-h-screen">
    <div class="page-wrapper flex w-full">
        <!-- Sidebar -->
        <?php include 'Template/nav.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 max-w-[calc(100%-250px)] mx-auto p-6 overflow-y-auto">
            <header class="flex flex-col md:flex-row md:justify-between md:items-center pb-5 border-b border-[#BFBCE9] mb-5">
                <h1 class="text-3xl font-bold text-[#F5DDD3] mb-4 md:mb-0">Panel de Personal</h1>
                <div class="flex flex-wrap gap-4 text-sm text-white">
                    <div class="flex items-center gap-2 bg-[#F5DDD3] text-[#4E5D83] px-4 py-2 rounded-lg shadow">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        <span id="current-date">--</span>
                    </div>
                    <div class="flex items-center gap-2 bg-[#E9C89A] text-[#4E5D83] font-semibold px-4 py-2 rounded-lg shadow">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <span id="current-time">--:--:--</span>
                    </div>
                </div>
            </header>

            <!-- Personal Section -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Lista Personal -->
                <div class="col-span-1 lg:col-span-1 bg-[#F5DDD3] text-[#4E5D83] rounded-lg shadow p-6 flex flex-col">
                    <h2 class="text-xl font-semibold mb-4">Personal</h2>
                    <div class="flex mb-4">
                        <button id="btnMeseros" class="flex items-center px-4 py-2 border-b-2 border-[#967ED5] text-[#967ED5] font-semibold">
                            <i data-lucide="users" class="w-4 h-4 mr-2"></i>Meseros
                            <span class="ml-2 bg-[#E9C89A] text-[#4E5D83] text-xs px-2 py-0.5 rounded-full">0</span>
                        </button>
                        <button id="btnChefs" class="flex items-center px-4 py-2 text-[#4E5D83] hover:text-[#967ED5]">
                            <i data-lucide="chef-hat" class="w-4 h-4 mr-2"></i>Chefs
                            <span class="ml-2 bg-[#E9C89A] text-[#4E5D83] text-xs px-2 py-0.5 rounded-full">0</span>
                        </button>
                    </div>
                    <div class="space-y-3 overflow-auto" id="contenedorPersonal">
                        <!-- Lista personal -->
                    </div>
                </div>



                <!-- Detalles Empleado -->
                <div class="col-span-1 lg:col-span-2 bg-[#F5DDD3] text-[#4E5D83] rounded-lg shadow p-6 flex flex-col">
                    <div id="contenedorPersonal"></div>
                    <div id="detalleEmpleado" class="hidden mt-6 col-span-2"></div>

            </section>
        </div>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../Public/JS/Gerente/personal.js"></script>
    <script src="../Public/JS/Gerente/time.js"></script>
</body>

</html>