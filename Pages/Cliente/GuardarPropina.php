<?php
session_start();
include '../../conexion.php';

if (!isset($_SESSION['IdUsuario'])) {
    echo "Debe iniciar sesión para dejar una propina.";
    header("Location: ../sitio/login.php");
    exit();
}

$IdUsuario = $_SESSION['IdUsuario'];
$montoPropina = isset($_POST['montoPropina']) ? floatval($_POST['montoPropina']) : 0;
$numeroMesa = isset($_POST['mesa']) ? intval($_POST['mesa']) : 0;

// Obtener el IdMesero de la mesa del usuario
$consultaMesa = $conn->prepare("SELECT IdMesero FROM Mesa WHERE Numero = ?");

$consultaMesa->bind_param("i", $numeroMesa);
$consultaMesa->execute();
$resMesa = $consultaMesa->get_result();

if ($resMesa->num_rows > 0) {
    $datosMesa = $resMesa->fetch_assoc();
    $IdMesero = $datosMesa['IdMesero'];
} else {
    die("No se encontró el mesero para la mesa número: " . htmlspecialchars($numeroMesa));
}

// Guardar la propina en la base de datos
$stmt = $conn->prepare("INSERT INTO AccionCliente (TipoAccion, IdUsuario, FechaHora, MontoPropina, IdMesero) VALUES (?, ?, NOW(), ?, ?)");

if ($stmt === false) {
    die('Error al preparar la consulta de inserción: ' . htmlspecialchars($conn->error));
}

$tipoAccion = 'Dejar Propina';
$stmt->bind_param("sidi", $tipoAccion, $IdUsuario, $montoPropina, $IdMesero);

if ($stmt->execute()) {
    echo "Propina registrada con éxito. ¡Gracias por su aporte!";
} else {
    echo "Error al registrar la propina: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
?>
