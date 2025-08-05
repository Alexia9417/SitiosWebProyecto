<?php
// Iniciar sesión
session_start();

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../Pages/sitio/login.html");
    exit();
}

// Verifica si el rol es correcto
if ($_SESSION['IdTipoUsuario'] != 2) {
    header("Location: ../Pages/sitio/login.html");
    exit();
}

// Incluir el archivo de conexión
include '../../conexion.php';

// Obtener el ID del mesero desde la sesión
$idMesero = $_SESSION['IdUsuario'];

// Verificar que $idMesero tenga un valor válido
if ($idMesero === null) {
    die("Error: ID de mesero es NULL.");
}

// Recuperar las propinas del mesero
$sqlPropinas = "SELECT MontoPropina FROM accioncliente WHERE IdMesero = ? AND TipoAccion = 'Dejar Propina'";
$stmt = $conn->prepare($sqlPropinas);
$stmt->bind_param("i", $idMesero);
$stmt->execute();
$resultPropinas = $stmt->get_result();

$totalPropinas = 0;
if ($resultPropinas->num_rows > 0) {
    while ($rowPropina = $resultPropinas->fetch_assoc()) {
        $totalPropinas += $rowPropina['MontoPropina'];
    }
}

// Obtener el nombre del mesero
$idMesero = $_SESSION['IdUsuario'];
$sqlMesero = "SELECT nombre FROM Usuario WHERE idUsuario = $idMesero";
$resultMesero = $conn->query($sqlMesero);
$nombreMesero = "Mesero";
if ($resultMesero->num_rows > 0) {
    $rowMesero = $resultMesero->fetch_assoc();
    $nombreMesero = $rowMesero['nombre'];
}

$sqlOrdenes = "
SELECT o.IdOrden, m.Numero AS NumeroMesa, o.Estado AS EstadoOrden,
       GROUP_CONCAT(p.Nombre SEPARATOR ', ') AS Platillos
FROM orden o
INNER JOIN mesa m ON o.IdMesa = m.IdMesa
INNER JOIN detalle_orden d ON o.IdOrden = d.IdOrden
INNER JOIN platillo p ON d.IdPlatillo = p.IdPlatillo
WHERE m.IdMesero = ?
GROUP BY o.IdOrden, m.Numero, o.Estado
ORDER BY o.Fecha DESC
";

$stmt = $conn->prepare($sqlOrdenes);
$stmt->bind_param("i", $idMesero);
$stmt->execute();
$resultOrdenes = $stmt->get_result();

$ordenes = [];
while ($row = $resultOrdenes->fetch_assoc()) {
    $ordenes[] = $row;
}


// Obtener notificaciones
$sqlNotificaciones = "SELECT n.IdNotificacion, n.Hora, n.Descripcion, n.AvisoTipo, m.Numero FROM Notificacion n JOIN Mesa m ON n.IdMesa = m.IdMesa WHERE m.IdMesero = ? ORDER BY n.Hora DESC";

$stmt = $conn->prepare($sqlNotificaciones);
$stmt->bind_param("i", $idMesero);
$stmt->execute();
$resultNotificaciones = $stmt->get_result();
$notificaciones = [];
if ($resultNotificaciones->num_rows > 0) {
    while ($rowNotificacion = $resultNotificaciones->fetch_assoc()) {
        $rowNotificacion['Hora'] = date('c', strtotime($rowNotificacion['Hora'])); // Convertir a formato ISO 8601
        $notificaciones[] = $rowNotificacion;
    }
}

//Obtener cantidad de tareas completadas
$sqlTareasCompletadas = "SELECT COUNT(*) AS TareasCompletadas FROM tareas WHERE IdMesero = ? AND Estado = 'Completada'";
$stmtTareas = $conn->prepare($sqlTareasCompletadas);
$stmtTareas->bind_param("i", $idMesero);
$stmtTareas->execute();
$resultTareas = $stmtTareas->get_result();
$tareasCompletadas = 0;
if ($resultTareas->num_rows > 0) {
    $rowTareas = $resultTareas->fetch_assoc();
    $tareasCompletadas = $rowTareas['TareasCompletadas'];
}

//Cantidad de mesas asignadas
$sqlCantMesas = "SELECT COUNT(*) AS CantidadMesas FROM Mesa WHERE IdMesero = ?";
$stmtCantM = $conn->prepare($sqlCantMesas);
$stmtCantM->bind_param("i", $idMesero);
$stmtCantM->execute();
$resultMesas = $stmtCantM->get_result();
$CantidadMesas = 0;
if ($resultMesas->num_rows > 0) {
    $rowCMesas = $resultMesas->fetch_assoc();
    $CantidadMesas = $rowCMesas['CantidadMesas'];
}

