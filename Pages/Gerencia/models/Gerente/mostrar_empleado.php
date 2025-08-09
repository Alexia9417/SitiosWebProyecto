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
        $stmt = $pdo->query("SELECT 
    me.IdMensaje,
    vw.*,
    a.IdAccion, 
    a.Nombre AS Accion,
    a.Tipo,
    me.FechaHora,
    me.Estado
FROM `vw_empleados_detalle` AS vw
LEFT JOIN mensaje_empleado AS me ON vw.IdUsuario = me.IdEmpleado  
LEFT JOIN accion AS a ON me.IdAccion = a.IdAccion
WHERE vw.TipoUsuario IN ('Empleado', 'Chef', 'Mesero');

    ");
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
