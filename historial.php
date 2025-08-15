<?php
session_start();

// verificar sesion
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "inventario");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$isAdmin = $_SESSION['nivel'] == 1;  
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de pedidos</title>
    <style>
        :root {
  --primary-bg: rgba(44, 39, 39, 0.45);
  --text-dark: rgb(4, 5, 5);
  --text-light: #f8f9fa;
  --accent-color: rgba(117, 227, 224, 0.8);
  --hover-effect: rgba(212, 220, 234, 0.6);
  --table-border: #dee2e6;
  --nav-bg: rgba(0, 0, 0, 0.02);
}

body {
  font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background-color: var(--primary-bg);
  margin: 0;
  padding: 20px;
  min-height: 100vh;
  line-height: 1.6;
  color: var(--text-dark);
}

h1 {
  color: var(--text-dark);
  text-align: center;
  padding: 15px 10px;
  margin-bottom: 1.5rem;
  position: relative;
}

h1::after {
  content: '';
  display: block;
  width: 80px;
  height: 3px;
  background: var(--accent-color);
  margin: 10px auto;
}

nav {
  display: flex;
  justify-content: center;
  width: 100%;
  margin-bottom: 2rem;
}

nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  gap: 2rem;
  background: var(--nav-bg);
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

nav a {
  text-decoration: none;
  color: var(--text-dark);
  font-size: 1.1rem;
  font-weight: 600;
  transition: all 0.3s ease;
  padding: 0.5rem 1rem;
  position: relative;
}

nav a:hover {
  color: var(--hover-effect);
  transform: translateY(-2px);
}

nav a::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: var(--accent-color);
  transition: width 0.3s ease;
}

nav a:hover::after {
  width: 100%;
}

.tabla {
  display: flex;
  justify-content: center;
  margin: 2rem auto;
  width: 90%;
  max-width: 1200px;
  overflow-x: auto;
}

table {
  width: 100%;
  margin-top: 1rem;
  border-collapse: collapse;
  background-color: white;
  box-shadow: 0 0 20px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  border: 1px solid var(--table-border);
  padding: 12px 15px;
  text-align: left;
}

th {
  background-color: var(--accent-color);
  color: #343a40;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.9rem;
  letter-spacing: 0.5px;
}

tr:nth-child(even) {
  background-color: rgba(0,0,0,0.02);
}

tr:hover {
  background-color: rgba(0,0,0,0.05);
}
button{
            padding: 10px 20px;
            background-color: rgb(74, 192, 200);
            color: white;
            justify-content: center;
            display: flex;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

@media (max-width: 768px) {
  nav ul {
    flex-direction: column;
    gap: 0.5rem;
    align-items: center;
  }
  
  .tabla {
    width: 100%;
    padding: 0 10px;
  }
  
  table {
    font-size: 0.9rem;
  }
  
  th, td {
    padding: 8px 10px;
  }
}
    </style>
</head>
<body>

    <div style="text-align: center;">
        <h1>Historial</h1>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="manager.php">Inventario</a></li>
                <li><a href="add.php">Solicitar</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="admin.php">Solicitudes</a></li>
                    <li><a href="historial.php">Historial</a></li>
                <?php endif; ?>
            </ul>
        </nav> 
    </div>
    
    <div class="tabla">
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th>GPcode</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Área</th>
                    <th>Fecha</th>
                    <th>Limpiar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = new mysqli("localhost", "root", "", "inventario");
                if ($conn->connect_error) {
                    die("Error de conexión: " . $conn->connect_error);
                }
                
                $sql = "SELECT id, GPCODE, Descripcion, Cantidad, Area, Fecha FROM historial";
                $res = $conn->query($sql);
                
                if ($res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($row['GPCODE'])."</td>";
                        echo "<td>".htmlspecialchars($row['Descripcion'])."</td>";
                        echo "<td>".htmlspecialchars($row['Cantidad'])."</td>";
                        echo "<td>".htmlspecialchars($row['Area'])."</td>";
                        echo "<td>".htmlspecialchars($row['Fecha'])."</td>";
                        echo '<td><button class="btn-limpiar" data-id="'.$row['id'].'">Limpiar</button></td>';
                    
                        
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='".($isAdmin ? 6 : 5)."'>No hay registros en el historial</td></tr>";
                }
                
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>  
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-limpiar')) {
            const button = e.target;
            const idRegistro = button.dataset.id;
            const fila = button.closest('tr');
            
            
            button.disabled = true;
            button.textContent = "Procesando...";
            
            fetch('limpiar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idRegistro })
            })
            .then(response => {
                if (!response.ok) throw new Error("Error en la respuesta");
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    fila.style.transition = "opacity 0.5s";
                    fila.style.opacity = "0";
                    setTimeout(() => fila.remove(), 500);
                } else {
                    throw new Error(data.message || "Error desconocido");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert(error.message);
                button.disabled = false;
                button.textContent = "Limpiar";
            });
        }
    });
});
</script>
    
</body>
</html>