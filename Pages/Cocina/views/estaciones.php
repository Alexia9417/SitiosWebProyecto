<?php
session_start();
require '../models/conexion.php';   // tu PDO en $conn

try {

    // Consulta de estaciones
    $stmt = $pdo->query("SELECT * 
FROM view_estaciones_por_chef");
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
            <div class="dashboard-sections grid grid-cols-1 gap-6 my-3">

                <!-- Estaciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                    <?php foreach ($estaciones as $estacion): ?>
                        <div
                            class="station-card bg-[#4E5D83] text-white rounded-xl shadow-md p-4 space-y-4 cursor-pointer"
                            data-id="<?= $estacion['estacion_id'] ?>"
                            onclick="mostrarDetalleEstacion(<?= $estacion['estacion_id'] ?>)">

                            <div class="flex justify-between items-center">
                                <h3 class="font-bold text-lg text-white"><?= htmlspecialchars($estacion['nombre_estacion']) ?></h3>
                                <span class="bg-[#E9C89A] text-black font-bold px-2 py-1 rounded-full text-xs">Disponible</span>
                            </div>

                            <div class="flex justify-between text-sm text-gray-700">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-thermometer-half"></i> <span>180°C</span>
                                </div>
                                <div class="flex text-white items-center gap-1">
                                    <i class="fas fa-utensils"></i> <?= $estacion['slots'] ?> slots libres
                                </div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-white mb-1">Cola de platillos:</div>
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