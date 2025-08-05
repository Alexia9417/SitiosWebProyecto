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
                <h1 class="text-3xl font-bold mb-4 md:mb-0">Panel de Asignación</h1>
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
                <!-- Resumen de métricas -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                    <div class="bg-[#F5DDD3] p-6 rounded-lg shadow-md text-gray-800 text-center">
                        <h3 class="text-lg font-semibold">Meseros</h3>
                        <p class="text-3xl font-bold mt-2">{{ waitersCount }}</p>
                    </div>
                    <div class="bg-[#D3F5E0] p-6 rounded-lg shadow-md text-gray-800 text-center">
                        <h3 class="text-lg font-semibold">Chefs</h3>
                        <p class="text-3xl font-bold mt-2">{{ chefsCount }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md text-gray-800 text-center">
                        <h3 class="text-lg font-semibold">Total Empleados</h3>
                        <p class="text-3xl font-bold mt-2">{{ totalEmployees }}</p>
                    </div>
                </div>

                <!-- Lista de empleados -->
                <div class="bg-white p-6 rounded-lg shadow-md text-gray-800">
                    <h2 class="text-xl font-semibold mb-4">Lista de Empleados</h2>
                    <ul class="space-y-4">
                        <!-- Iterar sobre cada empleado -->
                        <li class="flex items-center justify-between p-3 border border-gray-300 rounded-lg">
                            <div id="lista-empleados" class="space-y-4"></div>
                        </li>
                        <!-- Fin iteración -->
                    </ul>
                </div>
            </main>


            <script>
                function changeEmployeeRole(id, newRole) {
                    fetch('assign_role.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id,
                            role: newRole
                        })
                    }).then(() => location.reload());
                }
            </script>

        </div>
    </div>
    <script src="../Public/JS/Gerente/asignar_empleado.js"></script>
    <script src="../Public/JS/Gerente/time.js"></script>
    <!-- Scripts -->

</body>

</html>