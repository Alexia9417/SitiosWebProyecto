<?php
ob_clean(); // Elimina cualquier salida previa que pueda corromper el JSON
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'], $data['opcion'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

$ordenID = $data['id'];
$op = $data['opcion'];

try {
    include 'conexion.php';

    // Verificar que la orden exista
    $stmtCheck = $pdo->prepare("SELECT Estado FROM orden WHERE IdOrden = ?");
    $stmtCheck->execute([$ordenID]);
    $estado = $stmtCheck->fetchColumn();

    if (!$estado) {
        echo json_encode(["success" => false, "message" => "Orden no encontrada"]);
        exit;
    }

    if ($op === 'asignar') {
        if ($estado !== 'Pendiente') {
            echo json_encode(["success" => false, "message" => "La orden ya fue tomada por otro usuario"]);
            exit;
        }

        $stmt = $pdo->prepare("CALL sp_gestionar_orden_plato_estacion_simple(1, ?, 2)");
        $stmt->execute([$ordenID]);

        echo json_encode(["success" => true]);

        // …

    } elseif ($op === 'cocinar') {
        // 1) Verificar ocupación actual por estación
        $stmtEstaciones = $pdo->prepare("
        SELECT 
            e.IdEstacion,
            e.Espacios AS slots,
            COUNT(ope.IdOrdenPlatilloEstacion) AS ocupados
        FROM estacion e
        LEFT JOIN orden_plato_estacion ope
          ON ope.IdEstacion = e.IdEstacion
         AND ope.IdPlatoEstado = (
             SELECT IdPlatoEstado
             FROM estado_plato 
             WHERE nombre = 'Cocinando' 
             LIMIT 1
         )
        GROUP BY e.IdEstacion, e.Espacios
    ");
        $stmtEstaciones->execute();
        $ocupacion = [];
        while ($row = $stmtEstaciones->fetch(PDO::FETCH_ASSOC)) {
            $ocupacion[(int)$row['IdEstacion']] = (int)$row['ocupados'];
        }


        // 2) Determinar qué estaciones necesita esta orden
        $stmtEstacionesOrden = $pdo->prepare("
       SELECT DISTINCT mi.IdEstacion
        FROM detalle_orden do2
        JOIN platillo mi      ON do2.IdPlatillo = mi.IdPlatillo
        WHERE do2.IdOrden = ?
          AND do2.IdEstadoPlatillo = (
              SELECT IdEstadoPlatillo
              FROM estado_plato
              WHERE Nombre = 'Pendiente'
              LIMIT 1
          )
    ");
        $stmtEstacionesOrden->execute([$ordenID]);
        $estacionesRequeridas = $stmtEstacionesOrden->fetchAll(PDO::FETCH_COLUMN);

        // 3) Comprobar slots libres
        foreach ($estacionesRequeridas as $estacionID) {
            $limite   = isset($ocupacion[$estacionID]) ? (int)$ocupacion[$estacionID] : 0;
            $stmtSlots = $pdo->prepare("SELECT Espacios FROM estacion WHERE IdEstacion = ?");
            $stmtSlots->execute([$estacionID]);
            $slotsMax = (int)$stmtSlots->fetchColumn();

            if ($limite >= $slotsMax) {
                echo json_encode([
                    "success" => false,
                    "message" => "Estación $estacionID está llena, no se puede cocinar."
                ]);
                exit;
            }
        }

        // 4) Lanzar la cocción si hay espacio
        $stmt = $pdo->prepare("CALL sp_gestionar_orden_plato_estacion_simple(2, ?, 3)");
        $stmt->execute([$ordenID]);

        echo json_encode(["success" => true]);

        // …


    } else {
        echo json_encode(["success" => false, "message" => "Opción no válida"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error interno: " . $e->getMessage()]);
}
