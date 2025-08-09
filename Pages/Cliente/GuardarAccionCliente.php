<?php
include '../../conexion.php';

$tipo = $_POST['tipo'] ?? '';
$mesaId = $_GET['mesa'] ?? 1; // Aquí debes obtener el ID de la mesa actual
$usuarioId = 1; // Aquí debes obtener el ID del usuario actual

if (empty($tipo)) {
    echo "Error: Tipo de acción no especificado.";
    exit;
}

$stmt = $conn->prepare("INSERT INTO accioncliente (Hora, Descripcion, AvisoTipo, IdMesa, IdUsuario) VALUES (NOW(), ?, ?, ?, ?)");
$stmt->bind_param("ssii", $tipo, $tipo, $mesaId, $usuarioId);

if ($stmt->execute()) {
    echo "Acción guardada con éxito.";
} else {
    echo "Error al guardar la acción.";
}

$stmt->close();
$conn->close();
?>
