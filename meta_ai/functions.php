<?php
/* WP-Shield Viewer v1.0 - Generado por IA - 2026-05-05 */
/*
MIT License
Copyright (c) 2026 WP-Shield Viewer
Permission is hereby granted, free of charge, to any person obtaining a copy...
*/

/**
 * Sanitiza slug para URLs. Solo a-z, 0-9 y guiones
 * @param string $slug
 * @return string
 */
function sanitize_slug($slug) {
    return preg_replace('/[^a-z0-9-]/', '', strtolower($slug));
}

/**
 * Rate limiting básico: 60 req/min por sesión
 * @return void
 */
function rate_limit() {
    if (!isset($_SESSION['shield_requests'])) {
        $_SESSION['shield_requests'] = ['count' => 1, 'time' => time()];
    } else {
        if (time() - $_SESSION['shield_requests']['time'] > 60) {
            $_SESSION['shield_requests'] = ['count' => 1, 'time' => time()];
        } else {
            $_SESSION['shield_requests']['count']++;
            if ($_SESSION['shield_requests']['count'] > 60) {
                http_response_code(429);
                die('Too many requests. Intenta en 1 minuto.');
            }
        }
    }
}

/**
 * Obtiene contenido del sidebar: archivos por mes o categorías
 * @param mysqli $conn
 * @param string $table_prefix
 * @return array
 */
function get_sidebar_content($conn, $table_prefix) {
    $result = ['type' => 'categories', 'data' => []];
    
    $sql_count = "SELECT post_type, COUNT(*) as total FROM {$table_prefix}posts 
                  WHERE post_status='publish' GROUP BY post_type";
    $res = mysqli_query($conn, $sql_count);
    $posts_count = 0; $pages_count = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['post_type'] == 'post') $posts_count = $row['total'];
        if ($row['post_type'] == 'page') $pages_count = $row['total'];
    }

    if ($posts_count >= $pages_count) {
        $result['type'] = 'archives';
        $sql = "SELECT DATE_FORMAT(post_date, '%Y-%m') as mes, 
                DATE_FORMAT(post_date, '%M %Y') as mes_nombre,
                COUNT(*) as total 
                FROM {$table_prefix}posts 
                WHERE post_status='publish' AND post_type='post' 
                GROUP BY mes ORDER BY mes DESC LIMIT 12";
        $res = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $result['data'][] = $row;
        }
    } else {
        $sql = "SELECT t.name, t.slug, tt.count FROM {$table_prefix}terms t 
                INNER JOIN {$table_prefix}term_taxonomy tt ON t.term_id = tt.term_id 
                WHERE tt.taxonomy='category' AND tt.count > 0 
                ORDER BY tt.count DESC LIMIT 20";
        $res = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $result['data'][] = $row;
        }
    }
    return $result;
}

/**
 * Obtiene los 5 posts más recientes
 * @param mysqli $conn
 * @param string $table_prefix
 * @param int $limit
 * @return array
 */
function get_recent_posts($conn, $table_prefix, $limit = 5) {
    $limit = (int)$limit;
    $sql = "SELECT ID, post_title, post_name, post_date 
            FROM {$table_prefix}posts 
            WHERE post_status='publish' AND post_type='post' 
            ORDER BY post_date DESC LIMIT $limit";
    $res = mysqli_query($conn, $sql);
    $posts = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $posts[] = $row;
    }
    return $posts;
}

/**
 * Obtiene info de tema activo y plugins
 * @param mysqli $conn
 * @param string $table_prefix
 * @return array
 */
function get_admin_info($conn, $table_prefix) {
    $info = ['theme' => 'N/A', 'plugins' => []];
    
    $sql_theme = "SELECT option_value FROM {$table_prefix}options WHERE option_name='template' LIMIT 1";
    $res = mysqli_query($conn, $sql_theme);
    if ($row = mysqli_fetch_assoc($res)) $info['theme'] = htmlspecialchars($row['option_value']);
    
    $sql_plugins = "SELECT option_value FROM {$table_prefix}options WHERE option_name='active_plugins' LIMIT 1";
    $res = mysqli_query($conn, $sql_plugins);
    if ($row = mysqli_fetch_assoc($res)) {
        $plugins = @unserialize($row['option_value'], ['allowed_classes' => false]);
        if (is_array($plugins)) {
            foreach ($plugins as $plugin) {
                $info['plugins'][] = htmlspecialchars(basename($plugin, '.php'));
            }
        }
    }
    return $info;
}

/**
 * Obtiene URL de imagen destacada
 * @param mysqli $conn
 * @param string $table_prefix
 * @param int $post_id
 * @return string|null
 */
function get_featured_image($conn, $table_prefix, $post_id) {
    $post_id = (int)$post_id;
    $sql = "SELECT p.guid FROM {$table_prefix}posts p 
            INNER JOIN {$table_prefix}postmeta pm ON p.ID = pm.meta_value 
            WHERE pm.post_id = $post_id AND pm.meta_key = '_thumbnail_id' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        return htmlspecialchars($row['guid']);
    }
    return null;
}
?>
