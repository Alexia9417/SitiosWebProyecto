<?php
session_start();
include '../../conexion.php';

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.php");
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

// Obtener tarjeta registrada si existe
$tarjetaRegistrada = null;
$stmt = $conn->prepare("SELECT NombreTitular, NumeroTarjeta, FechaVenc FROM MetodoPago WHERE IdUsuario = ? LIMIT 1");
$stmt->bind_param("i", $IdUsuario);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $tarjetaRegistrada = $res->fetch_assoc();
}

// Variable para indicar si el usuario tiene una tarjeta registrada
$tieneTarjeta = $tarjetaRegistrada !== null;

// Obtener datos del pedido desde la base de datos
$pedido = [];
$subtotal = 0;

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

// Obtener el IdMesa basado en el número de mesa
$stmt = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
$stmt->bind_param("i", $numeroMesa);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("Mesa no encontrada.");
}
$filaMesa = $res->fetch_assoc();
$idMesa = $filaMesa['IdMesa'];

// Verificar si hay una orden activa para la mesa
$tienePedido = false;
$stmt = $conn->prepare("SELECT IdOrden, Estado FROM Orden WHERE IdMesa = ? AND Estado IN ('Pendiente', 'Confirmado', 'En preparación') ORDER BY Fecha DESC LIMIT 1");
$stmt->bind_param("i", $idMesa);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $orden = $res->fetch_assoc();
    $tienePedido = true;
    $idOrdenActual = $orden['IdOrden'];
}
//Obtener deuda del cliente
$stmt = $conn->prepare("SELECT Deuda FROM deudas WHERE IdUsuario = ?");
$stmt->bind_param("i", $IdUsuario);
$stmt->execute();
$res = $stmt->get_result();
$deuda = 0;
if ($res->num_rows > 0) {
    $deuda = (float)$res->fetch_assoc()['Deuda'];
}
$stmt->close();

// Obtener datos del pedido desde la base de datos
$pedido = [];
$subtotal = 0;

