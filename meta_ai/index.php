<?php
/* WP-Shield Viewer v1.0 - Generado por IA - 2026-05-05 */
/*
MIT License
Copyright (c) 2026 WP-Shield Viewer
*/
session_start();
require_once 'newconfig.php';
require_once 'functions.php';
rate_limit();

$sidebar_pos = 'sidebar-left'; // Cambiar a sidebar-right para mover sidebar
$search = isset($_GET['s']) ? mysqli_real_escape_string($conn, $_GET['s']) : '';
$slug = isset($_GET['slug']) ? sanitize_slug($_GET['slug']) : '';
$mes = isset($_GET['mes']) ? mysqli_real_escape_string($conn, $_GET['mes']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WP-Shield Viewer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $sidebar_pos; ?>">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-shield">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-shield-alt"></i> WP-Shield</a>
            <form class="form-inline ml-auto" method="get">
                <input class="form-control mr-sm-2" type="search" name="s" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="shield-container">
            <main class="content-main">
                <?php
                $where = "post_status='publish' AND post_type='post'";
                if ($search) $where .= " AND (post_title LIKE '%$search%' OR post_content LIKE '%$search%')";
                if ($slug) $where .= " AND post_name='".mysqli_real_escape_string($conn, $slug)."'";
                if ($mes) $where .= " AND DATE_FORMAT(post_date, '%Y-%m')='$mes'";
                
                $sql = "SELECT ID, post_title, post_content, post_date, post_name 
                        FROM {$table_prefix}posts 
                        WHERE $where 
                        ORDER BY post_date DESC LIMIT 20";
                $res = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($res) == 0) echo '<div class="alert alert-warning">No se encontraron posts.</div>';
                
                while ($post = mysqli_fetch_assoc($res)) {
                    $img = get_featured_image($conn, $table_prefix, $post['ID']);
                    $url = 'index.php?slug='.htmlspecialchars($post['post_name']);
                    $excerpt = htmlspecialchars(substr(strip_tags($post['post_content']), 0, 200)).'...';
                    $fecha = date('d M Y', strtotime($post['post_date']));
                    
                    if ($img) {
                        echo '<div class="card mb-4">
                                <img src="'.$img.'" class="card-img-top" alt="">
                                <div class="card-body">
                                    <h5 class="card-title"><a href="'.$url.'">'.htmlspecialchars($post['post_title']).'</a></h5>
                                    <p class="card-text">'.$excerpt.'</p>
                                    <small class="text-muted"><i class="far fa-calendar"></i> '.$fecha.'</small>
                                </div>
                              </div>';
                    } else {
                        echo '<div class="media mb-4 pb-3 border-bottom">
                                <div class="media-body">
                                    <h5 class="mt-0"><a href="'.$url.'">'.htmlspecialchars($post['post_title']).'</a></h5>
                                    <p>'.$excerpt.'</p>
                                    <small class="text-muted"><i class="far fa-calendar"></i> '.$fecha.'</small>
                                </div>
                              </div>';
                    }
                }
                ?>
            </main>

            <aside class="sidebar-main">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="widget-title"><i class="fas fa-clock"></i> Recientes</h5>
                        <ul class="list-unstyled">
                        <?php foreach (get_recent_posts($conn, $table_prefix) as $rp): ?>
                            <li class="mb-2">
                                <a href="index.php?slug=<?php echo htmlspecialchars($rp['post_name']); ?>">
                                    <?php echo htmlspecialchars($rp['post_title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <?php $sidebar = get_sidebar_content($conn, $table_prefix); ?>
                        <h5 class="widget-title">
                            <i class="fas fa-folder"></i> 
                            <?php echo $sidebar['type'] == 'archives' ? 'Archivos' : 'Categorías'; ?>
                        </h5>
                        <ul class="list-unstyled">
                        <?php foreach ($sidebar['data'] as $item): ?>
                            <li class="mb-1">
                                <?php if ($sidebar['type'] == 'archives'): ?>
                                    <a href="index.php?mes=<?php echo $item['mes']; ?>">
                                        <?php echo $item['mes_nombre']; ?> (<?php echo $item['total']; ?>)
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?cat=<?php echo htmlspecialchars($item['slug']); ?>">
                                        <?php echo htmlspecialchars($item['name']); ?> (<?php echo $item['count']; ?>)
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <footer class="footer-shield">
        WP-Shield Viewer v1.0 | Tema: <?php $info = get_admin_info($conn, $table_prefix); echo $info['theme']; ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>
