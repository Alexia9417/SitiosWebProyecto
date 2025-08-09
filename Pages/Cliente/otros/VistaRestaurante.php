<?php
include '../../conexion.php';

// Obtener número de mesa desde la URL
$numeroMesa = $_GET['mesa'] ?? 1;

// Buscar ID de mesa y datos asociados
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

// Calcular tiempo desde la última orden
$consultaOrden = $conn->prepare("
  SELECT FechaHora FROM Orden
  WHERE IdMesa = ?
  ORDER BY FechaHora DESC
  LIMIT 1
");
$consultaOrden->bind_param("i", $datosMesa['IdMesa']);
$consultaOrden->execute();
$resOrden = $consultaOrden->get_result();

$tiempo = 'N/A';
if ($resOrden->num_rows > 0) {
  $orden = $resOrden->fetch_assoc();
  $inicio = new DateTime($orden['FechaHora']);
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
  <title>Vista Restaurante</title>
  <link rel="stylesheet" href="Css/estilos.css" />
</head>

<body>
  <header>
    <h1>Mesa #<?= htmlspecialchars($numeroMesa) ?></h1>
    <span id="hora">Hora:</span>
    <!-- Botón para cerrar sesión -->
        <a href="../logout.php" class="cerrar-sesion">
            <i class="fa-solid fa-right-from-bracket"></i>
            Cerrar Sesión
        </a>
  </header>

  <nav class="nav-secundario" id="nav-mesas">
    <a class="btn-nav" href="MiOrden.php?mesa=<?= $numeroMesa ?>">Mi Orden</a>
    <a class="btn-nav" href="VistaRestaurante.php?mesa=<?= $numeroMesa ?>">Vista Restaurante</a>
    <a class="btn-nav" href="Acciones.php?mesa=<?= $numeroMesa ?>">Acciones</a>
    <a class="btn-nav" href="Redes.php?mesa=<?= $numeroMesa ?>">Redes</a>
    <a class="btn-nav" href="Pagar.php?mesa=<?= $numeroMesa ?>">Pagar</a>
  </nav>

  <h2 id="MesasOcupadas">Vista en Tiempo Real</h2>
  <p class="Sub-titulo">Eventos y notificaciones del Restaurante</p>

  <div class="contenedor-principal">
    <div class="columna-izquierda">
      <div class="cuadro-izquierda">
        <h3>Notificaciones Activas</h3>
        <!-- Puedes colocar alertas o mensajes aquí -->
      </div>
    </div>

    <div class="columna-derecha">
      <div class="cuadro-derecha">
        <h3>Estado del Local</h3>
        <p><i class="fa-solid fa-temperature-three-quarters"></i> Temperatura: 22°C</p>
        <p><i class="fa-solid fa-user-gear"></i> Ocupación: 75%</p>
        <p><i class="fa-solid fa-clock"></i> Servicio: Rápido</p>
        <p><i class="fa-solid fa-volume-high"></i> Ruido: Medio</p>
      </div>

      <div class="cuadro-derecha">
        <h3>Mesa #<?= htmlspecialchars($numeroMesa) ?></h3>
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
    // Mostrar hora actual
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
