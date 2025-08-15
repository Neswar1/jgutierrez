<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    die("No autorizado");
}

$conn = new mysqli("localhost", "root", "", "inventario");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// guardamos el id de pedido
$pedido = $_POST['pedido'];

//transaccion
$conn->begin_transaction();

try {
    // obtener datos a eliminar y actualizar
    $sql = "SELECT gpcode, cantidad FROM solicitud WHERE pedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Pedido no encontrado");
    }
        // borrar pedido
    $sql_delete = "DELETE FROM solicitud WHERE pedido = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $pedido);
    $stmt_delete->execute();
    $conn->commit();
    echo "ok";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
    }