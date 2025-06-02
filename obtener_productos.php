<?php
include 'db.php';
$db = new Database();

$categoria_id = $_GET['categoria_id'] ?? '';

if ($categoria_id) {
    $result = $db->query("SELECT id, nombre, precio, precio_por_unidad, stock_cajas, unidades_por_caja, unidades_restantes 
                          FROM productos WHERE categoria_id = $categoria_id");
    $productos = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($productos)) {
        echo json_encode([]);
    } else {
        echo json_encode($productos);
    }
} else {
    echo json_encode(['error' => 'ID de categoría no proporcionado']);
}
