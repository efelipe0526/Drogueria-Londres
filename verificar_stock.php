<?php
ob_start();
include 'db.php';

$db = new Database();
$producto_id = $_GET['producto_id'] ?? 0;
$laboratorio_id = $_GET['laboratorio_id'] ?? 0;
$cantidad = $_GET['cantidad'] ?? 1;

header('Content-Type: application/json');
ob_end_clean();

if (!$producto_id || !$laboratorio_id) {
    echo json_encode(['error' => 'Producto o laboratorio no especificado']);
    exit;
}

$producto_id = (int)$producto_id;
$laboratorio_id = (int)$laboratorio_id;

$result_check = $db->query("SELECT COUNT(*) as count 
                           FROM producto_laboratorio 
                           WHERE producto_id = $producto_id AND laboratorio_id = $laboratorio_id");
$check = $result_check->fetch_assoc();
if ($check['count'] == 0) {
    echo json_encode(['error' => 'El producto no está asociado al laboratorio especificado']);
    exit;
}

$result = $db->query("SELECT p.stock_cajas, p.unidades_por_caja, p.unidades_restantes, p.fecha_vencimiento 
                      FROM productos p 
                      WHERE p.id = $producto_id");

if (!$result) {
    echo json_encode(['error' => 'Error al ejecutar la consulta SQL']);
    exit;
}

$producto = $result->fetch_assoc();

if (!$producto) {
    echo json_encode(['error' => 'Producto no encontrado']);
    exit;
}

$stock_cajas = (int)$producto['stock_cajas'];
$unidades_por_caja = (int)$producto['unidades_por_caja'];
$unidades_restantes = (int)$producto['unidades_restantes'];
$fecha_vencimiento = $producto['fecha_vencimiento'] ?: null;

$maneja_cajas = ($stock_cajas > 0 || $unidades_por_caja > 0);

$stock_disponible = ($stock_cajas * ($unidades_por_caja > 0 ? $unidades_por_caja : 1)) + $unidades_restantes;

if ($stock_disponible < $cantidad) {
    echo json_encode(['error' => 'Stock insuficiente']);
    exit;
}

$response = [
    'stock_cajas' => $stock_cajas,
    'unidades_por_caja' => $unidades_por_caja,
    'unidades_restantes' => $unidades_restantes,
    'fecha_vencimiento' => $fecha_vencimiento,
    'maneja_cajas' => $maneja_cajas,
    'stock_disponible' => $stock_disponible
];

echo json_encode($response);
