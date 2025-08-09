<?php
session_start()
?>
<!DOCTYPE html>
<html lang="es" class="bg-gray-50 text-gray-800">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
    <style>
        [aria-hidden="true"] {
            display: none;
        }
    </style>
</head>

<body class="bg-[#000] text-white font-sans leading-relaxed min-h-screen flex flex-col">

    <!-- Header arriba -->
    <header class="bg-[#dcc093] flex flex-col md:flex-row md:justify-between md:items-center border-b border-[#BFBCE9] mb-4 px-6 py-2 gap-4">
        <?php include 'views/templates/header.php'; ?>
    </header>

    <!-- Contenedor principal -->
    <div class="page-wrapper flex w-full flex-1">

        <!-- Main -->
        <div class="flex-1 max-w-[calc(100%-250px)] mx-auto px-6 overflow-y-auto">


            <!-- Métricas -->
            <div id="contenedor-kpis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>

            <!-- Sidebar -->
            <div class="w-full max-w-6xl mx-auto m-6">
                <?php include 'views/templates/nav-index.php'; ?>
            </div>

            <!-- Contenedor completo -->
            <section class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-[0.5fr_2fr] gap-6">

                    <!-- Columna 1: Resumen (más estrecha) -->
                    <div class="bg-[#324064] rounded-2xl space-y-4">
                        <h3 class="text-xl font-bold p-4">Resumen</h3>
                        <div class="grid grid-cols-1 gap-4 text-white m-5">
                            <!-- Pendientes -->
                            <div id="card-pendientes" class="bg-[#E9C89A] text-[#4E5D83] p-4 rounded-xl text-center shadow">
                                <div class="flex justify-center mb-2">
                                    <i data-lucide="clock" class="w-8 h-8"></i>
                                </div>
                                <div class="text-2xl font-bold">--</div>
                                <div>Pendientes</div>
                            </div>

                            <!-- Listo -->
                            <div id="card-listo" class="bg-[#BFBCE9] text-[#4E5D83] p-4 rounded-xl text-center shadow">
                                <div class="flex justify-center mb-2">
                                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                                </div>
                                <div class="text-2xl font-bold">--</div>
                                <div>Listo</div>
                            </div>

                            <!-- Total -->
                            <div id="card-total" class="bg-[#967ED5] text-white p-4 rounded-xl text-center shadow">
                                <div class="flex justify-center mb-2">
                                    <i data-lucide="layers" class="w-8 h-8"></i>
                                </div>
                                <div class="text-2xl font-bold">--</div>
                                <div>Total</div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Órdenes Activas (más ancha) -->
                    <div>
                        <section class="bg-[#4E5D83] text-white rounded-2xl shadow-lg p-6">
                            <h3 class="text-2xl font-bold tracking-wide mb-4">Órdenes Activas</h3>

                            <div class="overflow-x-auto rounded-lg border border-[#BFBCE9] bg-white">
                                <table class="min-w-full text-sm text-left text-black">
                                    <thead class="bg-[#E9C89A] text-[#4E5D83] uppercase text-xs font-semibold sticky top-0 z-10">
                                        <tr>
                                            <th class="px-6 py-3">N° Orden</th>
                                            <th class="px-6 py-3">N° Mesa</th>
                                            <th class="px-6 py-3">Hora de Llegada</th>
                                            <th class="px-6 py-3">Estado</th>
                                            <th class="px-6 py-3">Tiempo Estimado</th>
                                            <th class="px-6 py-3 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaOrdenes" class="divide-y divide-[#F5DDD3]">
                                        <!-- Contenido dinámico aquí -->
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                </div>
            </section>




        </div>

        <!-- Modal MicroModal mejorado -->
        <div class="modal micromodal-slide" id="modal-orden" aria-hidden="true">
            <div
                class="modal__overlay fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300"
                tabindex="-1"
                data-micromodal-close>
                <div
                    class="modal__container bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 transform transition-transform duration-300 ease-out"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="modal-orden-title">
                    <!-- HEADER -->
                    <header class="modal__header flex items-center justify-between p-6 border-b border-gray-200">
                        <h2 id="modal-orden-title" class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-6 h-6 text-indigo-600"></i>
                            Detalle de la Orden
                        </h2>
                        <button
                            class="modal__close-btn text-gray-400 hover:text-gray-700 transition-colors"
                            aria-label="Cerrar ventana"
                            data-micromodal-close>
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </header>

                    <!-- CONTENT -->
                    <main id="modalBody" class="modal__content px-6 py-4 space-y-4 text-gray-700">
                        <!-- Ejemplo de contenido dinámico -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-medium">N° Orden:</p>
                                <p id="orden-id" class="text-gray-900">--</p>
                            </div>
                            <div>
                                <p class="font-medium">Mesa:</p>
                                <p id="orden-mesa" class="text-gray-900">--</p>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium">Platos:</p>
                            <ul id="orden-platos" class="list-disc list-inside space-y-1">
                                <!-- items dinámicos -->
                            </ul>
                        </div>
                        <div class="flex flex-col">
                            <p class="font-medium">Notas:</p>
                            <textarea
                                id="orden-notas"
                                class="mt-1 resize-none border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                rows="3"
                                placeholder="Sin notas adicionales"
                                readonly></textarea>
                        </div>
                    </main>

                    <!-- FOOTER -->
                    <footer class="modal__footer flex items-center justify-end p-6 border-t border-gray-200 gap-3">
                        <button
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                            data-micromodal-close>
                            Cerrar
                        </button>
                    </footer>
                </div>
            </div>
        </div>



        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
        <script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
        <script src="public/JS/metricas.js"></script>
        <script src="public/JS/ordenes.js"></script>
        <script src="public/JS/app.js"></script>

        <script>
            lucide.createIcons();
            MicroModal.init({
                disableScroll: true,
                awaitOpenAnimation: false,
                awaitCloseAnimation: false,
                disableFocus: true
            });
        </script>
</body>

</html>