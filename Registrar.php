<?php
require_once 'conexion.php';

$mensaje = ""; // Para mostrar en el HTML

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['nombre'], $_POST['apellido'])) {

        $usuario = $_POST['email'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellido'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($password !== $confirmPassword) {
            $mensaje = "Las contraseñas no coinciden.";
        } else {
            $contrasena = password_hash($password, PASSWORD_DEFAULT);
            $idTipoUsuario = 3;
            $codigo = rand(100000, 999999); // Código de verificación

            // --- Intentar enviar correo antes de insertar ---
            $asunto = "Código de verificación";
            $mensajeCorreo = "
                <html>
                <body>
                <p>¡Hola!</p>
                <p>Para completar tu registro, por favor verifica tu cuenta con el siguiente código:</p>
                <p>De parte del equipo de soporte,</p>
                <p>La Capuzzelle</p>
                <p>Hola <strong>$nombre</strong>,</p>
                <p>Tu código de verificación es: <strong>$codigo</strong></p>
                </body>
                </html>
            ";
            $cabeceras = "MIME-Version: 1.0\r\n";
            $cabeceras .= "Content-type:text/html;charset=UTF-8\r\n";
            $cabeceras .= "From: no-reply@tusitio.com\r\n";

            if (mail($usuario, $asunto, $mensajeCorreo, $cabeceras)) {
                // Correo enviado → ahora sí insertamos
                $sql = "INSERT INTO usuario (Usuario, Contraseña, Nombre, Apellidos, IdTipoUsuario, verificado, codigo_verificacion)
                        VALUES (?, ?, ?, ?, ?, 0, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssssii", $usuario, $contrasena, $nombre, $apellidos, $idTipoUsuario, $codigo);
                    if ($stmt->execute()) {
                        $mensaje = "Registro correcto. Revisa tu correo para el código.";
                    } else {
                        $mensaje = "Error al registrar: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $mensaje = "Error en la preparación de la consulta: " . $conn->error;
                }
            } else {
                // No se envió correo → no insertamos
                $mensaje = "No se pudo enviar el correo, el registro no se completó.";
            }

            $conn->close();
        }
    } else {
        $mensaje = "Faltan campos por completar.";
    }
}
?>
