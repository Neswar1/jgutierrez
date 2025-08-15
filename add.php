<?php
session_start();

// Verificamos la sesión consistentemente
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
<html lang="en">
<head> 

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Inventario</title>
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
        #submitButton {
            padding: 10px 20px;
            background-color: rgb(74, 192, 200);
            color: white;
            justify-content: center;
            display: flex;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            margin-left: 47.5%;
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
        .dropdown {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        select{
            width: 50%;              
            padding: 8px;
            box-sizing: border-box;  
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: rgba(117, 227, 224, 0.8); 
        }
        input{
            width: 50%;              
            padding: 8px;
            box-sizing: border-box;  
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: rgba(117, 227, 224, 0.8); 
        }
        .area, .info {
            margin-top: 20px;
            display: flex;
            justify-content: center; 
            width: 100%;
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
        #saveToDB:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
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
        <h1>Solicitar indirecto</h1>
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

    <form id="solicitudForm">
    <div class="dropdown">
        <?php
        $conn = new mysqli("localhost", "root", "", "inventario");
        $sql = "SELECT GPCODE,Descripcion FROM inv";
        $res = $conn->query($sql);
        $sql = "SELECT cantidad from inv";
        $cant = $conn->query($sql);
        
        if ($res === false) {
            die("Error en la consulta: " . $conn->error);
        }
        $gpcode = isset($_GET['gpcode']) ? $conn->real_escape_string($_GET['gpcode']) : null;
        ?>
        <select id="categoriaDropdown" name="categoriaDropdown" required>
            <option value="">Seleccione el GPcode a requerir</option>
            <?php while($categoria = $res->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($categoria['GPCODE']); ?>">
                    <?php echo htmlspecialchars($categoria['GPCODE'] . " - " . $categoria['Descripcion']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <?php
    $conn->close();
    ?>
    <div class="info">
        <input type="number" name="canti" id="canti" placeholder="Introduce la cantidad" min="1" required>
    </div>

    <div class="area">
        <select id="areaDrop" name="areaDrop" required>
            <option value="">Seleccione el área</option>
            <option value="Picking/Inbound">Picking/Inbound</option>
            <option value="Embarques/Conteos">Embarques/Conteos</option>  
            <option value="Limpieza">Limpieza</option>
            <option value="Calidad">Calidad</option>
            <option value="Entrenamiento">Entrenamiento</option>
            <option value="Recursos Humanos">Recursos Humanos</option>
        </select><br>
    </div>
     <button type="button" id="submitButton" >Agregar</button>  
    </form>

    <div class="tabla">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>GPcode</th>
                    <th>Cantidad</th>
                    <th>Área</th>
                    <th>Fecha/Hora</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                
            </tbody>
        </table>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <button id="saveToDB" style="padding: 10px 20px; background-color: rgb(74, 192, 200); color: white; border: none; border-radius: 4px; cursor: pointer;">
            Enviar Solicitud
        </button>   
        <div id="responseMessage" style="margin-top: 10px;"></div>
    </div>

    <script>
    let solicitudes = [];
    let tableBody = document.getElementById('tableBody');
    let saveToDB = document.getElementById('saveToDB');
    
    // Función para consultar stock disponible
    async function consultarStock(gpcode) {
        try {
            const response = await fetch(`get_cantidad.php?gpcode=${encodeURIComponent(gpcode)}`);
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            return data.cantidad;
        } catch (error) {
            console.error("Error al consultar stock:", error);
            return null;
        }
    }
    
    // render tabla
    function renderTable() {
        tableBody.innerHTML = '';
        
        solicitudes.forEach(solicitud => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${solicitud.GPcode}</td>
                <td>${solicitud.cantidad}</td>
                <td>${solicitud.area}</td>
                <td>${solicitud.fecha}</td>
                <td><button onclick="eliminarFila(${solicitud.id})">Eliminar</button></td>
            `;
            tableBody.appendChild(row);
        });
        saveToDB.disabled = solicitudes.length === 0;
    }
    
    // Función global para eliminar filas
    function eliminarFila(id) {
        solicitudes = solicitudes.filter(s => s.id !== id);
        renderTable();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const submitButton = document.getElementById('submitButton');
        const form = document.getElementById('solicitudForm');
        
        // Deshabilitar el botón de enviar inicialmente
        saveToDB.disabled = true;
        
        // se activa al clickear boton "Agregar"
        submitButton.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const GPcode = document.getElementById('categoriaDropdown').value;
            const cantidad = parseInt(document.getElementById('canti').value);
            const area = document.getElementById('areaDrop').value;

            // Validación básica de campos
            if (!GPcode || !area) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            // Validación de cantidad
            if (isNaN(cantidad)) {
                alert('La cantidad debe ser un número válido');
                return;
            }
            
            if (cantidad <= 0) {
                alert('La cantidad debe ser mayor a 0');
                return;
            }
            
            // Validar stock disponible
            const stockDisponible = await consultarStock(GPcode);
            if (stockDisponible === null) {
                alert('Error al consultar el stock disponible');
                return;
            }
            
            if (cantidad > stockDisponible) {
                alert(`No hay suficiente stock. Disponible: ${stockDisponible}`);
                return;
            }
            
            const now = new Date();
            solicitudes.push({
                id: Date.now(),
                GPcode: GPcode,
                cantidad: cantidad,
                area: area,
                fecha: now.toLocaleString()
            });
            
            renderTable();
            document.getElementById('canti').value = '';
        });
        
        // almacenar en base de datos
        saveToDB.addEventListener('click', function() {
            if (solicitudes.length === 0) {
                document.getElementById('responseMessage').textContent = "No hay datos para guardar";
                document.getElementById('responseMessage').style.color = "red";
                return;
            }
            
            if (!confirm(`¿Está seguro que desea enviar ${solicitudes.length} solicitudes?`)) {
                return;
            }
            
            document.getElementById('responseMessage').textContent = "Guardando...";
            document.getElementById('responseMessage').style.color = "blue";
            
            fetch('guardar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({solicitudes: solicitudes})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('responseMessage').textContent = data.message;
                    document.getElementById('responseMessage').style.color = "green";
                    
                    // Limpiar tabla
                    solicitudes = [];
                    renderTable();
                } else {
                    document.getElementById('responseMessage').textContent = data.message;
                    document.getElementById('responseMessage').style.color = "red";
                }
            })
            .catch(error => {
                document.getElementById('responseMessage').textContent = "Error: " + error.message;
                document.getElementById('responseMessage').style.color = "red";
            });
        });
    });
    </script>
</body>
</html>