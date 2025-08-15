<?php
header('Content-Type: application/json');


$conn = new mysqli("localhost", "root", "", "inventario");


if ($conn->connect_error) {
    die(json_encode([
        'success' => false, 
        'message' => 'Error de conexión: ' . $conn->connect_error
    ]));
}

// iniciamos json
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// validacion
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error en formato JSON: ' . json_last_error_msg()
    ]);
    exit;
}

if (!isset($data['solicitudes'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Estructura de datos incorrecta. Se esperaba clave "solicitudes"'
    ]);
    exit;
}

try {
    $conn->begin_transaction();
    $successCount = 0;
    
    foreach ($data['solicitudes'] as $solicitud) {
        // Validar
        $GPcode = $conn->real_escape_string($solicitud['GPcode'] ?? '');
        $cantidad = intval($solicitud['cantidad'] ?? 0);
        $area = $conn->real_escape_string($solicitud['area'] ?? '');
        $fechaInput = $solicitud['fecha'] ?? date('Y-m-d');
        $fechaFormateada = date('Y-m-d', strtotime($fechaInput));
        
        $fecha = $conn->real_escape_string($fechaFormateada);
        if (empty($GPcode)) {
            throw new Exception("GPcode vacío");
        }
        
        if ($cantidad <= 0) {
            throw new Exception("Cantidad debe ser mayor a 0");
        }

        if (empty($area)) {
            throw new Exception("Área no especificada");
        }

        // guardar en la base de datos
        $sql = "INSERT INTO solicitud (GPcode, cantidad, area, fecha) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en preparación: " . $conn->error);
        }
        
        $stmt->bind_param("siss", $GPcode, $cantidad, $area, $fecha);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar: " . $stmt->error);
        }
        
        $successCount++;
        $stmt->close();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Se guardaron $successCount registros correctamente",
        'count' => $successCount
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
    
} finally {
    $conn->close();
}
?>