<?php
session_start();
require '../models/conexion.php';   // tu PDO en $conn

try {

    // Consulta de estaciones
    $stmt = $pdo->query("SELECT * 
FROM view_estaciones_por_chef
WHERE chef_id = 1");
    $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
    exit;
}
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



            <!-- Estaciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                <?php foreach ($estaciones as $estacion): ?>
                    <div
                        class="station-card bg-[#F5DDD3] text-gray-900 rounded-xl shadow-md p-4 space-y-4 cursor-pointer"
                        data-id="<?= $estacion['estacion_id'] ?>"
                        onclick="mostrarDetalleEstacion(<?= $estacion['estacion_id'] ?>)">

                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-lg text-[#4E5D83]"><?= htmlspecialchars($estacion['nombre_estacion']) ?></h3>
                            <span class="bg-[#E9C89A] text-black px-2 py-1 rounded-full text-xs">Disponible</span>
                        </div>

                        <div class="flex justify-between text-sm text-gray-700">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-thermometer-half"></i> <span>180°C</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-utensils"></i> <?= $estacion['slots'] ?> slots libres
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-800 mb-1">Cola de platillos:</div>
                            <ul class="list-disc list-inside text-sm text-gray-700">
                                <li>Sin platillos</li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

            </main>


            <!-- Modal MicroModal compatible -->
            <div class="modal micromodal-slide" id="modal-orden" aria-hidden="true">
                <div class="modal__overlay bg-black/50 backdrop-blur-sm fixed inset-0 z-50 flex items-center justify-center" tabindex="-1" data-micromodal-close>
                    <div class="modal__container bg-white rounded-2xl p-6 max-w-md w-full shadow-xl" role="dialog" aria-modal="true" aria-labelledby="modal-orden-title">
                        <header class="modal__header flex justify-between items-center mb-4">
                            <h2 class="modal__title text-xl font-semibold text-gray-800" id="modal-orden-title">
                                Platillos de la Estación
                            </h2>
                            <button class="modal__close text-gray-500 hover:text-gray-800" aria-label="Cerrar" data-micromodal-close>&times;</button>
                        </header>
                        <main class="modal__content text-sm text-gray-700 space-y-2" id="modalBody">
                            <!-- contenido dinámico aquí -->
                        </main>
                        <footer class="modal__footer mt-6 text-right">
                            <button class="modal__btn px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700" data-micromodal-close>
                                Cerrar
                            </button>
                        </footer>
                    </div>
                </div>
            </div>

            <!-- Scripts -->
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@0.258.0"></script>
            <script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
            <script src="../public/js/metricas_view.js"></script>
            <script src="../public/JS/estacion.js"></script>
            <script src="../public/JS/time.js"></script>

</body>

</html>