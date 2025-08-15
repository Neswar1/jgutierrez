<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "inventario");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$isAdmin = $_SESSION['nivel'] == 1;  

// Manejar la actualización si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gpcode'])) {
    $gpcode = $_POST['gpcode'];
    $cantidad = intval($_POST['cantidad']);
    
    $stmt = $conn->prepare("UPDATE inv SET cantidad = cantidad + ? WHERE gpcode = ?");
    $stmt->bind_param("is", $cantidad, $gpcode);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar']);
    }
    exit;
}

// escoje lo que vas a solicitar a la db
$sql = "SELECT gpcode, descripcion, cantidad as quantity, min FROM inv";
$res = $conn->query($sql);
$inventoryData = array();
while($row = $res->fetch_assoc()) {
    $inventoryData[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Inventario</title>
</head>
<body>

    <div style="text-align: center;">
        <h1>Administrador de Inventario</h1>
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

    <div class="search-container">
    <input name="search" type="text" id="search" placeholder="Buscar en inventario...">
    </div>
    <div class="table-container">
    <table id="inventoryTable">
        <thead>
            <tr>
                <th>GPcode</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <?php if ($isAdmin): ?>
                    <th>Agregar inventario</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
    </div>  
<?php
    $conn = new mysqli("localhost", "root", "", "inventario");
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $sql = "SELECT  
            gpcode as gpcode, 
            descripcion as descripcion, 
            cantidad as quantity,
            min as min
        FROM inv";
    $res = $conn->query($sql); //ejecuta la consulta
    $inventoryData = array();
        while($row = $res->fetch_assoc()) {
            $inventoryData[] = $row;
        }
    $conn->close();
?>
    <script>
    const inventoryData = <?php echo json_encode($inventoryData); ?>;
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    
    // Función para mostrar datos
    function displayData(data) {
        const tbody = document.querySelector('#inventoryTable tbody');
        if (!tbody) {
            console.error("No se encontró el elemento tbody");
            return;
        }
        
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No hay datos disponibles</td></tr>';
            return;
        }
        //colorear rojo los minimos
        data.forEach(item => {
            const row = document.createElement('tr');
            const quantity = Number(item.quantity) || 0;
            const min = Number(item.min) || 0;
            const isBelowMin = quantity < min;
            const quantityClass = isBelowMin ? 'class="below-min"' : '';
            
            row.innerHTML = `
                <td>${item.gpcode || ''}</td>
                <td>${item.descripcion || ''}</td>
                <td ${quantityClass}>${quantity}</td>
                ${isAdmin ? `
                <td>
                    <input type="number" class="canti-input" data-gpcode="${item.gpcode}" placeholder="Cantidad" min="1" required>
                    <button onclick="updateQuantity('${item.gpcode}', this)">Confirmar</button>
                </td>
                ` : ''}
            `;
            tbody.appendChild(row);
        });
    }

    //funcion para actualizar la cantidad de un item
    function updateQuantity(gpcode, button) {
        const input = button.previousElementSibling;
        const cantidad = parseInt(input.value);
        
        if (isNaN(cantidad) || cantidad < 1) {
            alert('Por favor ingrese una cantidad válida');
            return;
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `gpcode=${encodeURIComponent(gpcode)}&cantidad=${cantidad}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = inventoryData.find(i => i.gpcode === gpcode);
                if (item) {
                    item.quantity = (parseInt(item.quantity) || 0) + cantidad;
                    displayData(inventoryData);
                }
                input.value = '';
            } else {
                alert('Error al actualizar: ' + (data.error || ''));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor');
        });
    }

    // busca en la tabla segun el input ingresado
    function searchInventory() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const filteredData = inventoryData.filter(item => 
            (item.gpcode && item.gpcode.toString().toLowerCase().includes(searchTerm)) ||
            (item.descripcion && item.descripcion.toLowerCase().includes(searchTerm)) ||
            (item.quantity && item.quantity.toString().toLowerCase().includes(searchTerm))
        );
        displayData(filteredData);
    }

    
    document.addEventListener('DOMContentLoaded', () => {
        console.log("Datos cargados:", inventoryData); 
        displayData(inventoryData);
        document.getElementById('search').addEventListener('input', searchInventory);
    });
</script>
</body>
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
       .canti-input{
            
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: rgb(74, 192, 200);
            margin-bottom: 5px;
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
        .below-min {
        background-color:rgb(250, 0, 0); /* Fondo rojo claro */
        color:rgb(0, 0, 0); /* Texto rojo oscuro */
        font-weight: bold;
    }
        .table-container {
            display: flex;
            justify-content: center;
            margin: 1rem auto;
            width: 90%;
            max-width: 1200px;
            overflow-x: auto;
        }
        
        
        table {
        width: 100%;
        margin-top: 0.3rem;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
        }
        
        th, td {
        border: 1px solid var(--table-border);
        padding: 5px 13px;
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

        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        #search {
            width: 50%;              
            padding: 8px;
            box-sizing: border-box;  
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: rgba(117, 227, 224, 0.8); 
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
</html>