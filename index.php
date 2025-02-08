<?php

include ('conexion.php');

?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
   <link rel="stylesheet" href="css/login.css">
   <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    
    <form action="loginAcceso.php" method="post">
        <img src="img/logotipo.png" alt="">

        <label for="">Usuario</label>
        <input type="text" name="usuario">                                                                                                             
        <label for="">Contraseña</label>
        <input type="password" name="password">
        
        <button class="btn btn-lg btn-primary">Acceder</button>

    </form>
</body>
</html>

