<?php
require_once 'conexion.php';

$error = ""; // Variable para almacenar errores

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['nombre'], $_POST['apellido'])) {

        $usuario = $_POST['email'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellido'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verificar que las contraseñas coincidan
        if ($password !== $confirmPassword) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Hashear contraseña
            $contrasena = password_hash($password, PASSWORD_DEFAULT);
            $idTipoUsuario = 3; // Cliente

            // Preparar la consulta SQL
            $sql = "INSERT INTO usuario (Usuario, Contraseña, Nombre, Apellidos, IdTipoUsuario) VALUES (?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssi", $usuario, $contrasena, $nombre, $apellidos, $idTipoUsuario);

                if ($stmt->execute()) {
                    // Redirigir al login si todo salió bien
                    header("Location: Pages/sitio/login.html");
                    exit;
                } else {
                    // Error al insertar (posiblemente email duplicado)
                    if ($conn->errno === 1062) { // Código de error para clave duplicada en MySQL
                        $error = "Este correo electrónico ya está registrado.";
                    } else {
                        $error = "Error al registrar: " . $stmt->error;
                    }
                }

                $stmt->close();
            } else {
                $error = "Error en la preparación de la consulta: " . $conn->error;
            }

            $conn->close();
        }
    } else {
        $error = "Uno o más campos del formulario no están definidos.";
    }
}
?>
