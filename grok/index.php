<?php
/**
 * WP-Shield Viewer - index.php
 * Versión: 1.0
 * Modelo: Grok 4 (xAI)
 * Licencia: MIT
 */

require_once 'newconfig.php';
require_once 'functions.php';

$search = isset($_GET['s']) ? mysqli_real_escape_string(CONN, $_GET['s']) : '';
$slug   = isset($_GET['slug']) ? mysqli_real_escape_string(CONN, $_GET['slug']) : '';
$year   = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month  = isset($_GET['month']) ? (int)$_GET['month'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Sitio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="./">Mi Sitio</a>
        <form class="form-inline ml-auto" method="GET">
            <input class="form-control mr-2" type="text" name="s" placeholder="Buscar..." value="<?=htmlspecialchars($search)?>">
            <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <?php if (SIDEBAR_POSITION === 'left'): ?>
            <div class="col-lg-3"><?= get_sidebar_content(); ?></div>
        <?php endif; ?>

        <div class="<?= SIDEBAR_POSITION === 'left' ? 'col-lg-9' : 'col-lg-8' ?>">

            <?php
            if ($slug) {
                // Vista de artículo individual
                $sql = "SELECT * FROM " . TABLE_PREFIX . "posts WHERE post_name = '$slug' AND post_status = 'publish' LIMIT 1";
                $result = mysqli_query(CONN, $sql);
                if ($post = mysqli_fetch_assoc($result)) {
                    echo '<h1>' . htmlspecialchars($post['post_title']) . '</h1>';
                    echo '<p><small>Publicado el ' . $post['post_date'] . '</small></p>';
                    echo '<div class="content">' . wpautop($post['post_content']) . '</div>'; // wpautop es seguro aquí
                }
            } else {
                // Lista de posts
                $where = "WHERE post_status = 'publish' AND post_type = 'post'";
                if ($search) $where .= " AND (post_title LIKE '%$search%' OR post_content LIKE '%$search%')";
                if ($year && $month) $where .= " AND YEAR(post_date) = $year AND MONTH(post_date) = $month";

                $sql = "SELECT ID, post_title, post_name, post_date, post_excerpt 
                        FROM " . TABLE_PREFIX . "posts 
                        $where 
                        ORDER BY post_date DESC LIMIT 20";

                $result = mysqli_query(CONN, $sql);

                while ($post = mysqli_fetch_assoc($result)) {
                    $image = get_featured_image($post['ID']);
                    if ($image) {
                        echo '<div class="card">';
                        echo '<img src="' . $image . '" class="card-img-top" alt="Imagen">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title"><a href="?slug=' . urlencode($post['post_name']) . '">' . 
                             htmlspecialchars($post['post_title']) . '</a></h5>';
                        echo '<p class="card-text">' . substr(strip_tags($post['post_excerpt'] ?: $post['post_content']), 0, 180) . '...</p>';
                        echo '</div></div>';
                    }
                }
            }
            ?>
        </div>

        <?php if (SIDEBAR_POSITION === 'right'): ?>
            <div class="col-lg-4"><?= get_sidebar_content(); ?></div>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 fixed-bottom">
    <div class="container">
        <p>WP-Shield Viewer v1.0 &copy; <?= date('Y') ?> - Protegido contra bots</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
