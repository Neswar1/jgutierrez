<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      background-image: url('fondo.jpg');
      background-size: cover;
      background-position: center;    
      background-repeat: no-repeat;   
      height: 100vh;                  
      margin: 0;
      padding: 0;
      
    }
    
    form input {
      width: 100%;
      padding: 10px;
      margin: 5px 0;
      box-sizing: border-box;
    }
    .login {
      width: 300px;
      margin: 0 auto;
      padding: 18px;
      background-color: #f0f0f0;
      border-radius: 10px;
    }
    form button {
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>
    <div style="text-align: center;">
  <div style="padding: 10px;border-radius: 5px;display: inline-block;color: white;
      font-family: Arial, sans-serif;
      background-color: rgba(0, 0, 0, 0.8);"><h1>Inventario Green Power</h1></div>
      </div>

  <form action="inv.php" method="post" class="login">
    <label for="username">Usuario:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required><br>

    <button type="submit">Ingresar</button>
  </form>

  
  <?php
  if (isset($_GET["msg"])) {
    echo "<p style='text-align:center; margin-top:8 0px; font-weight: bold; color: rgb(12, 11, 11); font-size:18px; '> " . htmlspecialchars($_GET["msg"]) . "</p>";
  }
  ?>
</body>
</html>

