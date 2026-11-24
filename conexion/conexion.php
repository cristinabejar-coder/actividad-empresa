<?php
// Conexión a MySQL mediante PDO con manejo de excepciones
$host = "localhost";
$db   = "empresa";
$user = "root";
$pass = ""; // Tu contraseña de MySQL (vacío por defecto en XAMPP/WAMP)

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