// Obtener la hora de entrada del turno de hoy
$sql = "SELECT HoraEntrada FROM RegistroHoras 
        WHERE IdMesero = ? 
          AND DATE(HoraEntrada) = CURDATE()
        ORDER BY HoraEntrada DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idMesero);
$stmt->execute();
$result = $stmt->get_result();

$horaEntrada = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $horaEntrada = $row['HoraEntrada'];
}

// Cerrar la conexión
$conn->close();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Mesero - Cocina</title>
    <link rel="stylesheet" href="Css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" />
</head>

<style>
    /*Boton para cerrar sesion*/
    .cerrar-sesion {
        background-color: #4E5D83;
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
        height: 30px;
        width: 120px;
        text-decoration: none;
        /* quita la línea del enlace */
    }

    /* Notificacion */
    .cuadro-notificaciones {
        position: absolute;
        top: 60px;
        left: 10px;
        width: 350px;
        max-height: 300px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
        z-index: 1000;
        display: none;
        flex-direction: column;
    }

    .encabezado-notificaciones {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f5f5f5;
        padding: 10px;
        font-weight: bold;
        border-bottom: 1px solid #ccc;
        color: #333;
    }

    .encabezado-notificaciones button {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .lista-notificaciones {
        padding: 10px;
    }

    .notificacion {
        padding: 8px;
        font-size: 0.85rem;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        color: #333;
    }

    .notificacion.alta {
        border-left: 4px solid red;
        background-color: #ffe6e6;
    }

    .notificacion.media {
        border-left: 4px solid orange;
        background-color: #fff3e0;
    }

    .notificacion.baja {
        border-left: 4px solid green;
        background-color: #e8f5e9;
    }

    hr {
        margin: 8px 0;
        border: none;
        border-top: 1px solid #ccc;
    }

    .notificacion.no-leida {
        font-weight: bold;
        opacity: 1;
    }

    .notificacion.leida {
        font-weight: normal;
        opacity: 1;
    }


    .texto-notificacion {
        margin-left: 5px;
        display: none;
        /* oculto por defecto */
    }

    /*-----------------------------------------------------------------------------------*/

    .contenedor-principal {
        height: 500px;
    }

    /* CONTENEDOR DE LA LISTA DE ÓRDENES */
    .Lista-Ordenes {
        width: 100%;
        margin-top: 20px;
        color: #000;
        /* texto visible por defecto */
    }

    /* TÍTULO */
    .Lista-Ordenes h2 {
        color: #4e37a0;
        margin-bottom: 15px;
    }

    /* CADA ORDEN */
    .orden {
        background-color: #f9f7ff;
        border: 1.5px solid #d5ccff;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 15px;
        width: 100%;
        box-sizing: border-box;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        color: #000;
        /* fuerza color negro para todo dentro */
    }

    /* ENCABEZADO DE LA ORDEN */
    .orden-cabecera {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 15px;
        /* espacio entre "Orden Mesa #" y el estado */
        font-size: 1rem;
        margin-bottom: 10px;
    }

    /* DETALLE DE ORDEN: PRODUCTOS Y BOTÓN */
    .orden-detalle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 0.95rem;
        color: #000;
    }

    /* PRODUCTOS */
    .orden-detalle span {
        flex: 1;
        min-width: 200px;
    }

    /* ESTADO (Etiqueta de color) */
    .estado {
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
        color: #fff;
        white-space: nowrap;
    }

    .estado.urgente {
        background-color: #e74c3c;
    }

    .estado.normal {
        background-color: #E9C89A;
    }

    /* BOTÓN */
    .boton-preguntar {
        background-color: #7543f5;
        color: #fff;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        white-space: nowrap;
    }

    .boton-preguntar:hover {
        background-color: #5e2fcf;
    }
</style>

