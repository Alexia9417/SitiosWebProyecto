<?php
?>

<!DOCTYPE html>
<html lang="es" class="bg-gray-50 text-gray-800">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef Dashboard - Gestión de Inventario</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>


<body class="bg-[#000] text-white font-sans leading-relaxed min-h-screen flex flex-col">

    <!-- Header arriba -->
    <header class="bg-[#dcc093] flex flex-col md:flex-row md:justify-between md:items-center border-b border-[#BFBCE9] mb-4 px-6 py-2 gap-4">
        <?php include 'templates/header.php'; ?>
    </header>

    <!-- Contenedor principal -->
    <div class="page-wrapper flex w-full flex-1">

        <!-- Main -->
        <div class="flex-1 max-w-[calc(100%-250px)] mx-auto px-6 overflow-y-auto">


            <!-- Métricas -->
            <div id="contenedor-kpis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>

            <!-- Sidebar -->
            <div class="w-full max-w-6xl mx-auto m-6">
                <?php include 'templates/nav.php'; ?>
            </div>
            <div class="dashboard-sections grid grid-cols-1 md:grid-cols-2 gap-6 my-3">


                <!-- Inventory Status and Critical Alerts Section -->
                <div class="grid grid-cols-1 mt-8 gap-6">

                    <!-- Estado del Inventario Card -->
                    <div class="bg-[#F5DDD3] rounded-2xl shadow-md p-6 border border-[#BFBCE9] lg:col-span-2">
                        <!-- Encabezado -->
                        <div class="flex items-center gap-4 mb-6">
                            <div class="bg-[#967ED5] text-white p-3 rounded-full shadow-md">
                                <i class="fas fa-box text-lg"></i>
                            </div>
                            <h2 class="text-2xl font-semibold text-[#4E5D83]">Estado del Inventario</h2>
                        </div>

                        <!-- Cuerpo principal -->
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            <!-- Sección de stock -->
                            <div class="flex-1 space-y-5">
                                <div>
                                    <div class="flex items-center gap-2 text-sm font-semibold text-[#4E5D83] mb-1">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Distribución del Stock</span>
                                    </div>
                                </div>
                                <!-- Stock Normal -->
                                <div>
                                    <div class="flex justify-between text-sm text-[#4E5D83] mb-1">
                                        <span>Stock Normal</span>
                                        <span class="font-medium" id="stock-normal-label">4 productos</span>
                                    </div>
                                    <div class="w-full bg-[#BFBCE9] h-2 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#4E5D83]" id="stock-normal-bar" style="width: 70%"></div>
                                    </div>
                                </div>
                                <!-- Stock Crítico -->
                                <div>
                                    <div class="flex justify-between text-sm text-[#967ED5] mb-1">
                                        <span>Stock Crítico</span>
                                        <span class="font-medium" id="stock-critico-label">3 productos</span>
                                    </div>
                                    <div class="w-full bg-[#E9C89A] h-2 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#967ED5]" id="stock-critico-bar" style="width: 30%"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Indicador de salud -->
                            <div class="flex flex-col items-center justify-center lg:ml-6">
                                <div id="salud-stock" class="w-24 h-24 border-4 border-[#967ED5] rounded-full flex items-center justify-center text-xl font-bold text-[#4E5D83] shadow-inner">
                                    57%
                                </div>
                                <span class="mt-2 text-sm text-[#4E5D83]">Salud del Stock</span>
                            </div>
                        </div>
                    </div>


                    <!-- Alertas Críticas Card -->
                    <div class="bg-[#E9C89A] rounded-lg shadow-sm p-6 border border-[#BFBCE9]">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="bg-white text-[#4E5D83] p-3 rounded-full">
                                <i class="fas fa-exclamation-triangle text-lg"></i>
                            </div>
                            <h2 class="text-xl font-semibold text-[#4E5D83]">Alertas Críticas</h2>
                        </div>

                        <!-- Alerta 1 -->
                        <div class="bg-white rounded-lg p-4 mb-4 flex items-center justify-between border border-[#BFBCE9]">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-exclamation-circle text-[#967ED5] mt-1"></i>
                                <div>
                                    <p class="font-medium text-[#4E5D83]">Stock Crítico (3 productos)</p>
                                    <p class="text-sm text-gray-600">Tomates Cherry</p>
                                    <p class="text-xs text-gray-500">2 kg restantes</p>
                                    <p class="text-xs text-gray-500">Verduras</p>
                                </div>
                            </div>
                            <button class="text-gray-500 hover:text-[#4E5D83] btn-alerta"
                                data-titulo="Stock Crítico (3 productos)"
                                data-categoria="Verduras"
                                data-producto="Tomates Cherry"
                                data-stock="2 kg restantes">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <!-- Alerta 2 -->
                        <div class="bg-white rounded-lg p-4 mb-4 flex items-center justify-between border border-[#BFBCE9]">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-exclamation-circle text-[#967ED5] mt-1"></i>
                                <div>
                                    <p class="font-medium text-[#4E5D83]">Vino Tinto Reserva</p>
                                    <p class="text-xs text-gray-500">1 botella restante</p>
                                    <p class="text-xs text-gray-500">Bebidas</p>
                                </div>
                            </div>
                            <button class="text-gray-500 hover:text-[#4E5D83]">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <!-- Alerta 3 -->
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between border border-[#BFBCE9]">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-exclamation-circle text-[#967ED5] mt-1"></i>
                                <div>
                                    <p class="font-medium text-[#4E5D83]">Leche Fresca</p>
                                    <p class="text-xs text-gray-500">4 litros restantes</p>
                                    <p class="text-xs text-gray-500">Lácteos</p>
                                </div>
                            </div>
                            <button class="text-gray-500 hover:text-[#4E5D83]">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Modal de Alerta Crítica -->
                <div id="modal-alerta" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white text-black p-6 rounded-xl shadow-xl w-full max-w-md relative">
                        <button id="cerrar-alerta" class="absolute top-3 right-3 text-gray-600 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                        <h3 id="modal-titulo" class="text-2xl font-semibold mb-2 text-[#4E5D83]"></h3>
                        <p id="modal-detalle-categoria" class="text-sm text-gray-700 mb-1"></p>
                        <p id="modal-detalle-stock" class="text-sm text-gray-700 mb-4"></p>
                        <button id="accion-modal" class="mt-2 bg-[#967ED5] text-white px-4 py-2 rounded hover:bg-[#BFBCE9]">
                            Marcar como resuelta
                        </button>
                    </div>
                </div>

                </main>
                <!-- Scripts -->
                <script src="https://cdn.tailwindcss.com"></script>
                <script src="https://unpkg.com/lucide@0.258.0"></script>
                <script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
                <script src="../public/js/metricas_view.js"></script>
                <script src="../public/js/time.js"></script>
                <script src="../public/js/inventario.js"></script>
                <script>
                    lucide.createIcons();
                </script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>

</html>