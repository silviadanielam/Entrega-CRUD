<?php
// Archivo: C:\laragon\www\crud-ibero\conexion.php
$servidor = "127.0.0.1";
$baseDatos = "ibero_tienda";
$usuario = "root";
$clave = ""; // En Laragon por defecto va vacío

try {
  $pdo = new PDO(
    "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
    $usuario,
    $clave,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  die("Error conectando a MySQL en Laragon: " . $e->getMessage());
}