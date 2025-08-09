<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Ejecutivo</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-[#000] text-white font-sans leading-relaxed flex min-h-screen">
    <div class="page-wrapper flex w-full">
        <!-- Sidebar -->
        <?php include 'Template/nav.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 max-w-[calc(100%-250px)] mx-auto p-6 overflow-y-auto">
            <header class="flex flex-col md:flex-row md:justify-between md:items-center pb-5 border-b border-gray-200 mb-5">
                <h1 class="text-3xl font-bold mb-4 md:mb-0">Panel Ejecutivo</h1>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-lg shadow">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        <span id="current-date">--</span>
                    </div>
                    <div class="flex items-center gap-2 bg-green-50 text-green-600 font-semibold px-4 py-2 rounded-lg shadow">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <span id="current-time">--:--:--</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-content p-6">
                <div class="flex flex-col lg:flex-row space-y-6 lg:space-y-0 lg:space-x-6">
                    <!-- Meseros (1/3) -->
                    <div class="w-full lg:w-1/3 bg-[#F5DDD3] p-6 rounded-lg shadow-md text-gray-800">
                        <h2 class="text-xl font-semibold mb-4">Meseros</h2>
                        <div id="lista-meseros" class="space-y-4">
                            <!-- Aquí se cargan los meseros automáticamente -->
                        </div>
                    </div>


                    <!-- Áreas (2/3) -->
                    <div class="w-full lg:w-2/3 grid grid-cols-1 lg:grid-cols-2 gap-6" id="contenedor-areas">
                        <!-- Aquí se cargarán las áreas automáticamente -->

                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../Public/JS/Gerente/panel_mesas.js"></script>
    <script src="../Public/JS/Gerente/time.js"></script>

</body>

</html>