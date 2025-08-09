<?php
// Iniciar sesión
session_start();

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.php");
    exit();
}

// Verifica si el rol es correcto
if ($_SESSION['IdTipoUsuario'] != 2) {
    header("Location: ../sitio/login.php");
    exit();
}

// Incluir el archivo de conexión
include '../../conexion.php';

// Obtener el ID del mesero desde la sesión
$idMesero = $_SESSION['IdUsuario'];
// echo "ID del mesero: $idMesero";

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

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Mesero - Persona</title>
    <link rel="stylesheet" href="Css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<style>
    .contenedor-Cuidado {
        background-color: #f0f8ff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        width: 100%;
        color: black;
    }

    .botones-cuidado {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        /* espacio entre botones */
    }

    .btn-cuidado {
        background-color: #4d94ff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        height: 50px;

        /* Ocupa la mitad del contenedor menos espacio del gap */
        flex: 0 0 calc(50% - 10px);

        display: flex;
        align-items: center;
        justify-content: center;

        margin: 0;
        /* elimina margenes para que gap controle el espacio */
    }


    .contenedor-Emergencia {
        background-color: #ffe6e6;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        margin-bottom: 10px;
        color: black;
    }

    .botones-emergencia {
        display: flex;
        flex-wrap: wrap;
        /* Permite que los botones bajen de fila */
        gap: 10px;
        /* Espacio entre botones */
        justify-content: center;
        /* Centra los botones horizontalmente */
    }

    .btn-emergencia {
        flex: 0 0 calc(50% - 10px);
        /* Cada botón ocupa la mitad menos espacio */
        background-color: #ff4d4d;
        color: rgb(0, 0, 0);
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 10px;
        width: 350px;
        height: 50px;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-ancho-completo {
        flex: 0 0 100%;
        color: #ffffff;
    }

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

    /* Fondo oscuro */
    #modalRenuncia {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        /* Oculto por defecto */
        justify-content: center;
        align-items: center;
        z-index: 2000;
        animation: fadeIn 0.3s ease-in-out;
    }

    /* Contenedor del modal */
    #modalRenuncia .modal-contenido {
        background: #ffffff;
        padding: 25px 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease-out;
    }

    /* Título */
    #modalRenuncia h2 {
        font-size: 1.4rem;
        margin-bottom: 10px;
        color: #333;
    }

    /* Texto */
    #modalRenuncia p {
        font-size: 1rem;
        color: #555;
        margin-bottom: 20px;
    }

    /* Botones */
    #modalRenuncia .botones-modal {
        display: flex;
        justify-content: space-around;
        gap: 15px;
    }

    #modalRenuncia button {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    /* Botón cancelar */
    #modalRenuncia #btnCancelar {
        background: #ccc;
        color: #333;
    }

    #modalRenuncia #btnCancelar:hover {
        background: #b3b3b3;
    }

    /* Botón confirmar */
    #modalRenuncia #btnConfirmarRenuncia {
        background: #ff4d4d;
        color: #fff;
    }

    #modalRenuncia #btnConfirmarRenuncia:hover {
        background: #e60000;
    }

    .panel .barra div {
        background: #7543f5;
        transition: width 0.5s ease;
        /* animación */
    }
    /* Fondo oscuro */
#modalMensaje {
    display: none; /* Oculto por defecto */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5); /* Fondo semi-transparente */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2100;
    animation: fadeIn 0.3s ease-in-out;
}

/* Caja del modal */
#modalMensaje > div {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s ease-in-out;
}

/* Título */
#modalMensaje h2 {
    font-size: 22px;
    margin-bottom: 10px;
    color: #333;
}

/* Texto */
#modalMensaje p {
    font-size: 16px;
    color: #555;
    margin-bottom: 20px;
}

/* Botón */
#btnCerrarMensaje {
    background: #636AE8;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

