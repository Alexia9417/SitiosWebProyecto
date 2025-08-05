<?php
header('Content-Type: application/json');
include '../../conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data) || !isset($data['mesa']) || !isset($data['productos'])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit();
}

$mesa = intval($data['mesa']);
$productos = $data['productos'];
$fechaHora = date("Y-m-d H:i:s");

// 1. Obtener el IdMesa a partir del número de mesa
$stmtMesa = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
$stmtMesa->bind_param("i", $mesa);
$stmtMesa->execute();
$resultMesa = $stmtMesa->get_result();

if ($resultMesa->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Mesa no encontrada"]);
    exit();
}

$filaMesa = $resultMesa->fetch_assoc();
$idMesa = $filaMesa['IdMesa'];

// 2. Verificar si ya existe una orden activa para esta mesa
$stmtOrden = $conn->prepare("SELECT IdOrden FROM Orden WHERE IdMesa = ? AND Estado IN ('Pendiente', 'Confirmado') ORDER BY Fecha DESC LIMIT 1");
$stmtOrden->bind_param("i", $idMesa);
$stmtOrden->execute();
$resultOrden = $stmtOrden->get_result();

if ($resultOrden->num_rows > 0) {
    // Ya existe orden activa → usar esa
    $filaOrden = $resultOrden->fetch_assoc();
    $idOrden = $filaOrden['IdOrden'];
} else {
    // No existe → crear una nueva
    $sql = "INSERT INTO Orden (Fecha, IdMesa, Estado) VALUES (?, ?, 'Pendiente')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $fechaHora, $idMesa);
    $stmt->execute();
    $idOrden = $stmt->insert_id;
}

// 3. Insertar o actualizar detalles
$stmtDetalleSelect = $conn->prepare("SELECT Cantidad FROM detalle_orden WHERE IdOrden = ? AND IdPlatillo = ?");
$stmtDetalleInsert = $conn->prepare("INSERT INTO detalle_orden (IdOrden, IdPlatillo, Cantidad, IdEstadoPlatillo) VALUES (?, ?, ?, 2)");
$stmtDetalleUpdate = $conn->prepare("UPDATE detalle_orden SET Cantidad = ? WHERE IdOrden = ? AND IdPlatillo = ?");

foreach ($productos as $producto) {
    $idPlatillo = intval($producto['id']);
    $cantidad = intval($producto['cantidad']);

    if ($cantidad <= 0) continue;

    // Verificar si ya existe ese platillo en la orden
    $stmtDetalleSelect->bind_param("ii", $idOrden, $idPlatillo);
    $stmtDetalleSelect->execute();
    $res = $stmtDetalleSelect->get_result();

    if ($res->num_rows > 0) {
        // Actualizar sumando
        $fila = $res->fetch_assoc();
        $nuevaCantidad = $fila['Cantidad'] + $cantidad;
        $stmtDetalleUpdate->bind_param("iii", $nuevaCantidad, $idOrden, $idPlatillo);
        $stmtDetalleUpdate->execute();
    } else {
        // Insertar nuevo detalle
        $stmtDetalleInsert->bind_param("iii", $idOrden, $idPlatillo, $cantidad);
        $stmtDetalleInsert->execute();
    }
}

echo json_encode(["status" => "ok", "idOrden" => $idOrden]);
