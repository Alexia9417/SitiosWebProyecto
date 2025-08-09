<?php
session_start();
include '../../conexion.php';

// Verificar que haya usuario en sesión
if (!isset($_SESSION['IdUsuario'])) {
    http_response_code(401);
    echo "No autorizado";
    header("Location: ../sitio/login.php");
    exit;
}

$IdUsuario = $_SESSION['IdUsuario'];
$nombreTitular = $_POST['nombreTitular'] ?? '';
$numeroTarjeta = $_POST['numeroTarjeta'] ?? '';
$fechaVenc = $_POST['fechaVenc'] ?? '';

$fechaVenc = $_POST['fechaVenc'] ?? '';

if ($fechaVenc) {
    if (preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $fechaVenc)) {
        list($mes, $anio) = explode('/', $fechaVenc);
        $anioCompleto = 2000 + intval($anio);
        $fechaVenc = sprintf('%04d-%02d-01', $anioCompleto, $mes);
    } else {
        http_response_code(400);
        echo "Formato de fecha inválido. Use MM/AA";
        exit;
    }
}

// Validar datos mínimos (puedes extender validaciones)
if (!$nombreTitular || !$numeroTarjeta || !$fechaVenc) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

// Insertar en base de datos
$stmt = $conn->prepare("INSERT INTO MetodoPago (IdUsuario, NombreTitular, NumeroTarjeta, FechaVenc) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $IdUsuario, $nombreTitular, $numeroTarjeta, $fechaVenc);

if ($stmt->execute()) {
    echo "Tarjeta guardada con éxito";
} else {
    http_response_code(500);
    echo "Error al guardar la tarjeta";
}
?>
