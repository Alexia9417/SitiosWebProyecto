<?php
require_once 'conexion.php';

if (isset($_POST['codigo'])) {
    $codigo = $_POST['codigo'];

    $sql = "SELECT idUsuario FROM usuario WHERE codigo_verificacion = ? AND verificado = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $codigo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($idUsuario);
        $stmt->fetch();

        $update = $conn->prepare("UPDATE usuario SET verificado = 1 WHERE idUsuario = ?");
        $update->bind_param("i", $idUsuario);
        $update->execute();

        echo "Código correcto. Cuenta verificada.";
    } else {
        echo "Código incorrecto o ya verificado.";
    }
}
?>
