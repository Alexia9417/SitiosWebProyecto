<?php
// Iniciar sesión
session_start();

// Verificar si la sesión está iniciada y el rol es correcto
if (!isset($_SESSION['IdUsuario']) || $_SESSION['IdTipoUsuario'] != 2) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Incluir el archivo de conexión
include '../../conexion.php';

// Obtener el ID de la tarea desde la solicitud
$tareaId = $_POST['tarea_id'];

// Verificar que $tareaId tenga un valor válido
if ($tareaId === null) {
    echo json_encode(['success' => false, 'message' => 'ID de tarea es NULL']);
    exit();
}

// Actualizar el estado de la tarea en la base de datos
$sql = "UPDATE tareas SET Estado = 'Completada' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tareaId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la tarea']);
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
