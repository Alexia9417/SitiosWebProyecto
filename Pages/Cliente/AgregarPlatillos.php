<?php
include '../../conexion.php';

$idOrden = $_POST['IdOrden'];
$platillos = $_POST['platillos'] ?? [];
$cantidades = $_POST['cantidades'] ?? [];

// Valor predeterminado para IdEstadoPlatillo
$defaultIdEstadoPlatillo = 1; // Asegúrate de que este valor exista en la tabla estado_plato

foreach ($platillos as $idPlatillo) {
    $cantidad = $cantidades[$idPlatillo] ?? 1;
    $insert = $conn->prepare("INSERT INTO detalle_orden (IdOrden, IdPlatillo, Cantidad, IdEstadoPlatillo) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiii", $idOrden, $idPlatillo, $cantidad, $defaultIdEstadoPlatillo);
    $insert->execute();
}

// Redirigir de vuelta a MiOrden
$mesa = $_GET['mesa'] ?? 1;
header("Location: MiOrden.php?mesa=" . $mesa);
exit;
?>