<body>

    <!-- ENCABEZADO -->
    <header>
        <!-- Botón de notificaciones -->
        <button class="notificaciones">
            <i class="fa-solid fa-bell"></i>
            <span class="texto-notificacion">Notificaciones Pendientes</span>
        </button>

        <!-- Usuario -->
        <div class="usuario">
            <i class="fa-solid fa-circle-user"></i>
            <strong>Mesero: <?php echo htmlspecialchars($nombreMesero); ?></strong>
        </div>

        <!-- Botón para cerrar sesión -->
        <a href="#" class="cerrar-sesion" onclick="manejarCierreSesion(event)">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </a>
    </header>

    <!-- CUADRO DE NOTIFICACIONES -->
    <div class="cuadro-notificaciones" id="cuadroNotificaciones">
        <div class="encabezado-notificaciones">
            <span>Notificaciones</span>
            <button id="limpiarBtn">Limpiar Todo</button>
        </div>
        <div class="lista-notificaciones">
            <?php if (empty($notificaciones)): ?>
                <p style="text-align:center; color: gray;">Sin notificaciones</p>
            <?php else: ?>
                <?php foreach ($notificaciones as $notificacion): ?>
                    <div class="notificacion <?php echo htmlspecialchars($notificacion['AvisoTipo']); ?> no-leida">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo "Mesa " . htmlspecialchars($notificacion['Numero']) . ": " . htmlspecialchars($notificacion['Descripcion']); ?>
                        <br><small><?php echo htmlspecialchars($notificacion['Hora']); ?></small>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- PANEL SUPERIOR -->
    <section class="paneles-superiores">
        <div class="panel estres">
            <strong>Estrés</strong>
            <div class="barra">
                <div style="width: <?php echo $estres; ?>%"></div>
            </div>
            <span><?php echo $estres; ?>%</span>
        </div>
        <div class="panel energia">
            <strong>Energía</strong>
            <div class="barra">
                <div style="width: <?php echo $energia; ?>%"></div>
            </div>
            <span><?php echo $energia; ?>%</span>
        </div>
        <div class="panel eficiencia">
            <strong>Eficiencia</strong>
            <div class="barra">
                <div style="width: <?php echo $eficiencia; ?>%"></div>
            </div>
            <span><?php echo $eficiencia; ?>%</span>
        </div>
        <div class="panel">
            <strong>Propinas</strong>
            <div class="barra" style="background: none;"></div>
            <span><strong>$<?php echo number_format($totalPropinas, 2); ?></strong>
            </span>
        </div>
    </section>

    <!-- NAV SECUNDARIO -->
    <nav class="nav-secundario">
        <a href="Mesas.php"><i class="fas fa-chair" style="color: #6b30ff;"></i> Mis mesas</a>
        <a href="Tareas.php"><i class="fas fa-tasks" style="color: #6b30ff;"></i> Tareas</a>
        <a href="Cocina.php"><i class="fas fa-utensils" style="color: #6b30ff;"></i> Cocina</a>
        <a href="Comunicacion.php"><i class="fas fa-comments" style="color: #6b30ff;"></i> Comunicación</a>
        <a href="Persona.php"><i class="fas fa-user" style="color: #6b30ff;"></i> Persona</a>
    </nav>



    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">
        <div class="contenedor-principal">
            <div class="Lista-Ordenes">
                <h2 style="color: white; text-align: left;">Estado de Órdenes en Cocina</h2>

                <?php if (empty($ordenes)): ?>
                    <p style="color:white; text-align:center;">No hay órdenes actualmente</p>
                <?php else: ?>
                    <?php foreach ($ordenes as $orden): ?>
                        <?php
                        // Determinar la clase del estado según el valor
                        $estadoClase = '';
                        $textoBoton = '';
                        switch ($orden['EstadoOrden']) {
                            case 'Pendiente':
                                $estadoClase = 'urgente';
                                $textoBoton = 'Pedir que se apuren';
                                break;
                            case 'Listo':
                                $estadoClase = 'normal';
                                $textoBoton = 'Orden lista';
                                break;
                            case 'Cancelado':
                                $estadoClase = 'normal';
                                $textoBoton = 'Orden cancelada';
                                break;
                            default:
                                $estadoClase = 'normal';
                                $textoBoton = 'Preguntar cuánto falta';
                        }
                        ?>
                        <div class="orden">
                            <div class="orden-cabecera">
                                <span><strong>Orden Mesa #<?php echo htmlspecialchars($orden['NumeroMesa']); ?></strong></span>
                                <span class="estado <?php echo $estadoClase; ?>">
                                    <?php echo htmlspecialchars($orden['EstadoOrden']); ?>
                                </span>
                            </div>
                            <div class="orden-detalle">
                                <span><?php echo htmlspecialchars($orden['Platillos']); ?></span>
                                <button class="boton-preguntar" <?php echo ($orden['EstadoOrden'] === 'Listo' || $orden['EstadoOrden'] === 'Cancelado') ? 'disabled' : ''; ?>>
                                    <?php echo $textoBoton; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen -->
        <aside class="resumen">
            <h3>Resumen del Turno</h3>
            <div class="dato-resumen">
                <span>Propinas</span>
                <strong style="color: #7543f5;">$<?php echo number_format($totalPropinas, 2); ?></strong>
            </div>
            <div class="dato-resumen">
                <span>Tareas Completadas</span>
                <strong style="color: #7543f5;"><?php echo number_format($tareasCompletadas); ?></strong>
            </div>
            <div class="dato-resumen">
                <span>Tiempo Trabajado</span>
                <strong style="color: #7543f5;">
                    <p><span id="timer">00:00:00</span></p>
                </strong>
            </div>
            <div class="dato-resumen">
                <span>Cant Mesas Asignadas</span>
                <strong style="color: #7543f5;"><?php echo number_format($CantidadMesas); ?></strong>
            </div>
        </aside>

    </main>

    <!-- Script para manejar el estado de los botones -->
    <script src="JS/scriptEstado.js"></script>

    <!-- Script para manejar el estado de los botones -->
    <script src="Js/scriptEstado.js"></script>

    <script>
        const horaEntrada = <?php echo $horaEntrada ? "new Date('$horaEntrada')" : "null"; ?>;

        if (horaEntrada) {
            function actualizarTimer() {
                const ahora = new Date();
                let diff = Math.floor((ahora - horaEntrada) / 1000); // en segundos

                let horas = Math.floor(diff / 3600);
                diff %= 3600;
                let minutos = Math.floor(diff / 60);
                let segundos = diff % 60;

                document.getElementById("timer").textContent =
                    String(horas).padStart(2, '0') + ":" +
                    String(minutos).padStart(2, '0') + ":" +
                    String(segundos).padStart(2, '0');
            }

            setInterval(actualizarTimer, 1000);
            actualizarTimer();
        } else {
            document.getElementById("timer").textContent = "Sin turno activo";
        }
    </script>

    <script>
        // Script para manejar las notificaciones
        // Elementos
        const campanaBtn = document.querySelector('.notificaciones');
        const cuadro = document.getElementById('cuadroNotificaciones');
        const limpiarBtn = document.getElementById('limpiarBtn');
        const textoNotificacion = document.querySelector('.texto-notificacion');
        const listaNotificaciones = document.querySelector('.lista-notificaciones');

        // Mostrar/ocultar el cuadro y marcar como leídas
        campanaBtn.addEventListener('click', () => {
            const estaVisible = cuadro.style.display === 'flex';

            if (!estaVisible) {
                // Mostrar cuadro
                cuadro.style.display = 'flex';

                // Marcar como leídas las que no lo están
                const notificacionesNoLeidas = listaNotificaciones.querySelectorAll('.notificacion.no-leida');
                notificacionesNoLeidas.forEach(n => {
                    n.classList.remove('no-leida');
                    n.classList.add('leida');
                });

                // Actualizar estado del botón
                actualizarTextoNotificacion();
            } else {
                // Ocultar cuadro
                cuadro.style.display = 'none';
            }
        });

        // Limpiar todas las notificaciones
        limpiarBtn.addEventListener('click', () => {
            listaNotificaciones.innerHTML = '<p style="text-align:center; color: gray;">Sin notificaciones</p>';
            actualizarTextoNotificacion();
        });

        // Ocultar el cuadro al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!cuadro.contains(e.target) && !campanaBtn.contains(e.target)) {
                cuadro.style.display = 'none';
            }
        });

        // Mostrar u ocultar el texto del botón según haya no leídas
        function actualizarTextoNotificacion() {
            const hayPendientes = listaNotificaciones.querySelectorAll('.notificacion.no-leida').length > 0;
            textoNotificacion.style.display = hayPendientes ? 'inline' : 'none';
        }

        // Ejecutar al cargar
        actualizarTextoNotificacion();
    </script>

    <!-- Script para calcular el tiempo desde la notificación -->
    <script>
        function timeSince(date) {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000);
            let interval = Math.floor(seconds / 31536000);
            if (interval >= 1) return interval + " año" + (interval === 1 ? "" : "s");

            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return interval + " mes" + (interval === 1 ? "" : "es");

            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return interval + " día" + (interval === 1 ? "" : "s");

            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return interval + " hora" + (interval === 1 ? "" : "s");

            interval = Math.floor(seconds / 60);
            if (interval >= 1) return interval + " minuto" + (interval === 1 ? "" : "s");

            return Math.floor(seconds) + " segundo" + (seconds === 1 ? "" : "s");
        }

        document.querySelectorAll('.notificacion small').forEach(function(element) {
            const date = element.textContent;
            element.textContent = "Hace " + timeSince(date);
        });
    </script>

    <script>
        function manejarCierreSesion(event) {
            event.preventDefault(); //Evita que el enlace se comporte de manera predeterminada
            restablecerEstado(); //Restablece los valores

            // Redirige al usuario a logout.php después de restablecer los valores
            window.location.href = '../../logout.php';
        }

        // Función para restablecer el estado
        function restablecerEstado() {
            let estres = 0;
            let energia = 100;
            let eficiencia = 100;
            localStorage.setItem('estres', estres);
            localStorage.setItem('energia', energia);
            localStorage.setItem('eficiencia', eficiencia);
        }
    </script>

</body>

</html>