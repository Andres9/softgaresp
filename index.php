<?php

require_once __DIR__ . '/auth.php';

if (usuario_autenticado()) {
    header('Location: ' . pagina_inicio_usuario());
    exit;
}

$loginError = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>


<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
   <link rel="stylesheet" href="css/login.css">
   <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>
    
    <form action="loginAcceso.php" method="post">
        <img src="img/logosnegro.jpg" alt="">

        <?php if ($loginError !== '') { ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <label for="usuario">Usuario</label>
        <input id="usuario" type="text" name="usuario" maxlength="150" autocomplete="username" required>
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" maxlength="4096" autocomplete="current-password" required>
        
        <button type="submit" class="btn btn-lg btn-primary">Acceder</button>

    </form>
</body>
</html>

