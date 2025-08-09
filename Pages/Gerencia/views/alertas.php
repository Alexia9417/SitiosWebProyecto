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
                <h1 class="text-3xl font-bold mb-4 md:mb-0">Panel de Alertas</h1>
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
            <!-- Main Content -->
            <main class="flex-grow flex flex-col">
                <!-- Content Area -->
                <div class="flex flex-grow overflow-hidden">

                    <!-- Alerts List -->
                    <section class="w-2/3 p-6 overflow-y-auto bg-[#F5DDD3] shadow rounded-lg text-gray-900">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            <h3 class="ml-2 text-lg font-semibold flex-grow">Emergencias Activas</h3>
                            <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full font-medium">3</span>
                        </div>

                        <!-- Alert Cards -->
                        <div class="space-y-4">
                            <!-- ALTO -->
                            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-red-600">
                                <div class="flex">
                                    <div class="text-2xl">😟</div>
                                    <div class="ml-4 flex-grow">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-semibold">Cliente con malestar</h4>
                                            <span class="text-red-600 font-semibold">ALTO</span>
                                        </div>
                                        <p class="text-gray-600 mt-1">Cliente reporta náuseas después de consumir mariscos</p>
                                        <div class="flex space-x-4 text-gray-500 text-sm mt-2">
                                            <span><i class="fas fa-clock"></i> 8 m</span>
                                            <span><i class="fas fa-users"></i> 1 afectados</span>
                                            <span>$200</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-red-600"><i class="fas fa-exclamation-triangle"></i> -15% eficiencia</span>
                                            <button class="bg-yellow-500 text-white px-3 py-1 rounded">En proceso...</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MEDIO -->
                            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-yellow-400">
                                <div class="flex">
                                    <div><i class="fas fa-bolt text-2xl text-yellow-500"></i></div>
                                    <div class="ml-4 flex-grow">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-semibold">Falla eléctrica en cocina</h4>
                                            <span class="text-yellow-600 font-semibold">MEDIO</span>
                                        </div>
                                        <p class="text-gray-600 mt-1">Corte parcial de energía en la zona de cocina principal</p>
                                        <div class="flex space-x-4 text-gray-500 text-sm mt-2">
                                            <span><i class="fas fa-clock"></i> 15 m</span>
                                            <span><i class="fas fa-users"></i> 8 afectados</span>
                                            <span>$500</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-yellow-600"><i class="fas fa-exclamation-triangle"></i> -40% eficiencia</span>
                                            <button class="bg-blue-500 text-white px-3 py-1 rounded">Solucionar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BAJO -->
                            <div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-500">
                                <div class="flex">
                                    <div><i class="fas fa-wrench text-2xl text-green-500"></i></div>
                                    <div class="ml-4 flex-grow">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-semibold">Refrigerador con falla</h4>
                                            <span class="text-green-600 font-semibold">BAJO</span>
                                        </div>
                                        <p class="text-gray-600 mt-1">Refrigerador principal no mantiene temperatura adecuada</p>
                                        <div class="flex space-x-4 text-gray-500 text-sm mt-2">
                                            <span><i class="fas fa-clock"></i> 45 m</span>
                                            <span><i class="fas fa-users"></i> 0 afectados</span>
                                            <span>$150</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-green-600"><i class="fas fa-exclamation-triangle"></i> -10% eficiencia</span>
                                            <button class="bg-blue-500 text-white px-3 py-1 rounded">Solucionar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Details Panel -->
                    <section class="w-1/3 p-6 bg-[#BFBCE9] border-l overflow-y-auto text-gray-800">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-4xl mb-4 text-[#967ED5]"></i>
                            <h3 class="text-lg font-semibold">Selecciona una emergencia</h3>
                            <p class="mt-2">Haz clic en cualquier emergencia para ver los detalles y opciones de solución</p>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- Script para hora dinámica -->
    <script src="../Public/JS/Gerente/time.js"></script>
</body>

</html>