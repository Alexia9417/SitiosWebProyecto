<?php
header('Content-Type: application/json');
require_once '../conexion.php'; // Instancia PDO en $pdo

// Validar parámetros
if (!isset($_POST['id_usuario'], $_POST['nuevo_rol'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Faltan parámetros requeridos (id_usuario, nuevo_rol)'
    ]);
    exit;
}

$idUsuario = intval($_POST['id_usuario']);
$nuevoRol = intval($_POST['nuevo_rol']);

try {
    $stmt = $pdo->prepare("CALL sp_cambiar_rol_suario(:id_usuario, :nuevo_rol)");
    $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':nuevo_rol', $nuevoRol, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Rol actualizado correctamente'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
