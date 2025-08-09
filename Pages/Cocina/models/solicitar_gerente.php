<?php
// solicitar_gerente.php

require 'conexion.php'; // Asegúrate que este archivo contiene la conexión PDO ($pdo)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idEmpleado   = isset($_POST['id_empleado']) ? intval($_POST['id_empleado']) : 0;
    $idAccionChef = isset($_POST['id_accion_chef']) ? intval($_POST['id_accion_chef']) : 0;
    $estado       = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if ($idEmpleado > 0 && $idAccionChef > 0 && $estado !== '') {
        try {
            $stmt = $pdo->prepare("CALL sp_registrar_mensaje_empleado(:idEmpleado, :idAccionChef, :estado)");
            $stmt->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
            $stmt->bindParam(':idAccionChef', $idAccionChef, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Mensaje registrado con éxito']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Parámetros incompletos o inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
