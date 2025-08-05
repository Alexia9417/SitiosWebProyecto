<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es" class="bg-gray-50 text-gray-800">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chef Dashboard - Gestión de Estaciones</title>
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

        /* Cuando #bloqueo-overlay NO está oculto, deshabilitamos el pointer‑events en el modal */
        #bloqueo-overlay:not(.hidden)~.modal {
            pointer-events: none;
            /* evita clicks en el modal */
        }

        #bloqueo-overlay:not(.hidden) {
            /* además evitamos cerrar con Esc */
            pointer-events: all;
        }
    </style>
</head>

<body class="bg-[#000] text-white font-sans leading-relaxed flex min-h-screen">
    <div class="page-wrapper flex w-full">

        <!-- Sidebar -->
        <?php include 'templates/nav.php'; ?>

        <!-- Main -->
        <div class="flex-1 max-w-[calc(100%-250px)] mx-auto p-6 overflow-y-auto">
            <!-- Header -->
            <header class="flex flex-col md:flex-row md:justify-between md:items-center pb-5 border-b border-[#BFBCE9] mb-5">
                <div class="flex flex-wrap gap-4 text-sm">
                    <div class="w-16 h-16 bg-[#BFBCE9] text-[#4E5D83] flex items-center justify-center rounded-full">
                        <i data-lucide="user"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold" id="chef-nombre">Cargando...</h2>
                        <p class="text-sm text-[#F5DDD3]">Chef</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4 text-sm">
                    <div class="flex items-center gap-2 bg-white text-black px-4 py-2 rounded-lg shadow">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        <span id="current-date">--</span>
                    </div>
                    <div class="flex items-center gap-2 bg-green-50 text-green-600 font-semibold px-4 py-2 rounded-lg shadow">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <span id="current-time">--:--:--</span>
                    </div>
                </div>
            </header>

            <!-- Métricas -->
            <!-- Métricas -->
            <div id="contenedor-kpis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"></div>



            <div class="dashboard-sections grid grid-cols-1 md:grid-cols-2 gap-6 my-3">

                <!-- Solicitudes Rápidas -->
                <div id="contenedor-solicitudes"></div>


                <!-- Historial de Solicitudes -->
                <section class="bg-[#F5DDD3] rounded-xl shadow p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-bold text-[#4E5D83]">Historial de Solicitudes</h2>
                    </div>

                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 border-b pb-3 border-[#BFBCE9]">
                            <i data-lucide="clock" class="text-[#E9C89A] w-6 h-6"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium">Solicitud de ayudante adicional</p>
                                <small class="text-xs text-[#4E5D83]">2024-01-15 • 14:30</small>
                            </div>
                            <div class="flex flex-col items-end gap-1 text-xs">
                                <span class="bg-[#E9C89A] text-black px-2 py-0.5 rounded">Pendiente</span>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Acciones de Emergencia -->
            <section class="bg-[#F5DDD3] rounded-xl shadow p-6 space-y-4" id="contenedor-emergencias">
            </section>
        </div>

        <!-- Modal MicroModal compatible -->
        <div class="modal micromodal-slide" id="modal-solicitud" aria-hidden="true">
            <div class="modal__overlay bg-black/50 backdrop-blur-sm fixed inset-0 z-50 flex items-center justify-center" tabindex="-1" data-micromodal-close>
                <div class="modal__container bg-white rounded-2xl p-6 max-w-md w-full shadow-xl" role="dialog" aria-modal="true" aria-labelledby="modal-solicitud-title">
                    <header class="modal__header flex justify-between items-center mb-4">
                        <h2 class="modal__title text-xl font-semibold text-gray-800" id="modal-title">
                            Detalle de la Solicitud
                        </h2>
                        <button class="modal__close text-gray-500 hover:text-gray-800" aria-label="Cerrar" data-micromodal-close>&times;</button>
                    </header>
                    <main class="modal_content text-sm text-gray-700 space-y-2" id="modal-content">
                        <!-- contenido dinámico aquí -->
                    </main>

                    <footer class="modal__footer mt-6 text-right">
                        <button data-micromodal-close class="modal__btn px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button id="btn-iniciar-solicitud" class="modal__btn px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Ejecutar Acción
                        </button>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Overlay bloqueante -->
        <!-- Overlay bloqueante -->
        <div id="bloqueo-overlay" class="hidden fixed inset-0 z-60 bg-black bg-opacity-75 flex flex-col justify-center items-center">
            <img src="/Sitios/Chef/public/images/procesando.gif" alt="Procesando..." class="mb-6 w-32 h-32" />
            <p class="text-white text-2xl mb-2">Procesando Accion...</p>
            <span id="contador-overlay" class="text-white text-4xl font-mono">--:--</span>
        </div>


        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@0.258.0"></script>
        <script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
        <script src="../public/js/metricas_view.js"></script>
        <script src="../public/js/solicitude.js"></script>
        <script src="../public/js/solicitud_emergencia.js"></script>
        <script src="../public/js/time.js"></script>
        <script>
            MicroModal.init({
                disableScroll: true,
                awaitOpenAnimation: false,
                awaitCloseAnimation: false,
                disableFocus: true
            });
        </script>
</body>

</html>