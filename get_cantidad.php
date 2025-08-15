<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "inventario");

$gpcode = isset($_GET['gpcode']) ? $conn->real_escape_string($_GET['gpcode']) : null;
if (!$gpcode) die(json_encode(['error' => 'GPCODE no especificado']));


$sql = "SELECT cantidad FROM inv WHERE GPCODE = '$gpcode'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode(['cantidad' => $result->fetch_assoc()['cantidad']]);
} else {
    echo json_encode(['error' => 'Artículo no encontrado']);
}

$conn->close();
?>