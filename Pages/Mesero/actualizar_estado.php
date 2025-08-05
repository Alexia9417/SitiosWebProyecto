<?php
session_start();
include '../../conexion.php';

if (!isset($_SESSION['IdUsuario']) || $_SESSION['IdTipoUsuario'] != 2) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$idMesero = $_SESSION['IdUsuario'];
$estres = $_POST['estres'];
$energia = $_POST['energia'];
$eficiencia = $_POST['eficiencia'];

// Actualizar los valores en la base de datos
$sqlUpdate = "UPDATE EstadoMesero SET Estres = Estres + ?, Energia = Energia + ?, Eficiencia = Eficiencia + ?, UltimaActualizacion = NOW() WHERE IdMesero = ?";
$stmt = $conn->prepare($sqlUpdate);
$stmt->bind_param("iiii", $estres, $energia, $eficiencia, $idMesero);
$success = $stmt->execute();

// Obtener los valores actualizados
$sqlSelect = "SELECT Estres, Energia, Eficiencia FROM EstadoMesero WHERE IdMesero = ?";
$stmt = $conn->prepare($sqlSelect);
$stmt->bind_param("i", $idMesero);
$stmt->execute();
$result = $stmt->get_result();
$updatedValues = $result->fetch_assoc();

$stmt->close();
$conn->close();

echo json_encode(['success' => $success, 'estres' => $updatedValues['Estres'], 'energia' => $updatedValues['Energia'], 'eficiencia' => $updatedValues['Eficiencia']]);
?>
