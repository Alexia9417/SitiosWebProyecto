<?php
include '../../conexion.php';

session_start();

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../Pages/sitio/login.html");
    exit();
}

// Verifica si el rol es correcto
if ($_SESSION['IdTipoUsuario'] != 3) {
    header("Location: ../Pages/sitio/login.html");
    exit();
}

// Obtener ID de usuario
$IdUsuario = $_SESSION['IdUsuario'];

// Obtener número de mesa desde la URL
$numeroMesa = $_GET['mesa'] ?? 1;

// Obtener datos de la mesa y mesero
$consultaMesa = $conn->prepare("
  SELECT M.IdMesa, M.Capacidad, M.Ubicacion, U.Nombre, U.Apellidos
  FROM Mesa M
  LEFT JOIN Usuario U ON M.IdMesero = U.IdUsuario
  WHERE M.Numero = ?
");
$consultaMesa->bind_param("i", $numeroMesa);
$consultaMesa->execute();
$resMesa = $consultaMesa->get_result();

if ($resMesa->num_rows === 0) {
    die("Mesa no encontrada.");
}
$datosMesa = $resMesa->fetch_assoc();

//Obtener el nombre del usuario
$stmt = $conn->prepare("SELECT Nombre, Apellidos FROM Usuario WHERE IdUsuario = ?");
$stmt->bind_param("i", $IdUsuario);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $usuario = $res->fetch_assoc();
    $nombreUsuario = htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellidos']);
} else {
    $nombreUsuario = "Desconocido";
}

// Calcular tiempo desde la última orden para mostrar tiempo en mesa
$consultaOrden = $conn->prepare("
  SELECT Fecha FROM Orden
  WHERE IdMesa = ?
  ORDER BY Fecha DESC
  LIMIT 1
");
$consultaOrden->bind_param("i", $datosMesa['IdMesa']);
$consultaOrden->execute();
$resOrden = $consultaOrden->get_result();

$tiempo = 'N/A';
if ($resOrden->num_rows > 0) {
    $orden = $resOrden->fetch_assoc();
    $inicio = new DateTime($orden['Fecha']);
    $ahora = new DateTime();
    $intervalo = $inicio->diff($ahora);
    $tiempo = $intervalo->format('%i min');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Redes</title>
    <link rel="stylesheet" href="Css/estilos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<style>
    /*Boton para cerrar sesion*/
    .cerrar-sesion {
        background-color: #1a1f2c;
        border: none;
        /* si quieres sin borde */
        /* o si quieres borde: border: 1px solid black; */
        border-radius: 4px;
        font-size: 1rem;
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        /* inline para que no tome toda la línea */
        align-items: center;
        justify-content: center;
        /* centra horizontalmente */
        gap: 8px;
        padding: 0 15px;
        /* espacio interno horizontal para que no pegue el texto a los bordes */
        height: 35px;
        white-space: nowrap;
        text-decoration: none;
        /* quita la línea del enlace */
    }

    .cerrar-sesion:hover {
        background-color: #2c3345;
        /* color más claro al pasar el mouse */
    }

    .redes-sociales a {
        display: inline-block;
        background-color: white;
        color: black;
        border: 1px solid black;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        margin: 0.3rem 0;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .redes-sociales a+a {
        margin-left: 0.5rem;
    }

    /* Hover con colores personalizados */
    .redes-sociales a.facebook:hover {
        background-color: #3b5998;
        /* Azul Facebook */
        color: white;
        border-color: #3b5998;
    }

    .redes-sociales a.instagram:hover {
        background-color: #E1306C;
        /* Rosa Instagram */
        color: white;
        border-color: #E1306C;
    }

    .redes-sociales a.twitter:hover {
        background-color: #1DA1F2;
        /* Azul Twitter */
        color: white;
        border-color: #1DA1F2;
    }

    .btn-cerrar {
        display: inline-block;
        background-color: #36405aff;
        /* Rojo */
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-cerrar:hover {
        background-color: #9e3535ff;
        /* Rojo oscuro */
        color: white;
    }
</style>

<body>
    <header>
        <h1>Mesa #<?= htmlspecialchars($numeroMesa) ?></h1>
        <span id="hora">Hora:</span>
        <!-- Botón para cerrar sesión
        <a href="logout_cliente.php" class="cerrar-sesion">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </a> -->
    </header>

    <nav class="nav-secundario" id="nav-mesas">
        <a class="btn-nav" href="MiOrden.php?mesa=<?= $numeroMesa ?>">Mi Orden</a>
        <!-- <a class="btn-nav" href="VistaRestaurante.php?mesa=<?= $numeroMesa ?>">Vista Restaurante</a> -->
        <a class="btn-nav" href="Acciones.php?mesa=<?= $numeroMesa ?>">Acciones</a>
        <a class="btn-nav" href="Redes.php?mesa=<?= $numeroMesa ?>">Redes</a>
        <a class="btn-nav" href="Pagar.php?mesa=<?= $numeroMesa ?>">Pagar</a>
    </nav>

    <div class="contenedor-principal">
        <div class="columna-izquierda">
            <div class="cuadro-izquierda">
                <h3><i class="fa-solid fa-diagram-project"></i> Redes Sociales</h3>
                <p>Síguenos en:</p>
                <div class="redes-sociales">
                    <a href="#" target="_blank" class="facebook"><i class="fab fa-facebook"></i> Facebook</a>
                    <a href="#" target="_blank" class="instagram"><i class="fab fa-instagram"></i> Instagram</a>
                    <a href="#" target="_blank" class="twitter"><i class="fab fa-twitter"></i> Twitter</a>
                </div>

            </div>
        </div>

        <div class="columna-derecha">
            <!-- <div class="cuadro-derecha">
                <h3>Estado del Local</h3>
                <p><i class="fa-solid fa-temperature-three-quarters"></i> Temperatura: 22°C</p>
                <p><i class="fa-solid fa-user-gear"></i> Ocupación: 
                <strong> 75% </strong></p>
                <p><i class="fa-solid fa-clock"></i> Servicio: Rápido</p>
                <p><i class="fa-solid fa-volume-high"></i> Ruido: Medio</p>
            </div> -->

            <div class="cuadro-derecha">
                <h3>Mesa #<?= htmlspecialchars($numeroMesa) ?></h3>
                <p><strong>Usuario:</strong> <?= $nombreUsuario ?></p>
                <p><strong>Capacidad:</strong> <?= $datosMesa['Capacidad'] ?> personas</p>
                <p><strong>Ubicación:</strong> <?= htmlspecialchars($datosMesa['Ubicacion']) ?></p>
                <p><strong>Mesero:</strong>
                    <?= isset($datosMesa['Nombre']) ? $datosMesa['Nombre'] . ' ' . $datosMesa['Apellidos'] : 'No asignado' ?>
                </p>
                <p><strong>Tiempo:</strong> <?= $tiempo ?></p>
            </div>
        </div>
    </div>

    <script>
        // Mostrar hora actual en el span#hora
        function mostrarHoraActual() {
            const ahora = new Date();
            const hora = ahora.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('hora').textContent = `Hora: ${hora}`;
        }
        mostrarHoraActual();
        setInterval(mostrarHoraActual, 60000);
    </script>
</body>

</html>