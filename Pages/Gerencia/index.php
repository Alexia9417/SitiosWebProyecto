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
        <aside class="w-64 bg-[#4E5D83] text-white shadow-md flex-shrink-0 hidden md:block">
            <div class="p-6 border-b border-[#BFBCE9]">
                <h2 class="text-2xl font-bold">Restaurante</h2>
                <p class="text-sm">Sistema de gestión</p>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-[#967ED5] text-white font-semibold hover:bg-[#BFBCE9]">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>
                            <span>Panel ejecutivo</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/inicio.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="home" class="w-5 h-5 mr-3"></i>
                            <span>Dashboard principal</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/personal.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                            <span>Informacion empleados</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/asignacion.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="user-check" class="w-5 h-5 mr-3"></i>
                            <span>Aasignacion Empleados</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/clientes.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="user-check" class="w-5 h-5 mr-3"></i>
                            <span>Gestión clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/alertas.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="alert-triangle" class="w-5 h-5 mr-3"></i>
                            <span>Alertas y quejas</span>
                        </a>
                    </li>
                    <li>
                        <a href="views/inventario.php" class="flex items-center p-3 rounded-lg hover:bg-[#BFBCE9]">
                            <i data-lucide="box" class="w-5 h-5 mr-3"></i>
                            <span>Gestión inventario</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="p-6 border-t border-[#BFBCE9]">
                <div class="flex items-center justify-between text-sm text-white">
                    <span>Estado del sistema</span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Operativo</span>
                </div>
            </div>
        </aside>

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

            <main class="grid gap-6 grid-cols-1 md:grid-cols-3">


                <section class="bg-[#F5DDD3] text-[#4E5D83] p-6 rounded-2xl shadow-md border-t-4 border-[#E9C89A] md:col-span-3">

                    <h2 class="text-2xl font-semibold text-[#E9C89A] flex items-center gap-2 mb-4">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Estado del Personal
                    </h2>

                    <!-- Roles -->
                    <div class="flex flex-wrap justify-around gap-6 border-b border-[#BFBCE9] pb-4 mb-6">
                        <div class="text-center min-w-[120px]">
                            <p class="text-sm">Meseros</p>
                            <p id="totalMeseros" class="text-2xl font-bold text-black">0</p>
                        </div>
                        <div class="text-center min-w-[120px]">
                            <p class="text-sm">Chefs</p>
                            <p id="totalChefs" class="text-2xl font-bold text-black">0</p>
                        </div>
                    </div>

                    <!-- KPIs -->
                    <div class="border-b border-[#BFBCE9] pb-4 mb-6">
                        <h3 class="text-sm font-medium mb-3">Promedio de KPIs</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div class="flex items-center gap-3">
                                <i data-lucide="zap" class="w-6 h-6 text-green-500"></i>
                                <div>
                                    <p class="text-sm">Energía</p>
                                    <p id="kpiEnergia" class="font-semibold text-green-600">0%</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="slash" class="w-6 h-6 text-red-500"></i>
                                <div>
                                    <p class="text-sm">Estrés</p>
                                    <p id="kpiEstres" class="font-semibold text-red-600">0%</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="trending-up" class="w-6 h-6 text-[#967ED5]"></i>
                                <div>
                                    <p class="text-sm">Eficiencia</p>
                                    <p id="kpiEficiencia" class="font-semibold text-[#4E5D83]">0%</p>
                                </div>
                            </div>
                        </div>
                    </div>



                </section>

                <!-- Satisfacción del Cliente -->
                <section class="bg-white p-6 rounded-2xl shadow-md border-t-4 border-green-500 md:col-span-2">
                    <h2 class="text-2xl font-semibold text-green-600 flex items-center gap-2 mb-4">
                        <i data-lucide="star" class="w-5 h-5"></i>
                        Satisfacción del Cliente
                    </h2>

                    <!-- Calificaciones generales -->
                    <div class="flex flex-wrap justify-around gap-6 border-b border-gray-200 pb-4 mb-6">
                        <div class="text-center min-w-[120px]">
                            <p class="text-sm text-gray-600">Calificación promedio</p>
                            <p id="promedioEstrellas" class="text-2xl font-bold text-[#4E5D83]">0.00</p>
                        </div>
                        <div class="text-center min-w-[120px]">
                            <p class="text-sm text-gray-600">Total de reseñas</p>
                            <p id="totalResenasHoy" class="text-2xl font-bold text-green-600">0</p>
                        </div>
                    </div>

                    <div id="contenedorResena" class="bg-[#BFBCE9] p-4 rounded-lg"></div>

                </section>

                <!-- Inventario Crítico -->
                <section class="bg-[#F5DDD3] p-6 rounded-2xl shadow-md border-t-4 border-red-500">
                    <h2 class="text-2xl font-semibold text-red-500 flex items-center gap-2 mb-4">
                        <i data-lucide="box" class="w-5 h-5"></i>
                        Inventario Crítico
                    </h2>

                    <div class="space-y-4">
                        <!-- Producto 1 -->
                        <div class="flex justify-between items-center p-4 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500"></i>
                                <div>
                                    <p class="font-semibold text-black">Salmón fresco</p>
                                    <p class="text-sm text-[#4E5D83]">Pescados</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="bg-red-100 text-red-800 font-semibold px-2 py-1 rounded text-sm">2 kg</p>
                                <p class="text-sm text-[#4E5D83]">Mín: 10</p>
                            </div>
                        </div>

                        <!-- Producto 2 -->
                        <div class="flex justify-between items-center p-4 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500"></i>
                                <div>
                                    <p class="font-semibold text-black">Aceite de oliva</p>
                                    <p class="text-sm text-[#4E5D83]">Aceites</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-black">1 L</p>
                                <p class="text-sm text-[#4E5D83]">Mín: 5</p>
                            </div>
                        </div>

                        <!-- Producto 3 -->
                        <div class="flex justify-between items-center p-4 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500"></i>
                                <div>
                                    <p class="font-semibold text-black">Queso parmesano</p>
                                    <p class="text-sm text-[#4E5D83]">Lácteos</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-black">1 kg</p>
                                <p class="text-sm text-[#4E5D83]">Mín: 8</p>
                            </div>
                        </div>
                    </div>
                </section>

            </main>


        </div>
    </div>
    <script src="Public/JS/Gerente/inicio.js"></script>
    <script>
        lucide.createIcons();

        function updateClock() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('es-CR', {
                hour12: false
            });
            document.getElementById('current-date').textContent = now.toLocaleDateString('es-CR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>

</html>