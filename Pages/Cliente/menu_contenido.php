<?php
include '../../conexion.php'; // Archivo que conecta a la BD

$mesa = $_GET['mesa'] ?? 1; // número de mesa (si necesitas usarlo después)

header('Content-Type: text/html; charset=UTF-8');

// Obtener las categorías y sus platillos
$queryCategorias = "SELECT * FROM Categoria ORDER BY Nombre";
$resultCategorias = mysqli_query($conn, $queryCategorias);
?>

<div id="menu-restaurante">
    <?php while ($cat = mysqli_fetch_assoc($resultCategorias)) : ?>
        <div class="categoria-bloque">
            <h3><?= htmlspecialchars($cat['Nombre']) ?></h3>

            <?php
            $idCat = (int)$cat['IdCategoria'];
            $queryPlatillos = "SELECT * FROM Platillo WHERE IdCategoria = $idCat ORDER BY Nombre";
            $resultPlatillos = mysqli_query($conn, $queryPlatillos);
            ?>

            <div class="platillos">
                <?php while ($plat = mysqli_fetch_assoc($resultPlatillos)) : ?>
                    <div class="platillo-item" 
                         data-id="<?= (int)$plat['IdPlatillo'] ?>" 
                         data-nombre="<?= htmlspecialchars($plat['Nombre']) ?>" 
                         data-precio="<?= number_format($plat['Precio'], 2, '.', '') ?>">
                        <p><strong><?= htmlspecialchars($plat['Nombre']) ?></strong></p>
                        <p>₡<?= number_format($plat['Precio'], 2) ?></p>
                        <div class="contador">
                            <button class="btn-restar" type="button">-</button>
                            <span class="cantidad">0</span>
                            <button class="btn-sumar" type="button">+</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>
