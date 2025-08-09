<?php
session_start();
include '../../conexion.php';

// Para debug - mostrar errores PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['IdUsuario'])) {
    echo "error_sesion";
    exit();
}

$IdUsuario = $_SESSION['IdUsuario'];
$numeroMesa = $_POST['mesa'] ?? null;

if (!$numeroMesa) {
    echo "error_parametro";
    exit();
}

$sqlMesa = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
if (!$sqlMesa) {
    echo "error_preparar_sqlMesa: " . $conn->error;
    exit();
}
$sqlMesa->bind_param("i", $numeroMesa);
if (!$sqlMesa->execute()) {
    echo "error_ejecutar_sqlMesa: " . $sqlMesa->error;
    exit();
}
$resMesa = $sqlMesa->get_result();

if ($resMesa->num_rows === 0) {
    echo "mesa_no_encontrada";
    exit();
}

$idMesa = $resMesa->fetch_assoc()['IdMesa'];

// Preparar y ejecutar consulta orden (solo una vez)
$sqlOrden = $conn->prepare("
    SELECT o.IdOrden, SUM(d.Cantidad * p.Precio) AS Total
    FROM Orden o
    JOIN detalle_orden d ON o.IdOrden = d.IdOrden
    JOIN Platillo p ON d.IdPlatillo = p.IdPlatillo
    WHERE o.IdMesa = ? AND o.Estado = 'Pendiente'
    GROUP BY o.IdOrden
    LIMIT 1
");
if (!$sqlOrden) {
    echo "error_preparar_sqlOrden: " . $conn->error;
    exit();
}
$sqlOrden->bind_param("i", $idMesa);
if (!$sqlOrden->execute()) {
    echo "error_ejecutar_sqlOrden: " . $sqlOrden->error;
    exit();
}
$resOrden = $sqlOrden->get_result();

if ($resOrden->num_rows > 0) {
    $orden = $resOrden->fetch_assoc();
    $idOrden = $orden['IdOrden'];
    $montoDeuda = $orden['Total'];

    $sqlDeuda = $conn->prepare("INSERT INTO Deudas (IdUsuario, Deuda, Fecha) VALUES (?, ?, NOW())");
    if (!$sqlDeuda) {
        echo "error_preparar_sqlDeuda: " . $conn->error;
        exit();
    }
    $sqlDeuda->bind_param("id", $IdUsuario, $montoDeuda);
    if (!$sqlDeuda->execute()) {
        echo "error_insertar_deuda: " . $sqlDeuda->error;
        exit();
    }

    // Borra los detalles de la orden (nota: tabla 'detalle_orden', minúsculas)
    $sqlDetalle = $conn->prepare("DELETE FROM detalle_orden WHERE IdOrden = ?");
    if (!$sqlDetalle) {
        echo "error_preparar_sqlDetalle: " . $conn->error;
        exit();
    }
    $sqlDetalle->bind_param("i", $idOrden);
    if (!$sqlDetalle->execute()) {
        echo "error_eliminar_detalle: " . $sqlDetalle->error;
        exit();
    }

    // Borra la orden
    $sqlEliminarOrden = $conn->prepare("DELETE FROM Orden WHERE IdOrden = ?");
    if (!$sqlEliminarOrden) {
        echo "error_preparar_sqlEliminarOrden: " . $conn->error;
        exit();
    }

    $sqlEliminarOrden->bind_param("i", $idOrden);
    if (!$sqlEliminarOrden->execute()) {
        echo "error_eliminar_orden: " . $sqlEliminarOrden->error;
        exit();
    }

    echo "deuda_guardada";
} else {
    echo "sin_deuda";
}

$sqlEliminarPago = $conn->prepare("DELETE FROM pagos WHERE IdMesa = ?");
$sqlEliminarPago->bind_param("i", $idMesa);
$sqlEliminarPago->execute();
$conn->close();