<?php
/**
 * SISTEMA: Visor wordpress
 * https://vibecodingmexico.com/los-siete-magnificos-llm-son-uno-por-ahora/
 * FECHA: 5 de mayo de 2026
 * LICENCIA: MIT
 * COAUTORÍA: Meta AI (v.2026-03) & Alfonso Orozco Aguilar
 * NO SE USE EN PRODUCCION
 * DESCRIPCIÓN: revisar el enlace, pero es un visor de wordpress como ejercicio de benchmarking
 */
session_start();
require_once 'newconfig.php';

if (isset($_POST['login'])) {
    if ($_POST['user'] === SHIELD_USER && password_verify($_POST['pass'], SHIELD_PASS_HASH)) {
        $_SESSION['shield_auth'] = true;
        header('Location: editor.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: editor.php');
    exit;
}

if (!isset($_SESSION['shield_auth'])) {
    ?>
    <!DOCTYPE html><html><head><title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    </head><body class="bg-light">
    <div class="container mt-5"><div class="row justify-content-center"><div class="col-md-4">
    <div class="card"><div class="card-body">
    <h5>WP-Shield Editor</h5>
    <?php if(isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
    <form method="post">
        <input type="text" name="user" class="form-control mb-2" placeholder="Usuario" required>
        <input type="password" name="pass" class="form-control mb-2" placeholder="Clave" required>
        <button name="login" class="btn btn-primary btn-block">Entrar</button>
    </form>
    </div></div></div></div></div></body></html>
    <?php exit;
}

if (isset($_POST['save'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    if (!$slug) $slug = sanitize_slug($title);
    
    $sql = "INSERT INTO {$table_prefix}posts 
            (post_title, post_content, post_name, post_status, post_type, post_date, post_date_gmt) 
            VALUES ('$title', '$content', '$slug', 'publish', 'post', NOW(), NOW())";
    if (mysqli_query($conn, $sql)) {
        $msg = 'Post guardado. ID: '.mysqli_insert_id($conn);
    } else {
        $msg = 'Error: '.mysqli_error($conn);
    }
}
?>
<!DOCTYPE html><html><head><title>Editor</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head><body>
<div class="container mt-4">
    <h3>Editor WP-Shield <a href="?logout=1" class="btn btn-sm btn-danger float-right">Salir</a></h3>
    <?php if(isset($msg)) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
    <form method="post">
        <input type="text" name="title" class="form-control mb-2" placeholder="Título" required>
        <input type="text" name="slug" class="form-control mb-2" placeholder="slug-url (opcional)">
        <textarea name="content" class="form-control mb-2" rows="10" placeholder="Contenido HTML" required></textarea>
        <button name="save" class="btn btn-success">Publicar Post</button>
        <a href="index.php" class="btn btn-secondary">Ver Sitio</a>
    </form>
</div></body></html>
