<?php
header('Content-Type: application/json');
require_once '../conexion.php'; // instancia PDO $pdo

try {
    // Verifica si se pasa IdTipoUsuario como parámetro GET o POST
    $tipo = isset($_GET['tipo']) ? intval($_GET['tipo']) : (isset($_POST['tipo']) ? intval($_POST['tipo']) : null);

    if ($tipo !== null) {
        $stmt = $pdo->prepare("SELECT * FROM vw_empleados_detalle WHERE IdTipoUsuario = ?");
        $stmt->execute([$tipo]);
    } else {
        $stmt = $pdo->query("SELECT * FROM vw_empleados_detalle");
    }

    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'datos' => $empleados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
