<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include 'db.php';

$db = new Database();
$producto_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : 0;
$laboratorio_id = isset($_GET['laboratorio_id']) ? (int)$_GET['laboratorio_id'] : 0;

if ($producto_id <= 0 || $laboratorio_id <= 0) {
    echo json_encode(['error' => 'Producto o laboratorio no válido']);
    exit;
}

try {
    $stmt = $db->getConnection()->prepare("SELECT unidad_original FROM producto_laboratorio WHERE producto_id = ? AND laboratorio_id = ?");
    $stmt->bind_param("ii", $producto_id, $laboratorio_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['unidad_original' => $data['unidad_original']]);
    } else {
        echo json_encode(['error' => 'No se encontró asociación entre el producto y el laboratorio']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
