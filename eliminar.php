<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    die("No autorizado");
}

$conn = new mysqli("localhost", "root", "", "inventario");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$pedido = $_POST['pedido'];


$conn->begin_transaction();

try {
    
    $sql = "SELECT gpcode, cantidad, area, fecha FROM solicitud WHERE pedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Pedido no encontrado");
    }
    
    // Verificar si hay suficiente inventario
    $productos = [];
    while ($pedido_data = $result->fetch_assoc()) {
        $gpcode = $pedido_data['gpcode'];
        $cantidad = $pedido_data['cantidad'];
        $productos[] = $pedido_data;
        
        $sql_check = "SELECT cantidad FROM inv WHERE GPCODE = ? FOR UPDATE";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $gpcode);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            throw new Exception("El producto $gpcode no existe en inventario");
        }
        
        $inventario = $result_check->fetch_assoc();
        if ($cantidad > $inventario['cantidad']) {
            $conn->rollback();
            echo "Cantidad insuficiente para $gpcode (Disponible: {$inventario['cantidad']}, Solicitado: $cantidad)";
            exit();
        }
    }
    
    // se guarda antes de eliminar
    foreach ($productos as $producto) {
        // Primero obtenemos la descripción del producto
        $sql_desc = "SELECT Descripcion FROM inv WHERE GPCODE = ?";
        $stmt_desc = $conn->prepare($sql_desc);
        $stmt_desc->bind_param("s", $producto['gpcode']);
        $stmt_desc->execute();
        $desc_result = $stmt_desc->get_result();
        $desc_data = $desc_result->fetch_assoc();
        $descripcion = $desc_data['Descripcion'] ?? '';
        
        $sql_historial = "INSERT INTO historial (GPCODE, Descripcion, Cantidad, Area, Fecha) VALUES (?, ?, ?, ?, ?)";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("ssiss", $producto['gpcode'], $descripcion, $producto['cantidad'], $producto['area'], $producto['fecha']);
        $stmt_historial->execute();
    }
    
    // Eliminar pedido
    $sql_delete = "DELETE FROM solicitud WHERE pedido = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $pedido);
    $stmt_delete->execute();
    
    // Actualizar inventario
    foreach ($productos as $producto) {
        $sql_update = "UPDATE inv SET Cantidad = Cantidad - ? WHERE GPCODE = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("is", $producto['cantidad'], $producto['gpcode']);
        $stmt_update->execute();
    }

    $conn->commit();
    echo "ok";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>