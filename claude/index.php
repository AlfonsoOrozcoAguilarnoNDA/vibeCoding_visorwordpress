<?php
/**
 * WP-Shield Viewer — index.php
 * Modelo: Claude Sonnet 4.6 (Anthropic)
 * Licencia: MIT License — Copyright (c) 2025 WP-Shield Viewer
 */

require_once __DIR__ . '/newconfig.php';
require_once __DIR__ . '/functions.php';

// ─── PARÁMETROS DE ENTRADA (sanitizados) ────────────────────────────────────
$slug     = isset($_GET['slug'])   ? trim(strip_tags($_GET['slug']))   : '';
$search   = isset($_GET['s'])      ? trim(strip_tags($_GET['s']))      : '';
$month    = isset($_GET['month'])  ? trim(strip_tags($_GET['month']))  : '';
$cat      = isset($_GET['cat'])    ? trim(strip_tags($_GET['cat']))    : '';
$page_num = isset($_GET['paged'])  ? max(1, (int)$_GET['paged'])      : 1;
$per_page = 9;
$offset   = ($page_num - 1) * $per_page;

// ─── VISTA INDIVIDUAL ────────────────────────────────────────────────────────
$single_post = null;
if (!empty($slug)) {
    $single_post = wpshield_get_post_by_slug($slug);
}

// ─── FILTROS PARA LISTADO ────────────────────────────────────────────────────
$filters = [];
if (!empty($search)) $filters['search'] = $search;
if (!empty($month))  $filters['month']  = $month;
if (!empty($cat))    $filters['cat']    = $cat;

$posts       = (!$single_post) ? wpshield_get_posts($filters, $per_page, $offset) : [];
$total_posts = (!$single_post) ? wpshield_count_posts($filters) : 0;
$total_pages = (int)ceil($total_posts / $per_page);

// Obtener info del sitio para el navbar
global $conn, $table_prefix;
$sql_siteinfo = "SELECT option_name, option_value FROM {$table_prefix}options
                 WHERE option_name IN ('blogname','blogdescription') LIMIT 2";
$r_site = mysqli_query($conn, $sql_siteinfo);
$site_info = [];
while ($row = mysqli_fetch_assoc($r_site)) {
    $site_info[$row['option_name']] = $row['option_value'];
}
$site_name = esc_html($site_info['blogname'] ?? 'WP-Shield Viewer');
$site_desc = esc_html($site_info['blogdescription'] ?? 'Visor independiente de WordPress');

// ─── TÍTULO DE PÁGINA ────────────────────────────────────────────────────────
$page_title = $site_name;
if ($single_post) $page_title = esc_html($single_post['post_title']) . ' — ' . $site_name;
if (!empty($search)) $page_title = 'Búsqueda: ' . esc_html($search) . ' — ' . $site_name;
if (!empty($month))  $page_title = 'Archivo ' . esc_html($month)  . ' — ' . $site_name;
if (!empty($cat))    $page_title = 'Categoría: ' . esc_html($cat) . ' — ' . $site_name;

// ─── CONTROL SIDEBAR: usa constante definida en functions.php ────────────────
// SIDEBAR_POSITION 'right' → [contenido col-8][sidebar col-4]
// SIDEBAR_POSITION 'left'  → [sidebar col-4][contenido col-8]
$sidebar_right = (SIDEBAR_POSITION === 'right');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $page_title ?></title>
    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
          crossorigin="anonymous">
    <!-- FontAwesome 5.15.4 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css"
          crossorigin="anonymous">
    <!-- WP-Shield CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ═══ NAVBAR FIJA ═══════════════════════════════════════════════════════════ -->
<nav id="wpshield-navbar" class="navbar navbar-expand-md">
    <a class="navbar-brand" href="index.php">
        <i class="fas fa-shield-alt mr-1"></i>
        <span><?= $site_name ?></span>
    </a>

    <button class="navbar-toggler navbar-toggler-right" type="button"
            data-toggle="collapse" data-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false"
            style="border-color:rgba(255,255,255,.3)">
        <i class="fas fa-bars" style="color:#fff"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link <?= (empty($slug) && empty($search) && empty($month) && empty($cat)) ? 'active' : '' ?>"
                   href="index.php"><i class="fas fa-home mr-1"></i>Inicio</a>
            </li>
        </ul>

        <!-- Buscador en navbar -->
        <form class="form-inline my-1 my-md-0" method="GET" action="index.php">
            <div class="input-group">
                <input class="form-control"
                       type="search"
                       name="s"
                       placeholder="Buscar..."
                       value="<?= esc_html($search) ?>"
                       aria-label="Buscar">
                <div class="input-group-append">
                    <button class="btn-search" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</nav>
