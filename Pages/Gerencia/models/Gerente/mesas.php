<?php
header('Content-Type: application/json');
require_once '../conexion.php'; // Asegúrate de tener una instancia válida de $pdo

try {
    // Ejecutar la consulta
    $stmt = $pdo->query("
        SELECT 
    m.Numero, 
    m.MesaEstado, 
    a.Nombre       AS AreaNombre,
    m.IdMesero,
    COALESCE(CONCAT(u.Nombre, ' ', u.Apellidos), '') AS Nombre
FROM 
    mesa m
INNER JOIN 
    area a ON m.IdArea = a.IdArea
LEFT JOIN 
    usuario u ON m.IdMesero = u.IdUsuario;");

    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar como JSON
    echo json_encode([
        'success' => true,
        'datos' => $mesas
    ]);
} catch (PDOException $e) {
    // Manejar errores
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
