<?php
include 'db.php';

$db = new Database();
$conexion = $db->getConnection();

if (isset($_POST['codigo_barras'])) {
    $codigo_barras = $conexion->real_escape_string($_POST['codigo_barras']);

    // Consulta simplificada sin JOIN a laboratorios
    $query = "SELECT p.*, c.usa_cajas 
              FROM productos p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              WHERE p.codigo_barras = ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $codigo_barras);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
        // Si necesitas el laboratorio, puedes buscarlo manualmente después
        $laboratorio_id = null;
        $laboratorio_nombre = 'Sin laboratorio';
        if ($producto['laboratorio_id'] ?? false) { // Verifica si existe laboratorio_id
            $lab_query = $conexion->prepare("SELECT id, nombre FROM laboratorios WHERE id = ? LIMIT 1");
            $lab_query->bind_param("i", $producto['laboratorio_id']);
            $lab_query->execute();
            $lab_result = $lab_query->get_result();
            if ($lab_result->num_rows > 0) {
                $lab_data = $lab_result->fetch_assoc();
                $laboratorio_id = $lab_data['id'];
                $laboratorio_nombre = $lab_data['nombre'];
            }
            $lab_query->close();
        }
        $producto['laboratorio_id'] = $laboratorio_id;
        $producto['laboratorio_nombre'] = $laboratorio_nombre;
        echo json_encode($producto);
    } else {
        echo json_encode(['error' => 'Producto no encontrado con el código de barras proporcionado.']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No se proporcionó un código de barras.']);
}

$conexion->close();