<!-- ─── FIN NAVBAR ─────────────────────────────────────────────────────────── -->

<!-- ═══ CONTENIDO PRINCIPAL ══════════════════════════════════════════════════ -->
<main id="wpshield-main">
<div class="container-fluid px-3 px-md-4">

    <?php if ($single_post): ?>
    <!-- ── VISTA INDIVIDUAL ────────────────────────────────────────────────── -->
    <div class="row">
        <?php if ($sidebar_right): /* CONTENIDO → SIDEBAR */ ?>
        <div class="col-md-8">
    <?php else: /* SIDEBAR → CONTENIDO */ ?>
        <!-- sidebar izquierda se renderiza después -->
    <div class="col-md-4">
        <?php wpshield_render_recent_posts(); ?>
        <?php wpshield_render_sidebar_dynamic(); ?>
        <?php wpshield_render_admin_info(); ?>
    </div>
    <div class="col-md-8">
    <?php endif; ?>

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb wpshield-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active"><?= esc_html(mb_strimwidth($single_post['post_title'], 0, 60, '…')) ?></li>
                </ol>
            </nav>

            <!-- Artículo -->
            <article class="wpshield-post-single">
                <?php
                $thumb = wpshield_get_thumbnail_url((int)$single_post['ID']);
                if (!empty($thumb)):
                ?>
                <img src="<?= esc_html($thumb) ?>"
                     alt="<?= esc_html($single_post['post_title']) ?>"
                     class="wpshield-thumb">
                <?php endif; ?>

                <h1><?= esc_html($single_post['post_title']) ?></h1>
                <div class="wpshield-meta">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?= esc_html(date('d \d\e F, Y', strtotime($single_post['post_date']))) ?>
                </div>
                <div class="wpshield-content">
                    <?= $single_post['post_content'] /* Contenido tal como está en BD */ ?>
                </div>
            </article>

            <a href="index.php" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fas fa-arrow-left mr-1"></i>Volver al listado
            </a>

        </div><!-- /col-8 -->

        <?php if ($sidebar_right): ?>
        <aside class="col-md-4">
            <?php wpshield_render_recent_posts(); ?>
            <?php wpshield_render_sidebar_dynamic(); ?>
            <?php wpshield_render_admin_info(); ?>
        </aside>
        <?php endif; ?>
    </div><!-- /row -->

    <?php else: ?>
    <!-- ── VISTA LISTADO ───────────────────────────────────────────────────── -->

    <?php /* Contexto del filtro activo */ ?>
    <?php if (!empty($search) || !empty($month) || !empty($cat)): ?>
    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="font-size:.88rem;border-radius:6px;">
        <i class="fas fa-filter mr-2"></i>
        <?php if (!empty($search)): ?>
            Resultados para: <strong class="ml-1">"<?= esc_html($search) ?>"</strong>
        <?php elseif (!empty($month)): ?>
            Archivo de: <strong class="ml-1"><?= esc_html($month) ?></strong>
        <?php elseif (!empty($cat)): ?>
            Categoría: <strong class="ml-1"><?= esc_html($cat) ?></strong>
        <?php endif; ?>
        &nbsp;—&nbsp; <?= $total_posts ?> resultado(s)
        <a href="index.php" class="ml-auto text-muted" title="Limpiar filtro">
            <i class="fas fa-times"></i>
        </a>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- ─── Orden de columnas según SIDEBAR_POSITION ─────────────────── -->
        <?php if ($sidebar_right): /* CONTENIDO 8 | SIDEBAR 4 */ ?>
        <main class="col-md-8">
    <?php else: /* SIDEBAR 4 | CONTENIDO 8 */ ?>
        <aside class="col-md-4">
            <?php wpshield_render_recent_posts(); ?>
            <?php wpshield_render_sidebar_dynamic(); ?>
            <?php wpshield_render_admin_info(); ?>
        </aside>
        <section class="col-md-8">
    <?php endif; ?>

            <?php if (empty($posts)): ?>
            <div class="wpshield-empty">
                <i class="fas fa-inbox d-block"></i>
                <p class="mb-0">No se encontraron publicaciones.</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($posts as $post): ?>
                <?php
                    $thumb  = wpshield_get_thumbnail_url((int)$post['ID']);
                    $has_thumb = !empty($thumb);
                    // Solo mostrar card con imagen si tiene imagen destacada
                    $excerpt = !empty($post['post_excerpt'])
                        ? strip_tags($post['post_excerpt'])
                        : wpshield_excerpt($post['post_content']);
                    $post_url = 'index.php?slug=' . urlencode($post['post_name']);
                    $date_fmt  = date('d M Y', strtotime($post['post_date']));
                ?>
                <div class="col-sm-<?= $has_thumb ? '6' : '12' ?> col-lg-<?= $has_thumb ? '4' : '12' ?> mb-3">
                    <div class="wpshield-post-card card h-100">
                        <?php if ($has_thumb): ?>
                        <a href="<?= esc_html($post_url) ?>">
                            <img src="<?= esc_html($thumb) ?>"
                                 alt="<?= esc_html($post['post_title']) ?>"
                                 class="card-img-top"
                                 loading="lazy">
                        </a>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= esc_html($post_url) ?>"><?= esc_html($post['post_title']) ?></a>
                            </h5>
                            <p class="card-text"><?= esc_html($excerpt) ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-alt mr-1"></i><?= esc_html($date_fmt) ?></span>
                            <a href="<?= esc_html($post_url) ?>">Leer más <i class="fas fa-chevron-right fa-xs"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div><!-- /row cards -->

            <!-- PAGINACIÓN -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Paginación" class="mt-2">
                <ul class="pagination wpshield-pagination flex-wrap">
                    <?php if ($page_num > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?paged=<?= $page_num - 1 ?><?= !empty($search) ? '&s=' . urlencode($search) : '' ?><?= !empty($month) ? '&month=' . urlencode($month) : '' ?><?= !empty($cat) ? '&cat=' . urlencode($cat) : '' ?>">
                            <i class="fas fa-chevron-left fa-xs"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
                    <li class="page-item <?= $i === $page_num ? 'active' : '' ?>">
                        <a class="page-link" href="?paged=<?= $i ?><?= !empty($search) ? '&s=' . urlencode($search) : '' ?><?= !empty($month) ? '&month=' . urlencode($month) : '' ?><?= !empty($cat) ? '&cat=' . urlencode($cat) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($page_num < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?paged=<?= $page_num + 1 ?><?= !empty($search) ? '&s=' . urlencode($search) : '' ?><?= !empty($month) ? '&month=' . urlencode($month) : '' ?><?= !empty($cat) ? '&cat=' . urlencode($cat) : '' ?>">
                            <i class="fas fa-chevron-right fa-xs"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php endif; /* fin posts no vacíos */ ?>

        <?php echo $sidebar_right ? '</main>' : '</section>'; ?>

        <?php if ($sidebar_right): /* SIDEBAR a la derecha */ ?>
        <aside class="col-md-4">
            <?php wpshield_render_recent_posts(); ?>
            <?php wpshield_render_sidebar_dynamic(); ?>
            <?php wpshield_render_admin_info(); ?>
        </aside>
        <?php endif; ?>
    </div><!-- /row principal -->
    <?php endif; /* fin listado */ ?>

</div><!-- /container -->
</main>
<!-- ─── FIN CONTENIDO ──────────────────────────────────────────────────────── -->

<!-- ═══ FOOTER FIJO ══════════════════════════════════════════════════════════ -->
<footer id="wpshield-footer">
    <span>
        <i class="fas fa-shield-alt mr-1"></i>
        <strong>WP-Shield Viewer</strong> &nbsp;·&nbsp; <?= $site_desc ?>
    </span>
    <span>
        Generado por <?= WPSHIELD_MODEL ?> &nbsp;·&nbsp;
        <a href="index.php">Inicio</a>
        <?php if (!empty($slug)): ?>
        &nbsp;·&nbsp; <a href="index.php?slug=<?= urlencode($slug) ?>">Post actual</a>
        <?php endif; ?>
    </span>
</footer>
<!-- ─── FIN FOOTER ─────────────────────────────────────────────────────────── -->

<!-- Bootstrap JS (solo para toggler del navbar) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
        crossorigin="anonymous"></script>
</body>
</html>
