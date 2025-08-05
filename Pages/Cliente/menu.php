<?php
include '../../conexion.php'; // Archivo que conecta a la BD

$mesa = $_GET['mesa'] ?? 1; // número de mesa

// Obtener las categorías y sus platillos
$queryCategorias = "SELECT * FROM Categoria";
$resultCategorias = mysqli_query($conn, $queryCategorias);
?>

<div id="menu-restaurante">
    <?php while ($cat = mysqli_fetch_assoc($resultCategorias)) : ?>
        <div class="categoria-bloque">
            <h3><?= htmlspecialchars($cat['Nombre']) ?></h3>
            <?php
            $idCat = $cat['IdCategoria'];
            $queryPlatillos = "SELECT * FROM Platillo WHERE IdCategoria = $idCat";
            $resultPlatillos = mysqli_query($conn, $queryPlatillos);
            ?>

            <div class="platillos">
                <?php while ($plat = mysqli_fetch_assoc($resultPlatillos)) : ?>
                    <div class="platillo-item" data-id="<?= $plat['IdPlatillo'] ?>" data-nombre="<?= $plat['Nombre'] ?>" data-precio="<?= $plat['Precio'] ?>">
                        <p><strong><?= $plat['Nombre'] ?></strong></p>
                        <p>₡<?= number_format($plat['Precio'], 2) ?></p>
                        <div class="contador">
                            <button class="btn-restar">-</button>
                            <span class="cantidad">0</span>
                            <button class="btn-sumar">+</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>
