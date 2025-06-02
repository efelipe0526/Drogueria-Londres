<?php
// Iniciar el buffer de salida
ob_start();

// Habilitar errores para depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

require_once('tcpdf/tcpdf.php'); // Incluir la biblioteca TCPDF

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "marci");

// Verificar conexión
if ($mysqli->connect_error) {
    error_log("Error de conexión: " . $mysqli->connect_error);
    die("Error de conexión a la base de datos.");
}

// Obtener el porcentaje de impuesto desde la base de datos
$impuesto_query = $mysqli->query("SELECT porcentaje, numero_factura_inicial FROM configuracion_impuestos WHERE id = 1");
if ($impuesto_query && $impuesto_query->num_rows > 0) {
    $impuesto_data = $impuesto_query->fetch_assoc();
    $impuesto_porcentaje = $impuesto_data['porcentaje'] / 100; // Convertir a decimal
    $numero_factura = sprintf("%07d", $impuesto_data['numero_factura_inicial'] ?? 10); // Formato 0000010
} else {
    error_log("Error: No se encontró la configuración de impuestos.");
    die("Error: No se encontró la configuración de impuestos.");
}

// Procesar datos de la factura
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos obligatorios
    if (!isset($_POST['numero_factura'], $_POST['total'], $_POST['cliente_id'], $_POST['productos'], $_POST['forma_pago'])) {
        error_log("Error: Campos obligatorios faltantes. POST: " . print_r($_POST, true));
        die("Error: Los campos 'numero_factura', 'total', 'cliente_id', 'productos' y 'forma_pago' son obligatorios.");
    }

    $numero_factura = $_POST['numero_factura'];
    $total = floatval($_POST['total']);
    $cliente_id = intval($_POST['cliente_id']);
    $productos = json_decode($_POST['productos'], true);
    $forma_pago = intval($_POST['forma_pago']);
    $banco = $_POST['banco'] ?? null;
    $cuenta = !empty($_POST['cuenta']) ? $_POST['cuenta'] : null;
    $aplicar_impuesto = isset($_POST['aplicar_impuesto']) && $_POST['aplicar_impuesto'] == 1 ? 1 : 0;
    $dividir_pago = isset($_POST['dividir_pago']) && $_POST['dividir_pago'] == 1 ? 1 : 0;
    $monto_efectivo = floatval($_POST['monto_efectivo'] ?? 0);
    $monto_otra_forma_pago = floatval($_POST['monto_otra_forma_pago'] ?? 0);

    // Validar cliente_id
    if ($cliente_id <= 0) {
        error_log("Error: cliente_id inválido ($cliente_id). POST: " . print_r($_POST, true));
        die("Error: El ID del cliente es inválido.");
    }
    $stmt_cliente = $mysqli->prepare("SELECT id FROM clientes WHERE id = ?");
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $result = $stmt_cliente->get_result();
    if ($result->num_rows === 0) {
        error_log("Error: Cliente con ID $cliente_id no encontrado. POST: " . print_r($_POST, true));
        die("Error: El cliente especificado no existe.");
    }
    $stmt_cliente->close();

    // Validar productos
    if (!is_array($productos) || empty($productos)) {
        error_log("Error: Productos inválidos o vacíos. POST: " . print_r($_POST, true));
        die("Error: Los productos son inválidos o están vacíos.");
    }

    // Verificar si el número de factura ya existe
    $stmt_factura = $mysqli->prepare("SELECT id FROM ventas WHERE numero_factura = ? UNION SELECT id FROM reportes WHERE numero_factura = ?");
    $stmt_factura->bind_param("ss", $numero_factura, $numero_factura);
    $stmt_factura->execute();
    $result = $stmt_factura->get_result();
    if ($result->num_rows > 0) {
        ob_end_clean();
        echo "<!DOCTYPE html><html><head>";
        echo "<script src='alerta/sweetalert2.all.min.js'></script>";
        echo "</head><body>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El número de factura ya existe. Por favor, genere una nueva factura.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'ventas.php';
            });
        </script>";
        echo "</body></html>";
        $stmt_factura->close();
        exit();
    }
    $stmt_factura->close();

    // Calcular subtotal y validar precios contra la base de datos
    $subtotal = 0;
    foreach ($productos as $producto) {
        if (
            !isset($producto['cantidad'], $producto['usar_unidad'], $producto['id'], $producto['laboratorio_id']) ||
            ($producto['usar_unidad'] && !isset($producto['precio_por_unidad'])) ||
            (!$producto['usar_unidad'] && !isset($producto['precio_venta']))
        ) {
            error_log("Error: Producto inválido: " . print_r($producto, true));
            die("Error: Datos de producto incompletos. Falta laboratorio_id u otros campos requeridos.");
        }
        $cantidad = floatval($producto['cantidad']);
        $producto_id = intval($producto['id']);
        $laboratorio_id = intval($producto['laboratorio_id']);

        // Validar precio contra la base de datos
        $stmt_precio = $mysqli->prepare("SELECT precio, precio_por_unidad FROM productos WHERE id = ?");
        $stmt_precio->bind_param("i", $producto_id);
        $stmt_precio->execute();
        $result = $stmt_precio->get_result();
        if ($result->num_rows === 0) {
            error_log("Error: Producto ID {$producto_id} no encontrado en la base de datos.");
            die("Error: Producto ID {$producto_id} no encontrado.");
        }
        $db_producto = $result->fetch_assoc();
        $stmt_precio->close();

        if ($producto['usar_unidad']) {
            $precio_por_unidad = floatval($producto['precio_por_unidad']);
            if (abs($precio_por_unidad - $db_producto['precio_por_unidad']) > 0.01) {
                error_log("Error: Precio por unidad inválido para producto ID {$producto_id}: recibido $precio_por_unidad, esperado {$db_producto['precio_por_unidad']}");
                die("Error: Precio por unidad inválido para producto ID {$producto_id}.");
            }
            $subtotal += $cantidad * $precio_por_unidad;
        } else {
            $precio_venta = floatval($producto['precio_venta']);
            if (abs($precio_venta - $db_producto['precio']) > 0.01) {
                error_log("Error: Precio de venta inválido para producto ID {$producto_id}: recibido $precio_venta, esperado {$db_producto['precio']}");
                die("Error: Precio de venta inválido para producto ID {$producto_id}.");
            }
            $subtotal += $cantidad * $precio_venta;
        }

        // Advertencia para precios altos (solo para monitoreo, no detiene la operación)
        if (($producto['usar_unidad'] && $precio_por_unidad > 15000) || (!$producto['usar_unidad'] && $precio_venta > 15000)) {
            error_log("Advertencia: Precio alto para producto ID {$producto_id} (usar_unidad={$producto['usar_unidad']}, precio_por_unidad=" . ($producto['precio_por_unidad'] ?? 'N/A') . ", precio_venta=" . ($producto['precio_venta'] ?? 'N/A') . "): " . print_r($producto, true));
        }
    }

    // Calcular impuesto
    $impuesto = $aplicar_impuesto ? $subtotal * $impuesto_porcentaje : 0;
    $total_con_impuesto = $subtotal + $impuesto;

    // Validar que $_POST['total'] coincida con total_con_impuesto
    if (abs($total - $total_con_impuesto) > 0.01) {
        error_log("Error: Total enviado ($total) no coincide con el calculado ($total_con_impuesto). Productos: " . print_r($productos, true));
        die("Error: El total enviado no coincide con el total calculado.");
    }

    // Validar subtotal y total razonables
    if ($subtotal <= 0 || $total_con_impuesto <= 0) {
        error_log("Error: Subtotal ($subtotal) o total_con_impuesto ($total_con_impuesto) inválidos.");
        die("Error: El subtotal o total calculado es inválido.");
    }

    // Debug: Log variables before inserting
    error_log("Venta: numero_factura=$numero_factura, subtotal=$subtotal, impuesto=$impuesto, total_con_impuesto=$total_con_impuesto, cliente_id=$cliente_id, forma_pago=$forma_pago, banco=" . ($banco ?? 'NULL') . ", cuenta=" . ($cuenta ?? 'NULL') . ", monto_efectivo=$monto_efectivo, monto_otra_forma_pago=$monto_otra_forma_pago");
    error_log("POST total: $total, Calculated total_con_impuesto: $total_con_impuesto");
    error_log("POST data: " . print_r($_POST, true));

    // Iniciar transacción para asegurar consistencia
    $mysqli->begin_transaction();

    try {
        // Verificar y ajustar stock para cada producto
        foreach ($productos as $producto) {
            $producto_id = intval($producto['id']);
            $laboratorio_id = intval($producto['laboratorio_id']);
            $cantidad = floatval($producto['cantidad']);
            $usar_unidad = isset($producto['usar_unidad']) && ($producto['usar_unidad'] === true || $producto['usar_unidad'] === 'true' || $producto['usar_unidad'] === 1 || $producto['usar_unidad'] === '1');

            // Log del valor de cantidad recibido
            error_log("Producto recibido: producto_id=$producto_id, cantidad=$cantidad, usar_unidad=$usar_unidad");

            // Obtener información de usa_cajas desde la categoría
            $stmt_categoria = $mysqli->prepare("SELECT usa_cajas FROM categorias c JOIN productos p ON p.categoria_id = c.id WHERE p.id = ?");
            $stmt_categoria->bind_param("i", $producto_id);
            $stmt_categoria->execute();
            $result = $stmt_categoria->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Categoría no encontrada para producto ID {$producto_id}.");
            }
            $categoria_info = $result->fetch_assoc();
            $usa_cajas = $categoria_info['usa_cajas'] == 1;
            $stmt_categoria->close();

            if ($usa_cajas) {
                // Gestión por cajas y unidades por caja
                $stmt_stock = $mysqli->prepare("SELECT stock_cajas, unidades_por_caja, unidades_restantes, unidades_vendidas_acumuladas 
                                               FROM productos 
                                               WHERE id = ? FOR UPDATE");
                $stmt_stock->bind_param("i", $producto_id);
                $stmt_stock->execute();
                $result = $stmt_stock->get_result();
                if ($result->num_rows === 0) {
                    throw new Exception("No se encontró stock para producto ID {$producto_id}.");
                }
                $stock_data = $result->fetch_assoc();
                $stmt_stock->close();

                $stock_cajas = intval($stock_data['stock_cajas']);
                $unidades_por_caja = intval($stock_data['unidades_por_caja']);
                $unidades_restantes = intval($stock_data['unidades_restantes']);
                $unidades_vendidas_acumuladas = intval($stock_data['unidades_vendidas_acumuladas']);

                // Debug: Log inicial de stock
                error_log("Stock inicial: producto_id=$producto_id, stock_cajas=$stock_cajas, unidades_por_caja=$unidades_por_caja, unidades_restantes=$unidades_restantes, unidades_vendidas_acumuladas=$unidades_vendidas_acumuladas");

                // Calcular total de unidades disponibles
                $total_unidades = ($stock_cajas * $unidades_por_caja) + $unidades_restantes;
                error_log("Total unidades calculadas: producto_id=$producto_id, total_unidades=$total_unidades, cantidad_solicitada=$cantidad");

                // Validar stock disponible
                if ($cantidad > $total_unidades) {
                    throw new Exception("Stock insuficiente para producto ID {$producto_id}. Stock disponible: {$total_unidades} unidades, Cantidad solicitada: {$cantidad} unidades.");
                }

                if ($usar_unidad) {
                    // Descontar por unidades
                    $unidades_a_descontar = $cantidad;
                    $nueva_unidades_restantes = $unidades_restantes - $unidades_a_descontar;
                    $nuevo_stock_cajas = $stock_cajas;

                    // Actualizar acumulador de unidades vendidas
                    $unidades_vendidas_acumuladas += $cantidad;

                    // Reducir stock_cajas si las unidades vendidas acumuladas alcanzan un múltiplo de unidades_por_caja
                    while ($unidades_vendidas_acumuladas >= $unidades_por_caja && $nuevo_stock_cajas > 0) {
                        $nuevo_stock_cajas--;
                        $unidades_vendidas_acumuladas -= $unidades_por_caja;
                        error_log("Reducción de stock_cajas: producto_id=$producto_id, nuevo_stock_cajas=$nuevo_stock_cajas, unidades_vendidas_acumuladas=$unidades_vendidas_acumuladas");
                    }

                    // Convertir cajas si hay déficit en unidades_restantes
                    while ($nueva_unidades_restantes < 0 && $nuevo_stock_cajas > 0) {
                        $nuevo_stock_cajas--;
                        $nueva_unidades_restantes += $unidades_por_caja;
                        error_log("Conversión de caja: producto_id=$producto_id, nuevo_stock_cajas=$nuevo_stock_cajas, nuevas_unidades_restantes=$nueva_unidades_restantes");
                    }

                    // Validar que no haya valores negativos después del ajuste
                    if ($nueva_unidades_restantes < 0) {
                        throw new Exception("Stock insuficiente después de ajuste para producto ID {$producto_id}. Unidades restantes: {$nueva_unidades_restantes}.");
                    }

                    // Debug: Log después del cálculo
                    error_log("Debug: producto_id=$producto_id, unidades_a_descontar=$unidades_a_descontar, nuevo_stock_cajas=$nuevo_stock_cajas, nuevas_unidades_restantes=$nueva_unidades_restantes, nuevas_unidades_vendidas_acumuladas=$unidades_vendidas_acumuladas");

                    // Actualizar el stock en la base de datos
                    $stmt_update = $mysqli->prepare("UPDATE productos SET stock_cajas = ?, unidades_restantes = ?, unidades_vendidas_acumuladas = ? WHERE id = ?");
                    $stmt_update->bind_param("iiii", $nuevo_stock_cajas, $nueva_unidades_restantes, $unidades_vendidas_acumuladas, $producto_id);
                    if (!$stmt_update->execute()) {
                        throw new Exception("Error al actualizar stock en productos: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                    error_log("Actualización exitosa (unidades): producto_id=$producto_id, nuevo_stock_cajas=$nuevo_stock_cajas, nuevas_unidades_restantes=$nueva_unidades_restantes, nuevas_unidades_vendidas_acumuladas=$unidades_vendidas_acumuladas");
                } else {
                    // Descontar por cajas
                    $cajas_necesarias = ceil($cantidad / $unidades_por_caja);
                    $nuevo_stock_cajas = $stock_cajas - $cajas_necesarias;
                    if ($nuevo_stock_cajas < 0) {
                        throw new Exception("Stock insuficiente para producto ID {$producto_id}. Stock actual: {$stock_cajas} cajas, Cantidad solicitada: {$cajas_necesarias} cajas.");
                    }
                    $nueva_unidades_restantes = $unidades_restantes;

                    $stmt_update = $mysqli->prepare("UPDATE productos SET stock_cajas = ?, unidades_restantes = ?, unidades_vendidas_acumuladas = ? WHERE id = ?");
                    $stmt_update->bind_param("iiii", $nuevo_stock_cajas, $nueva_unidades_restantes, $unidades_vendidas_acumuladas, $producto_id);
                    if (!$stmt_update->execute()) {
                        throw new Exception("Error al actualizar stock en productos: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                    error_log("Actualización exitosa (cajas): producto_id=$producto_id, nuevo_stock_cajas=$nuevo_stock_cajas, nuevas_unidades_restantes=$nueva_unidades_restantes");
                }
            } else {
                // Gestión por unidades directas (usamos unidades_restantes como stock total)
                $stmt_stock = $mysqli->prepare("SELECT unidades_restantes FROM productos WHERE id = ? FOR UPDATE");
                $stmt_stock->bind_param("i", $producto_id);
                $stmt_stock->execute();
                $result = $stmt_stock->get_result();
                $stock_data = $result->fetch_assoc();
                $stmt_stock->close();

                $stock_actual = intval($stock_data['unidades_restantes']);
                $nuevo_stock = $stock_actual - $cantidad;

                if ($nuevo_stock < 0) {
                    throw new Exception("Stock insuficiente para producto ID {$producto_id}. Stock actual: {$stock_actual} unidades, Cantidad solicitada: {$cantidad} unidades.");
                }

                $stmt_update = $mysqli->prepare("UPDATE productos SET unidades_restantes = ? WHERE id = ?");
                $stmt_update->bind_param("ii", $nuevo_stock, $producto_id);
                if (!$stmt_update->execute()) {
                    throw new Exception("Error al actualizar stock en productos: " . $stmt_update->error);
                }
                $stmt_update->close();
                error_log("Actualización exitosa (unidades directas): producto_id=$producto_id, nuevo_stock=$nuevo_stock");
            }
        }

        // Insertar datos en la tabla ventas
        $stmt_venta = $mysqli->prepare("INSERT INTO ventas (fecha, numero_factura, total, impuesto, total_con_impuesto, cliente_id, forma_pago_id, cuenta, monto_efectivo, monto_otra_forma_pago) 
                                       VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_venta->bind_param("sdddiisdd", $numero_factura, $subtotal, $impuesto, $total_con_impuesto, $cliente_id, $forma_pago, $cuenta, $monto_efectivo, $monto_otra_forma_pago);
        if (!$stmt_venta->execute()) {
            throw new Exception("Error al insertar en ventas: " . $stmt_venta->error);
        }
        $venta_id = $stmt_venta->insert_id;
        $stmt_venta->close();

        // Procesar detalles de la venta (SIN actualizar stock aquí, ya se hizo arriba)
        $stmt_detalle = $mysqli->prepare("INSERT INTO detalles_venta (venta_id, producto_id, laboratorio_id, cantidad, unidad, precio_unitario, precio_venta) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($productos as $producto) {
            $producto_id = intval($producto['id']);
            $laboratorio_id = intval($producto['laboratorio_id']);
            $cantidad = floatval($producto['cantidad']);
            $unidad = !empty($producto['unidad']) ? $producto['unidad'] : '';
            $precio_unitario = $producto['usar_unidad'] ? floatval($producto['precio_por_unidad']) : 0.0;
            $precio_venta = !$producto['usar_unidad'] ? floatval($producto['precio_venta']) : 0.0;

            // Debug: Log detalle values
            error_log("Detalle: venta_id=$venta_id, producto_id=$producto_id, laboratorio_id=$laboratorio_id, cantidad=$cantidad, unidad='$unidad', precio_unitario=$precio_unitario, precio_venta=$precio_venta");

            $stmt_detalle->bind_param("iiidsdd", $venta_id, $producto_id, $laboratorio_id, $cantidad, $unidad, $precio_unitario, $precio_venta);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al insertar detalle de venta: " . $stmt_detalle->error);
            }
        }

        // Cerrar el statement de detalles fuera del bucle
        $stmt_detalle->close();

        // Insertar en la tabla reportes
        $stmt_reporte = $mysqli->prepare("INSERT INTO reportes (venta_id, cliente_id, numero_factura, fecha, total, impuesto, total_con_impuesto, forma_pago_id, subtotal, banco, cuenta, monto_efectivo, monto_otra_forma_pago) 
                                         VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        error_log("Reportes bind_param: type_string=iisddddidsdd, params_count=12, venta_id=$venta_id, cliente_id=$cliente_id, numero_factura=$numero_factura, total=$total, impuesto=$impuesto, total_con_impuesto=$total_con_impuesto, forma_pago=$forma_pago, subtotal=$subtotal, banco=" . ($banco ?? 'NULL') . ", cuenta=" . ($cuenta ?? 'NULL') . ", monto_efectivo=$monto_efectivo, monto_otra_forma_pago=$monto_otra_forma_pago");
        $stmt_reporte->bind_param("iisddddidsdd", $venta_id, $cliente_id, $numero_factura, $total, $impuesto, $total_con_impuesto, $forma_pago, $subtotal, $banco, $cuenta, $monto_efectivo, $monto_otra_forma_pago);
        if (!$stmt_reporte->execute()) {
            throw new Exception("Error al insertar en reportes: " . $stmt_reporte->error);
        }
        $stmt_reporte->close();

        // Confirmar transacción
        $mysqli->commit();

        // Generar el PDF
        try {
            ob_end_clean();
            generarTicketVenta($venta_id, $numero_factura, $subtotal, $impuesto, $total_con_impuesto, $productos, $impuesto_porcentaje, $cliente_id, $forma_pago, $aplicar_impuesto, $monto_efectivo, $monto_otra_forma_pago);
        } catch (Exception $e) {
            throw new Exception("Error al generar PDF: " . $e->getMessage());
        }
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $mysqli->rollback();
        error_log($e->getMessage());
        die($e->getMessage());
    }
}

// Cerrar conexión
$mysqli->close();

// Función para generar el ticket de venta en PDF
function generarTicketVenta($venta_id, $numero_factura, $subtotal, $impuesto, $total_con_impuesto, $productos, $impuesto_porcentaje, $cliente_id, $forma_pago, $aplicar_impuesto, $monto_efectivo, $monto_otra_forma_pago)
{
    // Inicializar TCPDF
    $pdf = new TCPDF('P', 'mm', array(80, 210), true, 'UTF-8', false);
    $pdf->SetCreator('Sistema de Facturación');
    $pdf->SetAuthor('Sistema de Facturación');
    $pdf->SetTitle('Factura de Venta #' . $numero_factura);
    $pdf->SetSubject('Factura de Venta');
    $pdf->SetKeywords('Factura, Venta');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(5, 5, 5);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 8);

    // Obtener datos de la empresa
    $mysqli = new mysqli("localhost", "root", "", "marci");
    $datos_empresa = $mysqli->query("SELECT * FROM datos_empresa LIMIT 1")->fetch_assoc();
    if (!$datos_empresa) {
        error_log("Error: No se encontraron datos de la empresa.");
        die("Error: No se encontraron datos de la empresa.");
    }

    // Obtener datos del cliente
    $stmt_cliente = $mysqli->prepare("SELECT nombre, direccion, identificacion FROM clientes WHERE id = ?");
    $stmt_cliente->bind_param("i", $cliente_id);
    $stmt_cliente->execute();
    $cliente = $stmt_cliente->get_result()->fetch_assoc();
    $stmt_cliente->close();
    if (!$cliente) {
        error_log("Error: Cliente no encontrado.");
        die("Error: Cliente no encontrado.");
    }

    // Obtener datos de la forma de pago
    $stmt_forma = $mysqli->prepare("SELECT tipo, banco, cuenta FROM formas_pago WHERE id = ?");
    $stmt_forma->bind_param("i", $forma_pago);
    $stmt_forma->execute();
    $forma_pago_data = $stmt_forma->get_result()->fetch_assoc();
    $stmt_forma->close();
    if (!$forma_pago_data) {
        error_log("Error: Forma de pago no encontrada.");
        die("Error: Forma de pago no encontrada.");
    }

    // Configurar la hora de Colombia (UTC-5)
    date_default_timezone_set('America/Bogota');
    $fecha_emision = date('d-m-Y H:i A');

    // Contenido del ticket
    $html = '<table cellpadding="2" cellspacing="0" border="0" width="100%">';

    // Encabezado de la empresa
    $html .= '<tr><td colspan="5" align="center"><strong>' . htmlspecialchars($datos_empresa['razon_social']) . '</strong></td></tr>';
    $html .= '<tr><td colspan="5" align="center">NIT: ' . htmlspecialchars($datos_empresa['nit']) . '</td></tr>';
    $html .= '<tr><td colspan="5" align="center">' . htmlspecialchars($datos_empresa['direccion']) . '</td></tr>';
    $html .= '<tr><td colspan="5" align="center">TEL: ' . htmlspecialchars($datos_empresa['telefono']) . '</td></tr>';
    $html .= '<tr><td colspan="5" align="center">Resolución: ' . htmlspecialchars($datos_empresa['resolucion']) . '</td></tr>';
    $html .= '<tr><td colspan="5" align="center"><strong>FACTURA DE VENTA ELECTRÓNICA</strong></td></tr>';
    $html .= '<tr><td colspan="5" align="center">FV' . htmlspecialchars($numero_factura) . '</td></tr>';
    $html .= '<tr><td colspan="5" align="center">FECHA DE EMISIÓN: ' . htmlspecialchars($fecha_emision) . '</td></tr>';

    // Datos del cliente
    $html .= '<tr><td colspan="5"><br></td></tr>';
    $html .= '<tr><td colspan="5"><strong>Cliente:</strong> ' . htmlspecialchars($cliente['nombre']) . '</td></tr>';
    $html .= '<tr><td colspan="5"><strong>Dirección:</strong> ' . htmlspecialchars($cliente['direccion']) . '</td></tr>';
    $html .= '<tr><td colspan="5"><strong>Cédula/NIT:</strong> ' . htmlspecialchars($cliente['identificacion']) . '</td></tr>';

    // Detalle de productos
    $html .= '<tr><td colspan="5"><br></td></tr>';
    $html .= '<tr><td colspan="5"><strong>Detalle de Productos</strong></td></tr>';
    $html .= '<tr><td><strong>Cant.</strong></td><td><strong>Unidad</strong></td><td><strong>Producto</strong></td><td><strong>Precio</strong></td><td><strong>Total</strong></td></tr>';
    foreach ($productos as $producto) {
        $precio = $producto['usar_unidad'] ? $producto['precio_por_unidad'] : $producto['precio_venta'];
        $unidad = !empty($producto['unidad']) ? $producto['unidad'] : 'N/A';
        $cantidad = $producto['cantidad'];
        $subtotal_producto = $cantidad * $precio;
        $laboratorio_id = intval($producto['laboratorio_id']);

        $stmt_lab = $mysqli->prepare("SELECT nombre FROM laboratorios WHERE id = ?");
        $stmt_lab->bind_param("i", $laboratorio_id);
        $stmt_lab->execute();
        $laboratorio = $stmt_lab->get_result()->fetch_assoc();
        $stmt_lab->close();
        $laboratorio_nombre = $laboratorio['nombre'] ?? 'N/A';

        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($cantidad) . '</td>';
        $html .= '<td>' . htmlspecialchars($unidad) . '</td>';
        $html .= '<td>' . htmlspecialchars($producto['nombre']) . '<br><small>Laboratorio: ' . htmlspecialchars($laboratorio_nombre) . '</small></td>';
        $html .= '<td>$' . number_format($precio, 2) . '</td>';
        $html .= '<td>$' . number_format($subtotal_producto, 2) . '</td>';
        $html .= '</tr>';
    }

    $html .= '<tr><td colspan="5"><br></td></tr>';
    $html .= '<tr><td colspan="5"><strong>SUBTOTAL:</strong> $' . number_format($subtotal, 2) . '</td></tr>';

    if ($aplicar_impuesto) {
        $html .= '<tr><td colspan="5"><strong>IVA (' . ($impuesto_porcentaje * 100) . '%):</strong> $' . number_format($impuesto, 2) . '</td></tr>';
    }

    $html .= '<tr><td colspan="5"><strong>TOTAL:</strong> $' . number_format($total_con_impuesto, 2) . '</td></tr>';

    if ($monto_efectivo > 0) {
        $html .= '<tr><td colspan="5"><strong>Monto en Efectivo:</strong> $' . number_format($monto_efectivo, 2) . '</td></tr>';
    }

    if ($monto_otra_forma_pago > 0) {
        $html .= '<tr><td colspan="5"><strong>Monto en Otra Forma de Pago:</strong> $' . number_format($monto_otra_forma_pago, 2) . '</td></tr>';
    }

    $html .= '<tr><td colspan="5"><strong>Forma de Pago:</strong> ' . htmlspecialchars($forma_pago_data['tipo']) . '</td></tr>';
    if ($forma_pago_data['tipo'] == 'transferencia') {
        $html .= '<tr><td colspan="5"><strong>Banco:</strong> ' . htmlspecialchars($forma_pago_data['banco'] ?: 'N/A') . '</td></tr>';
        $html .= '<tr><td colspan="5"><strong>Cuenta:</strong> ' . htmlspecialchars($forma_pago_data['cuenta'] ?: 'N/A') . '</td></tr>';
    }

    $html .= '<tr><td colspan="5"><br></td></tr>';
    $html .= '<tr><td colspan="5" align="center"><strong>GRACIAS POR SU PREFERENCIA</strong></td></tr>';
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    try {
        guardarFacturaEnCarpeta($pdf, $numero_factura, 'facturas');
        guardarFacturaEnCarpeta($pdf, $numero_factura, 'copiaf');
    } catch (Exception $e) {
        error_log("Error al guardar PDF: " . $e->getMessage());
        die("Error al guardar el PDF: " . $e->getMessage());
    }

    $pdf->Output('factura_venta_' . $numero_factura . '.pdf', 'I');

    $mysqli->close();
    exit;
}

function guardarFacturaEnCarpeta($pdf, $numero_factura, $carpeta)
{
    $carpeta_path = __DIR__ . '/marci/' . $carpeta;
    if (!file_exists($carpeta_path)) {
        if (!mkdir($carpeta_path, 0777, true)) {
            error_log("Error: No se pudo crear la carpeta '$carpeta_path'.");
            throw new Exception("No se pudo crear la carpeta '$carpeta_path'.");
        }
    }
    if (!is_writable($carpeta_path)) {
        error_log("Error: La carpeta '$carpeta_path' no tiene permisos de escritura.");
        throw new Exception("La carpeta '$carpeta_path' no tiene permisos de escritura.");
    }
    $ruta_archivo = $carpeta_path . '/factura_venta_' . $numero_factura . '.pdf';
    $pdf->Output($ruta_archivo, 'F');
}
