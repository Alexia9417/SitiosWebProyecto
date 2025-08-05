<?php
session_start();
include '../../conexion.php';
header('Content-Type: application/json');

// Habilitar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['IdUsuario'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no iniciada."]);
    exit();
}

if (!isset($_POST['total'], $_POST['mesa']) || !is_numeric($_POST['total']) || !is_numeric($_POST['mesa'])) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
    exit();
}

$IdUsuario = $_SESSION['IdUsuario'];
$total = (float)$_POST['total'];
$numeroMesa = (int)$_POST['mesa'];

$conn->begin_transaction();

try {
    // Obtener IdMesa
    $stmt = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
    $stmt->bind_param("i", $numeroMesa);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) throw new Exception("Mesa no encontrada.");
    $idMesa = $res->fetch_assoc()['IdMesa'];
    $stmt->close();

    // Obtener la última orden para esa mesa
    $stmt = $conn->prepare("SELECT IdOrden FROM Orden WHERE IdMesa = ? ORDER BY Fecha DESC LIMIT 1");
    $stmt->bind_param("i", $idMesa);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) throw new Exception("No se encontró ninguna orden para la mesa.");
    $IdOrden = $res->fetch_assoc()['IdOrden'];
    $stmt->close();

    // Obtener saldo
    $stmt = $conn->prepare("SELECT Saldo FROM MetodoPago WHERE IdUsuario = ?");
    $stmt->bind_param("i", $IdUsuario);
    $stmt->execute();
    $resp = $stmt->get_result();
    if ($resp->num_rows === 0) throw new Exception("No se encontró la tarjeta registrada.");
    $saldo = (float)$resp->fetch_assoc()['Saldo'];
    $stmt->close();

    // Obtener deuda del cliente
    $stmt = $conn->prepare("SELECT Deuda FROM deudas WHERE IdUsuario = ?");
    $stmt->bind_param("i", $IdUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $deuda = 0;
    if ($res->num_rows > 0) {
        $deuda = (float)$res->fetch_assoc()['Deuda'];
    }
    $stmt->close();

    // Sumar la deuda al total
    $totalConDeuda = $total + $deuda;

    // Validar saldo
    if ($saldo < $totalConDeuda) throw new Exception("No tiene saldo suficiente en la tarjeta.");

    // Actualizar saldo
    $nuevoSaldo = $saldo - $totalConDeuda;
    $stmt = $conn->prepare("UPDATE MetodoPago SET Saldo = ? WHERE IdUsuario = ?");
    $stmt->bind_param("di", $nuevoSaldo, $IdUsuario);
    if (!$stmt->execute()) throw new Exception("Error al actualizar saldo.");
    $stmt->close();

    // Eliminar detalles de la orden
    $stmtDO = $conn->prepare("DELETE FROM detalle_orden WHERE IdOrden = ?");
    $stmtDO->bind_param("i", $IdOrden);
    $stmtDO->execute();
    $eliminadosDetalles = $stmtDO->affected_rows;
    $stmtDO->close();

    // Eliminar la orden
    $stmtO = $conn->prepare("DELETE FROM Orden WHERE IdOrden = ?");
    $stmtO->bind_param("i", $IdOrden);
    $stmtO->execute();
    $eliminadosOrden = $stmtO->affected_rows;
    $stmtO->close();

    // Eliminar la deuda del cliente
    if ($deuda > 0) {
        $stmtD = $conn->prepare("DELETE FROM deudas WHERE IdUsuario = ?");
        $stmtD->bind_param("i", $IdUsuario);
        if (!$stmtD->execute()) {
            throw new Exception("Error al eliminar la deuda.");
        }
        $stmtD->close();
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Pago realizado con éxito.",
        "debug" => [
            "IdOrden" => $IdOrden,
            "detalles_eliminados" => $eliminadosDetalles,
            "orden_eliminada" => $eliminadosOrden,
            "deuda" => $deuda
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
