<?php
session_start();
include '../../conexion.php';

if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.php");
    exit();
}

$numeroMesa = $_POST['mesa'] ?? null;

if (!$numeroMesa) {
    echo "error";
    exit();
}

// Obtener IdMesa real
$sqlMesa = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
$sqlMesa->bind_param("i", $numeroMesa);
$sqlMesa->execute();
$resMesa = $sqlMesa->get_result();

if ($resMesa->num_rows === 0) {
    echo "mesa_no_encontrada";
    exit();
}

$idMesa = $resMesa->fetch_assoc()['IdMesa'];

// Verificar si hay orden pendiente
$sqlOrden = $conn->prepare("
    SELECT IdOrden 
    FROM Orden 
    WHERE IdMesa = ? AND Estado = 'Pendiente'
    LIMIT 1
");
$sqlOrden->bind_param("i", $idMesa);
$sqlOrden->execute();
$resOrden = $sqlOrden->get_result();

echo ($resOrden->num_rows > 0) ? "tiene_orden" : "sin_orden";
