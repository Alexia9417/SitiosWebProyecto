<?php
session_start();
include '../../conexion.php';

// Verificar si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    echo "Error: Sesión no iniciada.";
    exit();
}

// Obtener el tipo de notificación
$tipo = $_POST['tipo'] ?? '';

// Obtener el número de mesa desde la solicitud
$mesaNumero = $_POST['mesa'] ?? null;

// Obtener el ID del usuario desde la sesión
$usuarioId = $_SESSION['IdUsuario'];

// Verificar que los datos necesarios estén presentes
if (empty($tipo) || $mesaNumero === null || $usuarioId === null) {
    echo "Error: Datos insuficientes para guardar la notificación.";
    exit();
}

// Obtener el IdMesa basado en el número de mesa
$stmtMesa = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
$stmtMesa->bind_param("i", $mesaNumero);
$stmtMesa->execute();
$resultMesa = $stmtMesa->get_result();

if ($resultMesa->num_rows === 0) {
    echo "Error: El número de mesa proporcionado no existe.";
    exit();
}

$mesa = $resultMesa->fetch_assoc();
$mesaId = $mesa['IdMesa'];

// Preparar la consulta para insertar la notificación
$stmt = $conn->prepare("INSERT INTO Notificacion (Hora, Descripcion, AvisoTipo, IdMesa, IdUsuario) VALUES (NOW(), ?, ?, ?, ?)");
$stmt->bind_param("ssii", $tipo, $tipo, $mesaId, $usuarioId);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo "Notificación enviada con éxito.";
} else {
    echo "Error al guardar la notificación: " . $stmt->error;
}

// Cerrar la conexión
$stmt->close();
$stmtMesa->close();
$conn->close();
?>