#btnCerrarMensaje:hover {
    background: #4f53d2;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
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
        <!-- Icono de usuario -->
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
        <!-- Espacio para mesas (luego) -->
        <div class="contenedor-principal">
            <div class="contenedor-Cuidado">
                <H2>Cuidado Personal</H2>
                <!-- Aqui va a hacer 4 botones, Descanso, Tomar Agua, Comer Algo, Ir a Casa-->
                <div class="botones-cuidado">
                    <button class="btn-cuidado" onclick="descanso()" style="background-color: #636AE8;"><i class="fa-solid fa-clock"></i>
                        Descanso</button>
                    <button class="btn-cuidado" onclick="tomarAgua()" style="background-color: #636AE8;"><i class="fa-solid fa-glass-water"></i> Tomar
                        Agua</button>
                    <button class="btn-cuidado" onclick="comerAlgo()" style="background-color: #636AE8;"><i class="fa-solid fa-bowl-food"></i> Comer
                        Algo</button>
                    <!-- <button class="btn-cuidado" onclick="irACasa()" style="background-color: #967ED5;"><i class="fa-solid fa-house"></i> Ir a
                        Casa</button> -->
                </div>
            </div>
            <div class="contenedor-Emergencia">
                <h2>Emergencia</h2>
                <!-- Aqui va a hacer 3 botones, Reportar Enfermedad, Solicitar Refuerzos, Renunciar al Trabajo-->
                <div class="botones-emergencia">
                    <button class="btn-emergencia" onclick="reportarEnfermedad()" style="background-color: #e9c89a;"><i class="fa-solid fa-virus"></i> Reportar
                        Enfermedad</button>
                    <button class="btn-emergencia" onclick="solicitarRefuerzos()" style="background-color: #e9c89a;"><i class="fa-solid fa-users"></i> Solicitar
                        Refuerzos</button>
                    <button id="btnRenunciar" class="btn-emergencia btn-ancho-completo" style="background-color: #ff4d4d;">
                        <i class="fa-solid fa-door-open"></i> Renunciar al Trabajo
                    </button>
                </div>
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

    <!-- MODAL PARA CONFIRMAR RENUNCIA -->
    <div id="modalRenuncia" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:2000;">
        <div style="background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px; text-align:center;">
            <h2>¿Estás seguro de que deseas renunciar?</h2>
            <p>Tu cuenta pasará a ser Cliente.</p>
            <div style="margin-top:20px; display:flex; justify-content:space-around;">
                <button id="btnCancelar" style="background:#ccc; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Cancelar</button>
                <button id="btnConfirmarRenuncia" style="background:#ff4d4d; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Sí, renunciar</button>
            </div>
        </div>
    </div>

    <!--Modal mensaje-->
    <div id="modalMensaje" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:2100;">
        <div style="background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px; text-align:center;">
            <h2>¡Has renunciado!</h2>
            <p>Ahora eres Cliente.</p>
            <button id="btnCerrarMensaje" style="background:#636AE8; color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; margin-top:20px;">Aceptar</button>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Script para manejar el estado de los botones -->
    <script src="JS/scriptEstado.js"></script>

    <!--Script modal renuncia-->
    <script>
        const modalRenuncia = document.getElementById('modalRenuncia');
        const btnRenunciar = document.getElementById('btnRenunciar');
        const btnCancelar = document.getElementById('btnCancelar');
        const btnConfirmarRenuncia = document.getElementById('btnConfirmarRenuncia');

        const modalMensaje = document.getElementById('modalMensaje');
        const btnCerrarMensaje = document.getElementById('btnCerrarMensaje');

        // Abrir modal de confirmación
        btnRenunciar.addEventListener('click', () => {
            modalRenuncia.style.display = 'flex';
        });

        // Cerrar modal de confirmación
        btnCancelar.addEventListener('click', () => {
            modalRenuncia.style.display = 'none';
        });

        // Cerrar modal si clic fuera del contenido
        window.addEventListener('click', (e) => {
            if (e.target === modalRenuncia) {
                modalRenuncia.style.display = 'none';
            }
        });

        // Confirmar renuncia
        btnConfirmarRenuncia.addEventListener('click', () => {
            fetch('renuncia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modalRenuncia.style.display = 'none'; // Cierra el modal de confirmación
                        modalMensaje.style.display = 'flex'; // Muestra el modal con el mensaje
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        // Cerrar modal de mensaje y redirigir
        btnCerrarMensaje.addEventListener('click', () => {
            modalMensaje.style.display = 'none';
            window.location.href = '../../logout.php'; // ✅ Redirige al logout
        });
    </script>

    <!-- Tiempo hora trabajadas -->
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
            event.preventDefault();

            restablecerEstado(); // Reset UI state

            fetch('cerrarSesionMesero.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ahora redirige al logout global
                        window.location.href = '../../logout.php';
                    } else {
                        console.error("Error: ", data.message);
                        window.location.href = '../../logout.php'; // fallback
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    window.location.href = '../../logout.php'; // fallback
                });
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