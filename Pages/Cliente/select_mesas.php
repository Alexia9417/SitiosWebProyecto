<?php
require_once '../../conexion.php';

$sql = "SELECT Numero, Ubicacion, Capacidad, IdCliente, IdMesero FROM Mesa ORDER BY Numero";
$resultado = $conn->query($sql);

$mesas = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $mesas[] = [
            "numero" => $fila["Numero"],
            "ubicacion" => $fila["Ubicacion"],
            "capacidad" => $fila["Capacidad"],
            "estado" => is_null($fila["IdCliente"]) ? "disponible" : "ocupada",
            "idMesero" => $fila["IdMesero"]
        ];
    }

    header("Content-Type: application/json");
    echo json_encode($mesas);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Error en la consulta: " . $conn->error]);
}
