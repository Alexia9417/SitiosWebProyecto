<?php
$host = '127.0.0.1';
$port = '3306';
$dbname = 'sitio'; // Cambiado a 'persona' para que coincida con el contexto
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
$conn = $pdo;
?>