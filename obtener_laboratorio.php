<?php
ob_start();
include 'db.php';

$db = new Database();
$producto_id = $_GET['producto_id'] ?? 0;

header('Content-Type: application/json');
ob_end_clean();

if (!$producto_id) {
    echo json_encode(['error' => 'Producto no especificado']);
    exit;
}

$producto_id = (int)$producto_id;

$result = $db->query("SELECT laboratorio_id 
                      FROM producto_laboratorio 
                      WHERE producto_id = $producto_id");

if (!$result) {
    echo json_encode(['error' => 'Error al ejecutar la consulta SQL']);
    exit;
}

$producto = $result->fetch_assoc();

if (!$producto) {
    echo json_encode(['error' => 'Producto no encontrado o no tiene laboratorio asignado']);
    exit;
}

echo json_encode(['laboratorio_id' => $producto['laboratorio_id']]);
