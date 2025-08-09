<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventario de Restaurante</title>
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
                <h1 class="text-3xl font-bold mb-4 md:mb-0">Panel de Inventario</h1>
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

            <!-- Main Inventory Content -->
            <main class="flex-grow flex flex-col">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Inventario Lista -->
                    <section class="lg:col-span-2 p-6 bg-[#F5DDD3] shadow rounded-lg text-gray-900">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-boxes text-[#4E5D83] text-xl"></i>
                            <h3 class="ml-2 text-lg font-semibold flex-grow">Productos en Inventario</h3>
                            <button class="bg-[#967ED5] text-white px-3 py-1 rounded text-sm">+ Agregar Producto</button>
                        </div>

                        <!-- Tabla Inventario -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white rounded shadow text-sm">
                                <thead class="bg-[#967ED5] text-white">
                                    <tr>
                                        <th class="py-2 px-4 text-left">Producto</th>
                                        <th class="py-2 px-4 text-left">Categoría</th>
                                        <th class="py-2 px-4 text-left">Cantidad</th>
                                        <th class="py-2 px-4 text-left">Estado</th>
                                        <th class="py-2 px-4 text-left">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b hover:bg-[#E9C89A]">
                                        <td class="py-2 px-4">Tomates</td>
                                        <td class="py-2 px-4">Verduras</td>
                                        <td class="py-2 px-4">25 kg</td>
                                        <td class="py-2 px-4 text-green-600">Disponible</td>
                                        <td class="py-2 px-4">
                                            <button class="text-blue-600 hover:underline">Editar</button>
                                        </td>
                                    </tr>
                                    <tr class="border-b hover:bg-[#E9C89A]">
                                        <td class="py-2 px-4">Carne de res</td>
                                        <td class="py-2 px-4">Carnes</td>
                                        <td class="py-2 px-4">12 kg</td>
                                        <td class="py-2 px-4 text-yellow-600">Bajo stock</td>
                                        <td class="py-2 px-4">
                                            <button class="text-blue-600 hover:underline">Editar</button>
                                        </td>
                                    </tr>
                                    <!-- Más filas -->
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Panel Lateral Detalles -->
                    <section class="p-6 bg-[#BFBCE9] border-l overflow-y-auto text-gray-800">
                        <div class="text-center">
                            <i class="fas fa-box-open text-4xl mb-4 text-[#967ED5]"></i>
                            <h3 class="text-lg font-semibold">Detalles del producto</h3>
                            <p class="mt-2">Selecciona un producto para ver sus detalles, cantidad, y acciones disponibles.</p>
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
