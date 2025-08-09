<?php
session_start();
include 'conexion.php';

// Verificar si se envió el formulario
$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

// Buscar el usuario por nombre de usuario
$sql = "SELECT IdUsuario, Nombre, Apellidos, IdTipoUsuario, Contraseña FROM usuario WHERE Usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuarioData = $result->fetch_assoc();
    $loginValido = false;

    // Verificar si la contraseña en BD parece hash (bcrypt = 60 caracteres)
    if (strlen($usuarioData['Contraseña']) < 60) {
        // Contraseña antigua en texto plano
        if ($contrasena === $usuarioData['Contraseña']) {
            $loginValido = true;

            // Migrar a hash automáticamente
            $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE usuario SET Contraseña = ? WHERE IdUsuario = ?");
            $update->bind_param("si", $nuevoHash, $usuarioData['IdUsuario']);
            $update->execute();
        }
    } else {
        // Contraseña ya en hash, verificar con password_verify
        if (password_verify($contrasena, $usuarioData['Contraseña'])) {
            $loginValido = true;
        }
    }

    if ($loginValido) {
        // Guardar datos en sesión
        $_SESSION['IdUsuario'] = $usuarioData['IdUsuario'];
        $_SESSION['Nombre'] = $usuarioData['Nombre'];
        $_SESSION['Apellidos'] = $usuarioData['Apellidos'];
        $_SESSION['IdTipoUsuario'] = $usuarioData['IdTipoUsuario'];

        // Redirigir según el rol
        switch ($usuarioData['IdTipoUsuario']) {
            case 1: // Gerencia
                header("Location: Pages/Gerencia/index.php");
                break;
            case 2: // Mesero
                // Registrar la hora de entrada del mesero
                $idMesero = $_SESSION['IdUsuario'];
                $sqlInsert = "INSERT INTO RegistroHoras (IdMesero, HoraEntrada, HoraSalida) VALUES (?, NOW(), NULL)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param("i", $idMesero);
                $stmtInsert->execute();

                header("Location: Pages/Mesero/Mesas.php");
                break;
            case 3: // Cliente
                header("Location: Pages/Cliente/SelecMesa.html");
                break;
            case 4: // Cocina
                header("Location: Pages/Cocina/index.php");
                break;
            case 5: // DefConf
                header("Location: Pages/DefConf/DEFCON.html");
                break;
            case 6: // Caos
                header("Location: Pages/Caos/GeneradorDeCaos.html");
                break;
            case 7: // VistaGeneral
                header("Location: Pages/VistaGeneral/VistaGeneral.html");
                break;
            default:
                header("Location: Pages/sitio/login.html");
                break;
        }
        exit();
    } else {
        $errorMsg = urlencode("Usuario o contraseña incorrectos.");
        header("Location: Pages/sitio/login.php?error=" . urlencode("Usuario y/o contraseña incorrectos."));
        echo "Usuario o contraseña incorrectos.";
        exit;
    }
} else {
    $errorMsg = urlencode("Usuario o contraseña incorrectos.");
    header("Location: Pages/sitio/login.php?error=" . urlencode("Usuario y/o contraseña incorrectos."));
    echo "Usuario o contraseña incorrectos.";
    exit;
}
