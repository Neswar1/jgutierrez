<?php
session_start();
header('Content-Type: application/json');

// validar sesion
if (!isset($_SESSION['usuario'])) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$conn = new mysqli("localhost", "root", "", "inventario");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id']);

$stmt = $conn->prepare("DELETE FROM historial WHERE id = ?");
$stmt->bind_param("i", $id);
$success = $stmt->execute();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Registro eliminado' : 'Error al eliminar'
]);

$conn->close();
?>