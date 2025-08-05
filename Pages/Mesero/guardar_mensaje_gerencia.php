<?php
include '../../conexion.php';

$idMesero = $_POST['idMesero'];
$mensaje = $_POST['mensaje'];
$esLlamada = $_POST['esLlamada'];

$sql = "INSERT INTO MensajeGerencia (idMesero, Mensaje, LlamadaGerencia, FechaHora) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $idMesero, $mensaje, $esLlamada);

if ($stmt->execute()) {
    echo "Mensaje guardado correctamente";
} else {
    echo "Error al guardar el mensaje: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
