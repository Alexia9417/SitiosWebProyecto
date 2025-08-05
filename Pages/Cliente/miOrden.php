<?php
session_start();
include '../../conexion.php';

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

//insertar IdCliente en bd
if ($numeroMesa) {
  // Actualizar la mesa con el IdCliente
  $stmt = $conn->prepare("UPDATE Mesa SET IdCliente = ? WHERE Numero = ?");
  $stmt->bind_param("ii", $IdUsuario, $numeroMesa);
  $stmt->execute();
  $stmt->close();
} else {
  die("Número de mesa no proporcionado.");
}

// Buscar ID de la mesa
$consultaMesa = $conn->prepare("SELECT IdMesa FROM Mesa WHERE Numero = ?");
$consultaMesa->bind_param("i", $numeroMesa);
$consultaMesa->execute();
$resMesa = $consultaMesa->get_result();
if ($resMesa->num_rows === 0) {
  die("Mesa no encontrada.");
}
$filaMesa = $resMesa->fetch_assoc();
$idMesa = $filaMesa['IdMesa'];

// Buscar si hay una orden activa
$consultaOrden = $conn->prepare("SELECT IdOrden, Estado, Fecha FROM Orden WHERE IdMesa = ? ORDER BY Fecha DESC LIMIT 1");
$consultaOrden->bind_param("i", $idMesa);
$consultaOrden->execute();
$resOrden = $consultaOrden->get_result();
$tienePedido = false;
$capacidad = "No disponible";
$ubicacion = "Desconocida";
$nombreMesero = "No asignado";
$tiempoAtencion = "Desconocido";

// Si hay una orden activa, obtener detalles
if ($resOrden->num_rows > 0) {
  $orden = $resOrden->fetch_assoc();
  if ($orden['Estado'] === 'Confirmado' || $orden['Estado'] === 'Pendiente') {
    $tienePedido = true;
    $idOrdenActual = $orden['IdOrden'];
    // Calcular tiempo de atención
    $fechaHoraOrden = new DateTime($orden['Fecha']);
    $ahora = new DateTime();
    $intervalo = $fechaHoraOrden->diff($ahora);
    $minutos = ($intervalo->h * 60) + $intervalo->i;
    $tiempoAtencion = $minutos . " min";
  }
}

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

