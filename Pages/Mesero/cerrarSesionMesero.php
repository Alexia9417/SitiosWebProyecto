<?php
include '../../conexion.php';
session_start();

if (isset($_SESSION['IdUsuario']) && $_SESSION['IdTipoUsuario'] == 2) { // 2 = Mesero
    $idMesero = $_SESSION['IdUsuario'];

    $sqlUpdate = "UPDATE RegistroHoras 
                  SET HoraSalida = NOW() 
                  WHERE IdMesero = ? AND HoraSalida IS NULL";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $idMesero);
    $stmtUpdate->execute();

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
}
?>
