<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "inventario");
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Prevención básica contra SQL injection
    $usuario = $conn->real_escape_string($_POST["username"]);
    $password = $conn->real_escape_string($_POST["password"]);

    
    $sql = "SELECT id, username, nivel FROM usuarios WHERE username='$usuario' AND password='$password'";
    $res = $conn->query($sql);

    if ($res->num_rows > 0) {
        session_start();
        $user = $res->fetch_assoc();
        
        
        $_SESSION['usuario'] = $user['username'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['nivel'] = $user['nivel']; 
        
        echo "<pre>Datos de usuario:";
        print_r($user);
        echo "</pre>";
        
        header("Location: manager.php");
        exit;
    } else {
        $conn->close();
        $mensaje = "Usuario o contraseña incorrectos";
        header("Location: index.php?msg=" . urlencode($mensaje));
        exit;
    }

} else {
    header("Location: index.php?msg=" . urlencode("Por favor ingresa desde el formulario."));
    exit;
}
?>
