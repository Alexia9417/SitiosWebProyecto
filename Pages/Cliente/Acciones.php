<?php
session_start();
include '../../conexion.php';

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.html");
    exit();
}

// Verifica si el rol es correcto
if ($_SESSION['IdTipoUsuario'] != 3) {
    header("Location: ../sitio/login.html");
    exit();
}

// Obtener ID de usuario
$IdUsuario = $_SESSION['IdUsuario'];

// Obtener número de mesa desde la URL
$numeroMesa = $_GET['mesa'] ?? 1;

// Buscar ID de mesa y sus datos -----------------------------------------------------------------
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

// Obtener tarjeta registrada si existe ---------------------------------------------------------------
$tarjetaRegistrada = null;
$stmt = $conn->prepare("SELECT NombreTitular, NumeroTarjeta, FechaVenc FROM MetodoPago WHERE IdUsuario = ? LIMIT 1");
$stmt->bind_param("i", $IdUsuario);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $tarjetaRegistrada = $res->fetch_assoc();
}

// Verificar el valor de $tarjetaRegistrada
//var_dump($tarjetaRegistrada);


//Obtener el nombre del usuario -----------------------------------------------------------------
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

// Calcular el tiempo desde la última orden -----------------------------------------------------------------
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Acciones</title>
    <link rel="stylesheet" href="Css/estilos.css" />
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

    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-contenido {
        background: #4e5d83;
        border-radius: 6px;
        padding: 1rem 1.5rem;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .modal-contenidoQueja {
        background: #4e5d83;
        border-radius: 6px;
        padding: 1rem 1.5rem;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .modal-contenidoSalida {
        background: #4e5d83;
        border-radius: 6px;
        padding: 1.5rem;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .cerrar {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        font-size: 1.3rem;
        color: #000000ff;
    }

    .btn-grupo {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .btn-grupo button {
        flex: 1;
        padding: 0.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: #967ed5;
        color: white;
    }

    .btn-grupo button.btn-cancelar {
        background-color: #32394bff;
        color: white;
    }

    .btn-grupo button:hover {
        background-color: #7764a7ff;
    }

    .btn-grupo button.btn-cancelar:hover {
        background-color: rgba(29, 34, 48, 1);
    }

    .estrellas i {
        font-size: 2rem;
        color: #32394bff;
        cursor: pointer;
    }

    .estrellas i.seleccionada {
        color: gold;
    }

    .mensaje {
        margin-top: 0.8rem;
        font-weight: bold;
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
        <!-- Botón para cerrar sesión -->
        <!-- <a href="logout_cliente.php" class="cerrar-sesion">
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
    <!-- Contenedor principal con Acciones -->
    <h2 id="MesasOcupadas">Acciones</h2>
    <div class="contenedor-principal">
        <div class="columna-izquierda">
            <div class="contenedor-mini-cuadros">
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-star"></i>
                    <h3>Calificar Servicio</h3>
                    <p>Comparte tu experiencia</p>
                </div>
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-phone"></i>
                    <h3>Llamar mesero</h3>
                    <p>Solicitar Asistencia</p>
                </div>
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <h3>Hacer una Queja</h3>
                    <p>Reportar un problema</p>
                </div>
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-coins"></i>
                    <h3>Dejar Propina</h3>
                    <p>Darle propina al mesero</p>
                </div>
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-bottle-water"></i>
                    <h3>Pedir Agua</h3>
                    <p>Solicitar agua gratis</p>
                </div>
                <div class="mini-cuadroIzquierda">
                    <i class="fa-solid fa-door-open"></i>
                    <h3>Irme del Restaurante</h3>
                    <p>Finalizar visita</p>
                </div>
            </div>
        </div>

        <div class="columna-derecha">
            <!-- <div class="cuadro-derecha">
                <h3>Estado del Local</h3>
                <p><i class="fa-solid fa-temperature-three-quarters"></i> Temperatura: 22°C</p>
                <p><i class="fa-solid fa-user-gear"></i> Ocupación: 75%</p>
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

    <!-- MODAL Calificar Servicio -->
    <div id="modalCalificar" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal('modalCalificar')">&times;</span>
            <h3 style="color: white">Calificar Servicio</h3>
            <p style="color: white">¿Cómo calificarías nuestro servicio?</p>
            <div class="estrellas" id="estrellas">
                <i class="fa-regular fa-star" data-valor="1"></i>
                <i class="fa-regular fa-star" data-valor="2"></i>
                <i class="fa-regular fa-star" data-valor="3"></i>
                <i class="fa-regular fa-star" data-valor="4"></i>
                <i class="fa-regular fa-star" data-valor="5"></i>
            </div>
            <label for="comentario" style="color: white">Comentario (opcional):</label>
            <textarea id="comentario" rows="4" style="width: calc(100% - 1rem); border-radius: 6px; padding: 0.5rem; resize: vertical; margin-right: 1rem;"></textarea>
            <div class="btn-grupo">
                <button onclick="enviarCalificacion()">Enviar Calificación</button>
                <button onclick="cerrarModal('modalCalificar')" class="btn-cancelar">Cancelar</button>
            </div>
            <div id="mensajeCalificacion" class="mensaje"></div>
        </div>
    </div>

    <!-- MODAL Hacer una Queja -->
    <div id="modalQueja" class="modal">
        <div class="modal-contenidoQueja">
            <span class="cerrar" onclick="cerrarModal('modalQueja')">&times;</span>
            <h3 style="color: white">Hacer una Queja</h3>
            <p style="color: white">Por favor, describe tu queja:</p>
            <textarea id="comentarioQueja" rows="4" style="width: calc(100% - 1rem); border-radius: 6px; padding: 0.5rem; resize: vertical; margin-right: 1rem;"></textarea>

            <div class="btn-grupo">
                <button onclick="enviarQueja()">Enviar Queja</button>
                <button onclick="cerrarModal('modalQueja')" class="btn-cancelar">Cancelar</button>
            </div>
            <div id="mensajeQueja" class="mensaje"></div>
        </div>
    </div>

    <!-- MODAL Irme del Restaurante -->
    <div id="modalSalir" class="modal">
        <div class="modal-contenidoSalida">
            <span class="cerrar" onclick="cerrarModal('modalSalir')">&times;</span>
            <h3 style="color: white">Irme del Restaurante</h3>
            <p style="color: white">¿Estás seguro de que deseas irte sin pagar?</p>
            <div class="btn-grupo">
                <button onclick="confirmarSalir()">Sí, estoy seguro</button>
                <button onclick="cerrarModal('modalSalir')" class="btn-cancelar">Cancelar</button>
            </div>
            <div id="mensajeSalir" class="mensaje"></div>
        </div>
    </div>

    <!-- MODAL Mensaje -->
    <div id="modalMensaje" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal('modalMensaje')">&times;</span>
            <h3 id="tituloMensaje" style="color:white">Mensaje</h3>
            <p id="textoMensaje" style="color:white">Este es un mensaje.</p>
            <div class="btn-grupo">
                <button onclick="cerrarModal('modalMensaje')">Aceptar</button>
            </div>
        </div>
    </div>

    <!-- MODAL Dejar Propina -->
    <div id="modalPropina" class="modal">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal('modalPropina')">&times;</span>
            <h3 style="color: white">Dejar Propina</h3>
            <p style="color: white">¿Desea dejar una propina al mesero asignado a su mesa?</p>
            <input type="number" id="montoPropina" placeholder="Monto de la propina" style="width: calc(100% - 1rem); border-radius: 6px; padding: 0.5rem; margin-right: 1rem;">
            <div class="btn-grupo">
                <button onclick="enviarPropina()">Sí, dejar propina</button>
                <button onclick="cerrarModal('modalPropina')" class="btn-cancelar">Cancelar</button>
            </div>
            <div id="mensajePropina" class="mensaje"></div>
        </div>
    </div>


    <!--Script para las propinas-->
    <script>
        // Función para cerrar el modal
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'modalPropina') {
                // Limpiar el campo de entrada del monto de la propina
                document.getElementById('montoPropina').value = '';

                // Limpiar y ocultar el mensaje
                const mensaje = document.getElementById('mensajePropina');
                mensaje.textContent = '';
                mensaje.style.display = 'none';
            }
        }

        // Función para mostrar mensajes en un modal
        function mostrarMensaje(titulo, mensaje) {
            const modal = document.getElementById('modalMensaje');
            const tituloMensaje = document.getElementById('tituloMensaje');
            const textoMensaje = document.getElementById('textoMensaje');
            tituloMensaje.textContent = titulo;
            textoMensaje.textContent = mensaje;
            modal.classList.add('active');
        }

        // Evento para dejar propina
        document.querySelectorAll('.mini-cuadroIzquierda')[3].addEventListener('click', () => {
            // Mostrar el modal de propina
            const mensaje = document.getElementById('modalPropina').classList.add('active');
            mensaje.textContent = '';
            mensaje.style.display = 'none';
        });

        function enviarPropina() {
            const montoPropina = document.getElementById('montoPropina').value.trim();
            const mensaje = document.getElementById('mensajePropina');

            if (montoPropina === '' || isNaN(montoPropina) || parseFloat(montoPropina) <= 0) {
                mensaje.style.color = 'black';
                mensaje.style.display = 'block';
                mensaje.textContent = 'Por favor, ingrese un monto válido para la propina.';
                return;
            }

            // Obtener el número de mesa actual desde la URL
            const numeroMesa = new URLSearchParams(window.location.search).get('mesa');

            const formData = new FormData();
            formData.append('montoPropina', montoPropina);
            formData.append('mesa', numeroMesa);

            fetch('GuardarPropina.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    if (data.includes('éxito')) {
                        document.getElementById('montoPropina').value = '';
                        mostrarMensaje('Notificación', 'Propina enviada, muchas gracias por su aporte.');
                        setTimeout(() => {
                            cerrarModal('modalPropina');
                        }, 1500);
                    } else {
                        mensaje.style.color = 'black';
                        mensaje.style.display = 'block';
                        mensaje.textContent = data;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mensaje.style.color = 'black';
                    mensaje.style.display = 'block';
                    mensaje.textContent = 'Error al conectar con el servidor.';
                });
        }
    </script>

    <!-- Script para calificar servicio, irse del restaurante y llamar al mesero, pedir agua-->
    <script>
        let calificacion = 0;

        document.querySelectorAll('.mini-cuadroIzquierda')[0].addEventListener('click', () => {
            document.getElementById('modalCalificar').classList.add('active');
        });

        document.querySelectorAll('#estrellas i').forEach(star => {
            star.addEventListener('click', () => {
                calificacion = parseInt(star.getAttribute('data-valor'));
                actualizarEstrellas();
            });
        });

        function actualizarEstrellas() {
            document.querySelectorAll('#estrellas i').forEach(star => {
                const valor = parseInt(star.getAttribute('data-valor'));
                star.classList.toggle('seleccionada', valor <= calificacion);
            });
        }

        function cerrarModal(id) {
            document.getElementById(id).classList.remove('active');
            if (id === 'modalCalificar') {
                document.getElementById('comentario').value = '';
                document.getElementById('mensajeCalificacion').textContent = '';
                calificacion = 0;
                actualizarEstrellas();
            }
            if (id === 'modalPropina') {
                document.getElementById('montoPropina').value = '';
                document.getElementById('mensajePropina').textContent = '';
            }
        }
        // Evento para enviar la calificación
        function enviarCalificacion() {
            const comentario = document.getElementById('comentario').value.trim();
            const mensaje = document.getElementById('mensajeCalificacion');

            if (calificacion === 0) {
                mensaje.style.color = 'red';
                mensaje.textContent = 'Por favor, selecciona una calificación.';
                return;
            }

            const formData = new FormData();
            formData.append('estrellas', calificacion);
            formData.append('comentario', comentario);

            fetch('GuardarCalificacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    mensaje.style.color = data.toLowerCase().includes('éxito') ? 'green' : 'red';
                    mensaje.textContent = data;

                    if (data.toLowerCase().includes('éxito')) {
                        setTimeout(() => cerrarModal('modalCalificar'), 1500);
                    }
                })
                .catch(() => {
                    mensaje.style.color = 'red';
                    mensaje.textContent = 'Error al conectar con el servidor.';
                });
        }

        document.querySelectorAll('.mini-cuadroIzquierda')[2].addEventListener('click', () => {
            document.getElementById('modalQueja').classList.add('active');
        });

        // Evento para enviar la queja
        function enviarQueja() {
            const comentario = document.getElementById('comentarioQueja').value.trim();
            const mensaje = document.getElementById('mensajeQueja');

            if (comentario === '') {
                mensaje.style.color = 'red';
                mensaje.textContent = 'Por favor, describe tu queja.';
                return;
            }

            const formData = new FormData();
            formData.append('comentario', comentario);

            fetch('GuardarQueja.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    mensaje.style.color = data.toLowerCase().includes('éxito') ? 'green' : 'red';
                    mensaje.textContent = data;
                    if (data.toLowerCase().includes('éxito')) {
                        setTimeout(() => cerrarModal('modalQueja'), 1500);
                    }
                })
                .catch(() => {
                    mensaje.style.color = 'red';
                    mensaje.textContent = 'Error al conectar con el servidor.';
                });
        }

        // Evento para el botón de irse del restaurante
        document.querySelectorAll('.mini-cuadroIzquierda')[5].addEventListener('click', () => {
            const numeroMesa = new URLSearchParams(window.location.search).get('mesa');
            const formData = new FormData();
            formData.append('mesa', numeroMesa);

            fetch('VerificarSalida.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    const texto = document.querySelector('#modalSalir p');
                    if (data === 'tiene_orden') {
                        texto.textContent = '¿Estás seguro de salir sin pagar? Lo no pagado se le cobrará en la siguiente visita.';
                    } else {
                        texto.textContent = '¿Estás seguro de salir del Restaurante?';
                    }
                    document.getElementById('modalSalir').classList.add('active');
                })
                .catch(() => {
                    alert('Error al verificar la salida');
                });
        });


        function confirmarSalir() {
            const mensaje = document.getElementById('mensajeSalir');
            const numeroMesa = new URLSearchParams(window.location.search).get('mesa');

            const formData = new FormData();
            formData.append('mesa', numeroMesa);

            fetch('ConfirmarSalida.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    console.log('Respuesta del servidor:', data);
                    if (data.includes('deuda_guardada') || data.includes('sin_deuda')) {
                        setTimeout(() => {
                            window.location.href = 'logout_cliente.php';
                        }, 2000);
                    } else {
                        mostrarMensaje('Error', 'Error al procesar la salida: ' + data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Error', 'Error al conectar con el servidor.');
                });
        }


        // Función para mostrar mensajes en un modal
        function mostrarMensaje(titulo, mensaje) {
            const modal = document.getElementById('modalMensaje');
            const tituloMensaje = document.getElementById('tituloMensaje');
            const textoMensaje = document.getElementById('textoMensaje');

            tituloMensaje.textContent = titulo;
            textoMensaje.textContent = mensaje;

            modal.classList.add('active');
        }
        // Función para cerrar el modal
        function cerrarModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Evento para llamar al mesero
        document.querySelectorAll('.mini-cuadroIzquierda')[1].addEventListener('click', () => {
            const formData = new FormData();
            formData.append('tipo', 'Llamar Mesero');
            formData.append('mesa', new URLSearchParams(window.location.search).get('mesa')); // Obtener el número de mesa de la URL

            fetch('GuardarNotificacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.text();
                })
                .then(data => {
                    mostrarMensaje('Notificación', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Error', 'Error al conectar con el servidor.');
                });
        });

        // Evento para notificación de pedir agua
        document.querySelectorAll('.mini-cuadroIzquierda')[4].addEventListener('click', () => {
            const formData = new FormData();
            formData.append('tipo', 'Pedir agua');
            formData.append('mesa', new URLSearchParams(window.location.search).get('mesa')); // Obtener el número de mesa de la URL

            fetch('GuardarNotificacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.text();
                })
                .then(data => {
                    mostrarMensaje('Notificación', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Error', 'Error al conectar con el servidor.');
                });
        });
    </script>


    <script>
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