// // Obtener siempre info de la mesa
$consultaMesaInfo = $conn->prepare("SELECT Capacidad, Ubicacion, IdMesero FROM mesa WHERE IdMesa = ?");
$consultaMesaInfo->bind_param("i", $idMesa);
$consultaMesaInfo->execute();
$resMesaInfo = $consultaMesaInfo->get_result();
if ($resMesaInfo->num_rows > 0) {
  $filaMesaInfo = $resMesaInfo->fetch_assoc();
  $capacidad = $filaMesaInfo['Capacidad'];
  $ubicacion = $filaMesaInfo['Ubicacion'];
  $idMesero = $filaMesaInfo['IdMesero'];
  // Buscar nombre del mesero si existe
  if (!empty($idMesero)) {
    $consultaMesero = $conn->prepare("
      SELECT Nombre, Apellidos FROM Usuario WHERE IdUsuario = ?
    ");
    $consultaMesero->bind_param("i", $idMesero);
    $consultaMesero->execute();
    $resMesero = $consultaMesero->get_result();
    if ($resMesero->num_rows > 0) {
      $mesero = $resMesero->fetch_assoc();
      $nombreMesero = $mesero['Nombre'] . " " . $mesero['Apellidos'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Orden</title>
  <link rel="stylesheet" href="Css/estilos.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /*Boton para cerrar sesion*/
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

    .main-container {
      display: flex;
      gap: 1rem;
      margin: 1.5rem;
    }

    .left-section {
      flex: 2;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .right-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .bloque-info,
    .bloque-pedido,
    .bloque-estado {
      background-color: #BFBCE9;
      color: #333;
      padding: 1rem;
      border-radius: 6px;
      box-sizing: border-box;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pedido-estado-container {
      display: flex;
      gap: 1rem;
    }

    .pedido-izquierda,
    .estado-centro {
      flex: 1;
    }

    .categoria-bloque {
      margin-bottom: 2rem;
      max-width: 1200px;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 10px;
      background-color: #f0f0f5;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 0;
    }

    .categoria-bloque h3 {
      font-size: 1.5rem;
      color: #000;
      background-color: #BFBCE9;
      padding: 10px 20px;
      margin: 0;
      border-radius: 10px 10px 0 0;
    }

    .platillos {
      background-color: #4e5d83;
      padding: 10px 20px;
      border-radius: 0 0 10px 10px;
    }

    .platillo-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 15px;
      margin-bottom: 10px;
      background-color: #5b6a9a;
      border-radius: 6px;
      color: white;
      font-weight: 600;
    }

    .contador {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      background-color: white;
      border-radius: 5px;
      padding: 5px;
    }

    .btn-restar,
    .btn-sumar {
      background-color: #f1d9a7;
      border: none;
      padding: 5px 12px;
      border-radius: 3px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1.1rem;
      user-select: none;
    }

    .cantidad {
      margin: 0 10px;
      color: #333;
      font-weight: bold;
      min-width: 20px;
      text-align: center;
    }

    .btn-continuar {
      display: block;
      width: 100%;
      padding: 15px;
      background-color: #4e5d83;
      color: white;
      font-size: 16px;
      text-align: center;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 30px;
      transition: background-color 0.3s;
    }

    .btn-continuar:hover {
      background-color: #3a4561;
    }

    .resumen-pedido {
      background-color: #BFBCE9;
      padding: 1rem;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-contenido {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 50%;
      border-radius: 10px;
    }

    .cerrar {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .cerrar:hover,
    .cerrar:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .btn-resumen {
      display: none;
      /* Inicialmente oculto */
      width: auto;
      /* Ajusta el ancho del botón según su contenido */
      margin-left: 10px;
      /* Espacio entre el título y el botón */
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
    <a class="btn-nav" href="MiOrden.php?mesa=<?= $numeroMesa ?>">Mi Orden</a>
    <a class="btn-nav" href="Acciones.php?mesa=<?= $numeroMesa ?>">Acciones</a>
    <a class="btn-nav" href="Redes.php?mesa=<?= $numeroMesa ?>">Redes</a>
    <a class="btn-nav" href="Pagar.php?mesa=<?= $numeroMesa ?>">Pagar</a>
  </nav>
  <h2 class="Sub-titulo">
    <?= $tienePedido ? 'Mi Pedido' : 'Menú del Restaurante' ?>
    <?php if (!$tienePedido): ?>
      <button id="btn-resumen" class="btn-resumen btn-continuar" onclick="abrirModalResumen()">Resumen del Pedido</button>
    <?php endif; ?>
  </h2>
  <div class="main-container">
    <div class="left-section">
      <?php if ($tienePedido): ?>
        <div class="pedido-estado-container">
          <div class="pedido-izquierda">
            <div class="bloque-pedido">
              <h3>Pedido Actual</h3>
              <ul>
                <?php
                $detalle = $conn->prepare("
                  SELECT P.Nombre, D.Cantidad, P.Precio
                  FROM detalle_orden D
                  INNER JOIN platillo P ON D.IdPlatillo = P.IdPlatillo
                  WHERE D.IdOrden = ?
                ");
                $detalle->bind_param("i", $idOrdenActual);
                $detalle->execute();
                $resDetalle = $detalle->get_result();
                $total = 0;
                while ($item = $resDetalle->fetch_assoc()):
                  $subtotal = $item['Cantidad'] * $item['Precio'];
                  $total += $subtotal;
                ?>
                  <li><?= htmlspecialchars($item['Nombre']) ?> x<?= $item['Cantidad'] ?> - ₡<?= number_format($subtotal, 2) ?></li>
                <?php endwhile; ?>
              </ul>
              <p><strong>Total:</strong> ₡<?= number_format($total, 2) ?></p>
            </div>
          </div>
          <div class="estado-centro">
            <div class="bloque-estado">
              <h3>Estado del Pedido</h3>
              <p><i class="fa-solid fa-circle-check" style="color: green;"></i> Pedido confirmado</p>
              <p><i class="fa-solid fa-clock"></i> En preparación</p>
              <p>Tiempo estimado: 15-20 min</p>
              <button onclick="abrirModal()" class="btn-continuar">Pedir algo más</button>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div id="menu-restaurante">
          <?php include 'menu.php'; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="right-section">
      <div class="bloque-info">
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
    <!-- Modal para agregar platillos -->
    <div id="modal-menu" class="modal">
      <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModal()">&times;</span>
        <h3>Agregar Platillos</h3>
        <p>Puede seleccionar varios platillos y ajustar las cantidades!</p>
        <p>Seleccione los platillos que desea agregar a su orden:</p>
        <form action="AgregarPlatillos.php?mesa=<?= $numeroMesa ?>" method="POST">
          <input type="hidden" name="IdOrden" value="<?= $idOrdenActual ?>">
          <div class="lista-platillos">
            <?php
            $platillos = $conn->query("SELECT IdPlatillo, Nombre, Precio FROM Platillo");
            while ($p = $platillos->fetch_assoc()):
            ?>
              <div class="item-platillo">
                <label>
                  <input type="checkbox" name="platillos[]" value="<?= $p['IdPlatillo'] ?>">
                  <?= $p['Nombre'] ?> - ₡<?= number_format($p['Precio'], 2) ?>
                </label>
                <input type="number" name="cantidades[<?= $p['IdPlatillo'] ?>]" value="0" style="width: 60px;">
              </div>
            <?php endwhile; ?>
          </div>
          <button type="submit" class="btn-agregar">Agregar a la orden</button>
        </form>
      </div>
    </div>


    <!-- Modal para el resumen del pedido -->
    <div id="modal-resumen" class="modal">
      <div class="modal-contenido">
        <span class="cerrar" onclick="cerrarModalResumen()">&times;</span>
        <h3>Resumen del Pedido</h3>
        <ul id="lista-resumen"></ul>
        <p>Total: ₡<span id="total-pedido">0.00</span></p>
        <button id="btnConfirmar" class="btn-continuar">Confirmar pedido</button>
      </div>
    </div>

    <script>
      const idMesa = <?= $idMesa ?>;
    </script>

    <script>
      const ahora = new Date();
      const hora = ahora.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
      document.getElementById('hora').textContent = `Hora: ${hora}`;

      function abrirModal() {
        document.getElementById("modal-menu").style.display = "block";
      }

      function cerrarModal() {
        document.getElementById("modal-menu").style.display = "none";
      }

      function abrirModalResumen() {
        document.getElementById("modal-resumen").style.display = "block";
      }

      function cerrarModalResumen() {
        document.getElementById("modal-resumen").style.display = "none";
      }
      window.onclick = function(event) {
        const modalMenu = document.getElementById("modal-menu");
        const modalResumen = document.getElementById("modal-resumen");
        if (event.target == modalMenu) {
          modalMenu.style.display = "none";
        }
        if (event.target == modalResumen) {
          modalResumen.style.display = "none";
        }
      }
      // Script para manejar la selección de platillos y mostrar el botón de resumen
      document.querySelectorAll('.btn-sumar, .btn-restar').forEach(button => {
        button.addEventListener('click', function() {
          document.getElementById('btn-resumen').style.display = 'block';
        });
      });

      document.addEventListener('DOMContentLoaded', function() {
        const resumen = {};
        const listaResumen = document.getElementById('lista-resumen');
        const totalSpan = document.getElementById('total-pedido');

        // Verificar si los elementos existen
        if (!listaResumen || !totalSpan) {
          console.error('Uno o más elementos no se encontraron en el DOM.');
          return;
        }

        document.querySelectorAll('.platillo-item').forEach(item => {
          const id = item.dataset.id;
          const nombre = item.dataset.nombre;
          const precio = parseFloat(item.dataset.precio);
          const btnSumar = item.querySelector('.btn-sumar');
          const btnRestar = item.querySelector('.btn-restar');
          const cantidadSpan = item.querySelector('.cantidad');
          

          btnSumar.addEventListener('click', () => {
            console.log(`Botón sumar clickeado para el platillo: ${nombre}`);
            resumen[id] = resumen[id] ? resumen[id] + 1 : 1;
            cantidadSpan.textContent = resumen[id];
            actualizarResumen();
            const btnResumen = document.getElementById('btn-resumen');
            if (btnResumen) {
              btnResumen.style.display = 'block';
            }
          });

          btnRestar.addEventListener('click', () => {
            if (resumen[id]) {
              console.log(`Botón restar clickeado para el platillo: ${nombre}`);
              resumen[id]--;
              if (resumen[id] <= 0) {
                delete resumen[id];
              }
              cantidadSpan.textContent = resumen[id] || 0;
              actualizarResumen();
            }
          });
        });
        // Función para actualizar el contenido del moda
        function actualizarResumen() {
          console.log('Actualizando resumen...');
          listaResumen.innerHTML = '';
          let total = 0;

          Object.keys(resumen).forEach(id => {
            const item = document.querySelector(`.platillo-item[data-id="${id}"]`);
            if (item) {
              const nombre = item.dataset.nombre;
              const precio = parseFloat(item.dataset.precio);
              const cantidad = resumen[id];
              total += precio * cantidad;

              const li = document.createElement('li');
              li.textContent = `${nombre} x${cantidad} = ₡${(precio * cantidad).toFixed(2)}`;
              listaResumen.appendChild(li);
            }
          });

          totalSpan.textContent = total.toFixed(2);
          console.log(`Resumen actualizado. Total: ${total}`);
        }

        //confirmar pedido
        btnConfirmar.addEventListener('click', (event) => {
          event.preventDefault();
          let pedidoEnviado = false;
          if (pedidoEnviado) return; // Evitar doble envío
          pedidoEnviado = true;

          const mesa = new URLSearchParams(window.location.search).get('mesa');
          const productos = Object.keys(resumen).map(id => ({
            id: parseInt(id),
            cantidad: resumen[id]
          }));

          if (productos.length === 0) {
            alert("Debe seleccionar al menos un platillo.");
            pedidoEnviado = false;
            return;
          }

          fetch('guardar_pedido.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                mesa,
                productos
              })
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === "ok") {
                window.location.reload();
              } else {
                alert("Error: " + data.message);
                pedidoEnviado = false;
              }
            })
            .catch(err => {
              console.error(err);
              alert("Hubo un error al enviar el pedido.");
              pedidoEnviado = false;
            });
        });
      })
    </script>
    <?php if (!$tienePedido): ?>
      <script src="JS/menu.js"></script>
    <?php endif; ?>
</body>

</html>