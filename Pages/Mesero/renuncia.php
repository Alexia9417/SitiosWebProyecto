<?php
header('Content-Type: application/json');
session_start();
include '../../conexion.php';

$idMesero = $_SESSION['IdUsuario'] ?? null;

if ($idMesero) {
    $sql = "UPDATE Usuario SET IdTipoUsuario = 3 WHERE IdUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idMesero);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar rol."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No hay sesión activa."]);
}