if ($tienePedido) {
    $stmt = $conn->prepare("
        SELECT p.Nombre, p.Precio, do.Cantidad
        FROM detalle_orden do
        JOIN Platillo p ON do.IdPlatillo = p.IdPlatillo
        WHERE do.IdOrden = ?
    ");
    $stmt->bind_param("i", $idOrdenActual);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $pedido[] = $row;
            $subtotal += $row['Precio'] * $row['Cantidad'];
        }
    }
}
$propina = round($subtotal * 0.1, 2);
$total = $subtotal + $propina;


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
    <title>Pagar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Css/estilos.css" />
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

        /* Adaptaciones para el resumen y modales */
        .contenedor-pagos {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .cuadro-resumen,
        .cuadro-metodo-pago {
            height: 300px;
            /* Ancho fijo manual: */
            width: 550px;
            /* Cambia aquí al ancho que quieras */
            box-sizing: border-box;
            background: #BFBCE9;
            padding: 1rem;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(240, 140, 140, 0.1);
        }

        .cuadro-metodo-pago {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .cuadro-resumen ul {
            list-style: disc inside;
            margin: 0.5rem 0 1rem 0;
            padding-left: 1rem;
        }

        .cuadro-resumen ul li {
            margin-bottom: 0.3rem;
        }

        .linea-divisora {
            border-top: 2px solid #333;
            /* línea divisora más oscura y un poco más gruesa */
            margin: 0.5rem 0 1rem 0;
        }

        .cuadro-metodo-pago button {
            margin-top: 1rem;
            padding: 0.7rem 1.4rem;
            font-size: 1rem;
            background-color: #4e5d83;
            border-color: #333;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .cuadro-metodo-pago button:hover {
            background-color: #6375a5ff;
        }

        /* Modales */
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
            background: white;
            border-radius: 6px;
            padding: 1.5rem;
            width: 90%;
            max-width: 400px;
            position: relative;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .modal h3 {
            margin-top: 0;
        }

        .cerrar {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 1.3rem;
            color: #999;
        }

        .cerrar:hover {
            color: #333;
        }

        .btn-grupo {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .btn-grupo button {
            flex: 1;
            padding: 0.5rem;
            cursor: pointer;
        }

        label {
            display: block;
            margin: 0.5rem 0 0.2rem 0;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 0.4rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .mensaje {
            margin-top: 0.8rem;
            color: green;
            font-weight: bold;
        }

        .factura {
            background: #eee;
            padding: 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 0.8rem;
            text-align: left;
        }

        .Btn-Cancelar {
            background-color: #E9C89A;
            color: black;
            border-radius: 3px;
            border-color: #333;
        }

        .Btn-Cancelar:hover {
            background-color: #a58e6fff;
        }

        .Btn-Confirmar {
            background-color: #4ca0afff;
            color: white;
            border-radius: 3px;
            border-color: #333;
        }

        .Btn-Metodo {
            background-color: #4e5d83;
            color: white;
            border-radius: 3px;
            border-color: #eee;
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
</head>

<body>

    <header>
        <h1>Mesa #<?= htmlspecialchars($numeroMesa) ?></h1>
        <span id="hora"></span>
        <!-- Botón para cerrar sesión -->
        <!-- <a href="logout_cliente.php" class="cerrar-sesion">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </a> -->
    </header>

    <nav class="nav-secundario" id="nav-mesas">
        <a class="btn-nav" href="MiOrden.php?mesa=<?= htmlspecialchars($numeroMesa) ?>">Mi Orden</a>
        <!-- <a class="btn-nav" href="VistaRestaurante.php?mesa=<?= htmlspecialchars($numeroMesa) ?>">Vista Restaurante</a> -->
        <a class="btn-nav" href="Acciones.php?mesa=<?= htmlspecialchars($numeroMesa) ?>">Acciones</a>
        <a class="btn-nav" href="Redes.php?mesa=<?= htmlspecialchars($numeroMesa) ?>">Redes</a>
        <a class="btn-nav" href="Pagar.php?mesa=<?= htmlspecialchars($numeroMesa) ?>">Pagar</a>
    </nav>

    <h2 id="MesasOcupadas">Pagar</h2>

    <div class="contenedor-principal">
        <div class="contenedor-pagos">
            <div class="cuadro-resumen">
                <h3>Resumen de la cuenta</h3>
                <?php if (empty($pedido)): ?>
                    <p>Esperando su orden</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($pedido as $item): ?>
                            <li><?= htmlspecialchars($item['Nombre']) ?> — ₡<?= number_format($item['Precio'], 2) ?> x <?= $item['Cantidad'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="linea-divisora"></div>
                    <p>Subtotal: ₡<?= number_format($subtotal, 2) ?></p>
                    <p><strong>Total: ₡<?= number_format($total, 2) ?></strong></p>
                <?php endif; ?>
            </div>

            <div class="cuadro-metodo-pago">
                <h2>Método de Pago</h2>
                <button id="btnAbrirModalMetodoPago">Tarjeta de Crédito</button>
            </div>
        </div>

        <div class="columna-derecha">
            <!-- <div class="cuadro-derecha">
                <h3>Estado del Local</h3>
                <p><i class="fa-solid fa-temperature-three-quarters"></i> Temperatura</p>
                <p><i class="fa-solid fa-user-gear"></i> Ocupación</p>
                <p><i class="fa-solid fa-clock"></i> Servicio</p>
                <p><i class="fa-solid fa-volume-high"></i> Ruido</p>
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

    <!-- Modales -->

    <!-- Modal seleccionar método pago -->
    <div id="modalMetodoPago" class="modal">
        <div class="modal-contenido">
            <span class="cerrar">&times;</span>
            <h3>Método de Pago</h3>
            <p>Selecciona cómo deseas pagar:</p>
            <div class="btn-grupo">
                <button id="btnUsarTarjetaRegistrada" class="Btn-Metodo">Usar Tarjeta Registrada</button>
                <button class="Btn-Metodo btnNuevaTarjeta">Nueva Tarjeta</button>
                <button id="btnCancelarMetodoPago" class="Btn-Cancelar">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal para informar que no hay tarjeta registrada -->
    <div id="modalSinTarjeta" class="modal">
        <div class="modal-contenido">
            <span class="cerrar">&times;</span>
            <h2>No tiene una tarjeta registrada</h2>
            <p>Por favor, registre una nueva tarjeta para continuar con el pago.</p>
            <div class="btn-grupo">
                <button class="Btn-Metodo btnNuevaTarjeta">Agregar Tarjeta</button>
            </div>
        </div>
    </div>

    <!-- Modal factura (pago con tarjeta registrada) -->
    <div id="modalFactura" class="modal">
        <div class="modal-contenido" id="contenidoFactura">
            <span class="cerrar">&times;</span>
            <h3>Factura</h3>
            <div class="factura" id="facturaDetalles">
                <p><strong>Restaurante:</strong> Restaurante La Capuzzella</p>
                <p><strong>Mesa:</strong> <?= htmlspecialchars($numeroMesa) ?></p>
                <p><strong>Pedido:</strong></p>
                <ul>
                    <?php foreach ($pedido as $item): ?>
                        <li><?= htmlspecialchars($item['Nombre']) ?> — ₡<?= number_format($item['Precio'], 2) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Subtotal: ₡<?= number_format($subtotal, 2) ?></p>
                <p><strong>Deuda: ₡<?= number_format($deuda, 2) ?></strong></p>
                <p><strong>Total: ₡<?= number_format($total + $deuda, 2) ?></strong></p>
                <p><strong>Método de pago:</strong> Tarjeta de Crédito</p>
                <p><strong>Titular:</strong> <?= htmlspecialchars($tarjetaRegistrada['NombreTitular'] ?? '') ?></p>
                <p><strong>Tarjeta:</strong> <?= htmlspecialchars($tarjetaRegistrada ? str_repeat('*', 12) . substr($tarjetaRegistrada['NumeroTarjeta'], -4) : '') ?></p>
                <p><strong>Vencimiento:</strong> <?= htmlspecialchars($tarjetaRegistrada['FechaVenc'] ?? '') ?></p>
            </div>
            <div class="btn-grupo" style="margin-top: 1rem;">
                <button id="btnDescargarFactura">Descargar</button>
                <button id="btnConfirmarPago" class="Btn-Confirmar">Confirmar Pago</button>
                <button class="Btn-Cancelar cerrar">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal pago exitoso -->
    <div id="modalPagoExitoso" class="modal">
        <div class="modal-contenido">
            <h2><i class="fa-solid fa-circle-check" style="color:black; text-align:center;"></i> Pago realizado con éxito</h2>
            <p>La página se recargará en unos segundos.</p>
        </div>
    </div>


    <!-- Modal mensaje de error -->
    <div id="modalMensajeError" class="modal">
        <div class="modal-contenido">
            <span class="cerrar">&times;</span>
            <h3>Error</h3>
            <p id="mensajeError"></p>
            <button class="Btn-Cancelar cerrar">Cerrar</button>
        </div>
    </div>

    <!-- Modal nueva tarjeta -->
    <div id="modalNuevaTarjeta" class="modal">
        <div class="modal-contenido">
            <span class="cerrar">&times;</span>
            <h3>Registrar Nueva Tarjeta</h3>
            <form id="formNuevaTarjeta">
                <label for="nombreTitular" required>Nombre del Titular</label>
                <input type="text" id="nombreTitular" name="nombreTitular" required placeholder="Ej. Juan Pérez" />
                <label for="numeroTarjeta" required>Número de Tarjeta</label>
                <input type="text" id="numeroTarjeta" name="numeroTarjeta" maxlength="19" minlength="13" pattern="(\d{4}\s?){3,5}" placeholder="Ej. 1234 5678 9012 3456" />
                <label for="fechaVenc" required>Fecha de Vencimiento (MM/AA)</label>
                <input type="text" id="fechaVenc" name="fechaVenc" required pattern="\d{2}/\d{2}" placeholder="Ej. 08/27" />
                <div style="margin-top:1rem;">
                    <button type="submit" class="Btn-Metodo">Guardar Tarjeta</button>
                    <button type="button" class="Btn-Cancelar cerrar">Cancelar</button>
                </div>
                <div id="mensajeGuardado" class="mensaje"></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <!-- Script para manejar los modales -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            //variable
            const total = <?= json_encode($total) ?>;
            const numeroMesa = <?= json_encode($numeroMesa) ?>;

            // Obtener modales
            const modalMetodoPago = document.getElementById('modalMetodoPago');
            const modalFactura = document.getElementById('modalFactura');
            const modalPagoExitoso = document.getElementById('modalPagoExitoso');
            const modalNuevaTarjeta = document.getElementById('modalNuevaTarjeta');
            const modalSinTarjeta = document.getElementById('modalSinTarjeta');
            const modalMensajeError = document.getElementById('modalMensajeError');

            // Botones principales
            const btnAbrirModalMetodoPago = document.getElementById('btnAbrirModalMetodoPago'); // botón que abre el modal Método de Pago
            const btnUsarTarjetaRegistrada = document.getElementById('btnUsarTarjetaRegistrada');
            const btnNuevaTarjeta = document.querySelectorAll('#btnNuevaTarjeta'); // Hay dos botones con ese id? Mejor select all
            const btnCancelarMetodoPago = document.getElementById('btnCancelarMetodoPago');
            const btnDescargarFactura = document.getElementById('btnDescargarFactura');
            const btnConfirmarPago = document.getElementById('btnConfirmarPago');
            const btnAgregarTarjetaSinTarjeta = modalSinTarjeta.querySelector('button'); // botón "Agregar Tarjeta" en modalSinTarjeta
            const btnCerrarNuevaTarjeta = modalNuevaTarjeta.querySelectorAll('.cerrar, #cerrar'); // Cerrar X y botón cancelar del modal nueva tarjeta

            // Variable PHP inyectada (tiene tarjeta?)
            const tieneTarjeta = <?= $tieneTarjeta ? 'true' : 'false' ?>;

            // Funciones para abrir y cerrar modales
            function abrir(modal) {
                if (modal) modal.classList.add('active');
            }

            document.querySelectorAll('.btnNuevaTarjeta').forEach(btn => {
                btn.addEventListener('click', () => {
                    cerrar(modalMetodoPago); // cierra método pago si está abierto
                    cerrar(modalSinTarjeta); // cierra "sin tarjeta" si está abierto
                    abrir(modalNuevaTarjeta); // abre modal nueva tarjeta
                });
            });

            function cerrar(modal) {
                if (modal) modal.classList.remove('active');
            }

            // Función para cerrar todos los modales (por si quieres limpiar antes de abrir uno)
            function cerrarTodos() {
                [modalMetodoPago, modalFactura, modalPagoExitoso, modalNuevaTarjeta, modalSinTarjeta, modalMensajeError].forEach(cerrar);
            }

            // Cerrar modales al hacer click en las X (.cerrar)
            document.querySelectorAll('.modal .cerrar').forEach(spanCerrar => {
                spanCerrar.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    cerrar(modal);
                    if (modal === modalNuevaTarjeta) {
                        document.getElementById('formNuevaTarjeta').reset();
                        document.getElementById('mensajeGuardado').textContent = '';
                    }
                });
            });

            // Cerrar modal MetodoPago con botón Cancelar
            if (btnCancelarMetodoPago) {
                btnCancelarMetodoPago.addEventListener('click', () => cerrar(modalMetodoPago));
            }

            // Abrir modal MetodoPago (botón que tienes en el header o donde sea)
            if (btnAbrirModalMetodoPago) {
                btnAbrirModalMetodoPago.addEventListener('click', () => abrir(modalMetodoPago));
            }

            // Botón Usar Tarjeta Registrada
            if (btnUsarTarjetaRegistrada) {
                btnUsarTarjetaRegistrada.addEventListener('click', () => {
                    cerrar(modalMetodoPago);
                    if (!tieneTarjeta) {
                        abrir(modalSinTarjeta);
                    } else {
                        abrir(modalFactura);
                    }
                });
            }

            // Botón Nueva Tarjeta (hay dos botones con id btnNuevaTarjeta en diferentes modales, por eso seleccionamos todos)
            btnNuevaTarjeta.forEach(btn => {
                btn.addEventListener('click', () => {
                    cerrarTodos();
                    abrir(modalNuevaTarjeta);
                });
            });

            // Botón Cancelar y cerrar en modal Nueva Tarjeta (X y botón Cancelar)
            btnCerrarNuevaTarjeta.forEach(btnCerrar => {
                btnCerrar.addEventListener('click', () => {
                    cerrar(modalNuevaTarjeta);
                    document.getElementById('formNuevaTarjeta').reset();
                    document.getElementById('mensajeGuardado').textContent = '';
                });
            });

            // Botón Agregar Tarjeta en modal Sin Tarjeta
            if (btnAgregarTarjetaSinTarjeta) {
                btnAgregarTarjetaSinTarjeta.addEventListener('click', () => {
                    cerrar(modalSinTarjeta);
                    abrir(modalNuevaTarjeta);
                });
            }

            // Descargar factura
            if (btnDescargarFactura) {
                btnDescargarFactura.addEventListener('click', () => {
                    html2canvas(document.getElementById('contenidoFactura')).then(canvas => {
                        const enlace = document.createElement('a');
                        enlace.download = `factura_mesa_<?= htmlspecialchars($numeroMesa) ?>.png`;
                        enlace.href = canvas.toDataURL();
                        enlace.click();
                    });
                });
            }

            // Confirmar pago
            btnConfirmarPago.addEventListener('click', () => {
                console.log("Iniciando proceso de pago...");
                fetch('procesar_pago.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `total=${total}&mesa=${encodeURIComponent(numeroMesa)}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json(); // Cambiado a .json() para manejar la respuesta como JSON
                    })
                    .then(data => {
                        console.log("Respuesta del servidor:", data);
                        if (data.status === "success") { // Verifica el campo "status" en la respuesta JSON
                            cerrar(modalFactura);
                            abrir(modalPagoExitoso);
                            setTimeout(() => {
                                console.log("Recargando la página...");
                                window.location.reload();
                            }, 2000);
                        } else {
                            abrirModalError(data.message || "Error desconocido");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        abrirModalError('Error al conectar con el servidor');
                    });
            });

            // Mostrar modal error
            function abrirModalError(mensaje) {
                const modalError = modalMensajeError;
                const mensajeError = modalError.querySelector('#mensajeError');
                if (mensajeError) mensajeError.textContent = mensaje;
                abrir(modalError);
            }

            // Cerrar modal error al clicar cerrar o botón
            modalMensajeError.querySelectorAll('.cerrar, button.Btn-Cancelar').forEach(btn => {
                btn.addEventListener('click', () => cerrar(modalMensajeError));
            });

            // Guardar nueva tarjeta con fetch AJAX
            const formNuevaTarjeta = document.getElementById('formNuevaTarjeta');
            if (formNuevaTarjeta) {
                formNuevaTarjeta.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('GuardarTarjeta.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.text())
                        .then(data => {
                            const msg = document.getElementById('mensajeGuardado');
                            if (data.toLowerCase().includes('éxito')) {
                                msg.style.color = 'green';
                                msg.textContent = data;
                                setTimeout(() => {
                                    cerrar(modalNuevaTarjeta);
                                    window.location.reload();
                                }, 1500);
                            } else {
                                msg.style.color = 'red';
                                msg.textContent = data;
                            }
                        })
                        .catch(() => alert('Error al conectar con el servidor'));
                });
            }

        });
    </script>

    <script>
        const inputTarjeta = document.getElementById('numeroTarjeta');

        inputTarjeta.addEventListener('input', function(e) {
            let valor = e.target.value;

            // Eliminar todo lo que no sea dígito
            valor = valor.replace(/\D/g, '');

            // Limitar a 16 dígitos (opcional, algunas tarjetas tienen 13-19)
            valor = valor.substring(0, 16);

            // Agregar espacios cada 4 dígitos
            valor = valor.replace(/(.{4})/g, '$1 ').trim();

            // Asignar el valor formateado al input
            e.target.value = valor;
        });
    </script>

    <!-- Script para manejar la hora actual -->
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