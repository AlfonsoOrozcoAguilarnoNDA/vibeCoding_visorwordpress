<?php
/**
 * WP-Shield Viewer — functions.php
 * Modelo: Claude Sonnet 4.6 (Anthropic)
 * Licencia: MIT License
 *
 * MIT License - Copyright (c) 2025 WP-Shield Viewer
 * Ver newconfig.php para texto completo de licencia.
 */

// ─── GUARD: requiere conexión activa ────────────────────────────────────────
if (!isset($conn) || !isset($table_prefix)) {
    die('[WP-Shield] functions.php debe incluirse después de newconfig.php');
}

// ─── CONTROL GLOBAL DE SIDEBAR ───────────────────────────────────────────────
// Cambia este valor para invertir la posición del sidebar globalmente.
// 'right' → contenido col-8 | sidebar col-4
// 'left'  → sidebar col-4  | contenido col-8
define('SIDEBAR_POSITION', 'right');

// ─── HELPER: escape seguro para HTML ────────────────────────────────────────
function esc_html(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── 1. CONTEO DE TIPOS DE POST ─────────────────────────────────────────────
/**
 * Determina si predominan 'post' o 'page' en la instalación.
 * Retorna: 'posts' | 'pages'
 */
function wpshield_dominant_post_type(): string {
    global $conn, $table_prefix;
    $sql = "SELECT post_type, COUNT(*) as total
            FROM {$table_prefix}posts
            WHERE post_status = 'publish'
              AND post_type IN ('post','page')
            GROUP BY post_type";
    $result = mysqli_query($conn, $sql);
    $counts = ['post' => 0, 'page' => 0];
    while ($row = mysqli_fetch_assoc($result)) {
        $counts[$row['post_type']] = (int)$row['total'];
    }
    return ($counts['post'] >= $counts['page']) ? 'posts' : 'pages';
}

// ─── 2. SIDEBAR DINÁMICO ─────────────────────────────────────────────────────
/**
 * Si predominan posts → Archivo por Mes
 * Si predominan pages → Categorías con contador
 */
function wpshield_render_sidebar_dynamic(): void {
    $dominant = wpshield_dominant_post_type();
    if ($dominant === 'posts') {
        wpshield_render_archive_by_month();
    } else {
        wpshield_render_categories();
    }
}

/**
 * Genera lista de archivo por mes
 */
function wpshield_render_archive_by_month(): void {
    global $conn, $table_prefix;
    $sql = "SELECT DATE_FORMAT(post_date, '%Y-%m') as ym,
                   DATE_FORMAT(post_date, '%M %Y') as label,
                   COUNT(*) as total
            FROM {$table_prefix}posts
            WHERE post_status = 'publish' AND post_type = 'post'
            GROUP BY ym
            ORDER BY ym DESC
            LIMIT 24";
    $result = mysqli_query($conn, $sql);

    echo '<div class="card wpshield-card mb-3">';
    echo '<div class="card-header wpshield-card-header"><i class="fas fa-calendar-alt mr-2"></i>Archivo Mensual</div>';
    echo '<ul class="list-group list-group-flush">';
    while ($row = mysqli_fetch_assoc($result)) {
        $url = 'index.php?month=' . esc_html($row['ym']);
        echo '<li class="list-group-item d-flex justify-content-between align-items-center wpshield-list-item">';
        echo '<a href="' . $url . '" class="wpshield-link">' . esc_html($row['label']) . '</a>';
        echo '<span class="badge badge-primary badge-pill">' . (int)$row['total'] . '</span>';
        echo '</li>';
    }
    echo '</ul></div>';
}

/**
 * Genera nube/lista de categorías con contador
 */
function wpshield_render_categories(): void {
    global $conn, $table_prefix;
    $sql = "SELECT t.name, t.slug, COUNT(tr.object_id) as total
            FROM {$table_prefix}terms t
            JOIN {$table_prefix}term_taxonomy tt ON t.term_id = tt.term_id
            JOIN {$table_prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tt.taxonomy = 'category'
            GROUP BY t.term_id
            ORDER BY total DESC
            LIMIT 20";
    $result = mysqli_query($conn, $sql);

    echo '<div class="card wpshield-card mb-3">';
    echo '<div class="card-header wpshield-card-header"><i class="fas fa-tags mr-2"></i>Categorías</div>';
    echo '<div class="card-body">';
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    if (empty($rows)) {
        echo '<p class="text-muted small mb-0">Sin categorías.</p>';
    } else {
        // Nube simple ponderada por tamaño de fuente
        $max = max(array_column($rows, 'total'));
        foreach ($rows as $cat) {
            $size = 85 + round(($cat['total'] / max($max, 1)) * 45); // 85% a 130%
            $url  = 'index.php?cat=' . esc_html($cat['slug']);
            echo '<a href="' . $url . '" class="wpshield-tag mr-1 mb-1 d-inline-block" style="font-size:' . $size . '%">';
            echo esc_html($cat['name']) . ' <sup>' . (int)$cat['total'] . '</sup></a>';
        }
    }
    echo '</div></div>';
}

// ─── 3. POSTS RECIENTES ──────────────────────────────────────────────────────
/**
 * Retorna array de los 5 posts más recientes (id, title, slug, date)
 */
function wpshield_get_recent_posts(int $limit = 5): array {
    global $conn, $table_prefix;
    $sql = "SELECT ID, post_title, post_name, post_date
            FROM {$table_prefix}posts
            WHERE post_status = 'publish' AND post_type = 'post'
            ORDER BY post_date DESC
            LIMIT " . (int)$limit;
    $result = mysqli_query($conn, $sql);
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

/**
 * Renderiza el widget de posts recientes
 */
function wpshield_render_recent_posts(): void {
    $posts = wpshield_get_recent_posts(5);
    echo '<div class="card wpshield-card mb-3">';
    echo '<div class="card-header wpshield-card-header"><i class="fas fa-clock mr-2"></i>Recientes</div>';
    echo '<ul class="list-group list-group-flush">';
    if (empty($posts)) {
        echo '<li class="list-group-item text-muted small">Sin posts publicados.</li>';
    } else {
        foreach ($posts as $p) {
            $url  = 'index.php?slug=' . esc_html($p['post_name']);
            $date = date('d M Y', strtotime($p['post_date']));
            echo '<li class="list-group-item wpshield-list-item">';
            echo '<a href="' . $url . '" class="wpshield-link d-block">' . esc_html($p['post_title']) . '</a>';
            echo '<small class="text-muted">' . esc_html($date) . '</small>';
            echo '</li>';
        }
    }
    echo '</ul></div>';
}

// ─── 4. INFO DE ADMINISTRACIÓN (tema y plugins) ──────────────────────────────
/**
 * Obtiene tema activo y plugins activos desde wp_options
 */
function wpshield_get_admin_info(): array {
    global $conn, $table_prefix;

    // Tema activo (stylesheet)
    $sql_theme = "SELECT option_value FROM {$table_prefix}options
                  WHERE option_name = 'stylesheet' LIMIT 1";
    $r = mysqli_query($conn, $sql_theme);
    $theme = 'Desconocido';
    if ($r && $row = mysqli_fetch_assoc($r)) {
        $theme = $row['option_value'];
    }

    // Plugins activos (serializado)
    $sql_plugins = "SELECT option_value FROM {$table_prefix}options
                    WHERE option_name = 'active_plugins' LIMIT 1";
    $r2 = mysqli_query($conn, $sql_plugins);
    $plugins = [];
    if ($r2 && $row2 = mysqli_fetch_assoc($r2)) {
        $raw = $row2['option_value'];
        // Deserializar manualmente el array serializado de PHP
        $unserialized = @unserialize($raw);
        if (is_array($unserialized)) {
            foreach ($unserialized as $plugin_path) {
                // Formato: "folder/plugin-file.php"
                $parts = explode('/', $plugin_path);
                $plugins[] = count($parts) > 1
                    ? str_replace(['-', '_', '.php'], [' ', ' ', ''], $parts[0])
                    : str_replace(['-', '_', '.php'], [' ', ' ', ''], $plugin_path);
            }
        }
    }

    return ['theme' => $theme, 'plugins' => $plugins];
}

/**
 * Renderiza el widget de info de admin
 */
function wpshield_render_admin_info(): void {
    $info = wpshield_get_admin_info();
    echo '<div class="card wpshield-card mb-3">';
    echo '<div class="card-header wpshield-card-header"><i class="fas fa-info-circle mr-2"></i>Info del Sitio</div>';
    echo '<div class="card-body p-0">';
    echo '<ul class="list-group list-group-flush">';
    echo '<li class="list-group-item wpshield-list-item">';
    echo '<strong><i class="fas fa-palette mr-1"></i> Tema:</strong> ';
    echo '<span class="badge badge-secondary">' . esc_html(ucwords($info['theme'])) . '</span>';
    echo '</li>';

    if (!empty($info['plugins'])) {
        echo '<li class="list-group-item wpshield-list-item">';
        echo '<strong><i class="fas fa-plug mr-1"></i> Plugins activos:</strong>';
        echo '<ul class="mt-1 mb-0 pl-3">';
        foreach ($info['plugins'] as $plugin) {
            echo '<li class="small">' . esc_html(ucwords($plugin)) . '</li>';
        }
        echo '</ul></li>';
    }
    echo '</ul></div></div>';
}

// ─── 5. IMAGEN DESTACADA ─────────────────────────────────────────────────────
/**
 * Obtiene la URL de la imagen destacada de un post (JOIN posts + postmeta)
 * Retorna string con URL o '' si no tiene
 */
function wpshield_get_thumbnail_url(int $post_id): string {
    global $conn, $table_prefix;
    $sql = "SELECT att.guid
            FROM {$table_prefix}postmeta pm
            JOIN {$table_prefix}posts att
              ON att.ID = pm.meta_value
            WHERE pm.post_id = " . (int)$post_id . "
              AND pm.meta_key  = '_thumbnail_id'
              AND att.post_type = 'attachment'
            LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['guid'];
    }
    return '';
}

// ─── 6. LISTADO DE POSTS PRINCIPAL ──────────────────────────────────────────
/**
 * Obtiene posts publicados con filtros opcionales.
 * Soporta: search (título o contenido), month (YYYY-MM), cat (slug), slug (post_name)
 */
function wpshield_get_posts(array $filters = [], int $limit = 20, int $offset = 0): array {
    global $conn, $table_prefix;

    $where  = ["p.post_status = 'publish'", "p.post_type = 'post'"];
    $joins  = '';

    // Filtro de búsqueda
    if (!empty($filters['search'])) {
        $s = mysqli_real_escape_string($conn, $filters['search']);
        $where[] = "(p.post_title LIKE '%{$s}%' OR p.post_content LIKE '%{$s}%')";
    }

    // Filtro por mes YYYY-MM
    if (!empty($filters['month'])) {
        $m = mysqli_real_escape_string($conn, $filters['month']);
        $where[] = "DATE_FORMAT(p.post_date, '%Y-%m') = '{$m}'";
    }

    // Filtro por categoría (slug)
    if (!empty($filters['cat'])) {
        $c = mysqli_real_escape_string($conn, $filters['cat']);
        $joins = "JOIN {$table_prefix}term_relationships tr ON tr.object_id = p.ID
                  JOIN {$table_prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'category'
                  JOIN {$table_prefix}terms t ON t.term_id = tt.term_id AND t.slug = '{$c}'";
    }

    $where_clause = implode(' AND ', $where);
    $sql = "SELECT p.ID, p.post_title, p.post_name, p.post_date, p.post_excerpt, p.post_content
            FROM {$table_prefix}posts p
            {$joins}
            WHERE {$where_clause}
            ORDER BY p.post_date DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $result = mysqli_query($conn, $sql);
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

/**
 * Obtiene un post individual por su post_name (slug)
 */
function wpshield_get_post_by_slug(string $slug): array|false {
    global $conn, $table_prefix;
    $s   = mysqli_real_escape_string($conn, $slug);
    $sql = "SELECT * FROM {$table_prefix}posts
            WHERE post_name = '{$s}' AND post_status = 'publish'
            LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

/**
 * Genera un extracto limpio desde el contenido del post
 */
function wpshield_excerpt(string $content, int $words = 30): string {
    $clean = strip_tags($content);
    $clean = preg_replace('/\s+/', ' ', $clean);
    $arr   = explode(' ', trim($clean));
    if (count($arr) <= $words) return $clean;
    return implode(' ', array_slice($arr, 0, $words)) . '…';
}

// ─── 7. PAGINACIÓN ──────────────────────────────────────────────────────────
/**
 * Cuenta posts publicados según filtros
 */
function wpshield_count_posts(array $filters = []): int {
    global $conn, $table_prefix;
    $where = ["post_status = 'publish'", "post_type = 'post'"];
    if (!empty($filters['search'])) {
        $s = mysqli_real_escape_string($conn, $filters['search']);
        $where[] = "(post_title LIKE '%{$s}%' OR post_content LIKE '%{$s}%')";
    }
    if (!empty($filters['month'])) {
        $m = mysqli_real_escape_string($conn, $filters['month']);
        $where[] = "DATE_FORMAT(post_date, '%Y-%m') = '{$m}'";
    }
    $wc  = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) as c FROM {$table_prefix}posts WHERE {$wc}";
    $r   = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($r);
    return (int)$row['c'];
}
