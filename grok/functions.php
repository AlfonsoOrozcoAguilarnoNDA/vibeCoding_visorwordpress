<?php
/**
 * WP-Shield Viewer - functions.php
 * Versión: 1.0 - Grok 4 (xAI)
 * Licencia: MIT
 */

require_once 'newconfig.php';

// Sidebar dinámico
function get_sidebar_content() {
    global $conn;
    $prefix = TABLE_PREFIX;

    // Contar posts vs pages
    $sql = "SELECT post_type, COUNT(*) as total 
            FROM {$prefix}posts 
            WHERE post_status = 'publish' 
            GROUP BY post_type";
    $result = mysqli_query($conn, $sql);
    
    $posts_count = 0;
    $pages_count = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['post_type'] === 'post') $posts_count = $row['total'];
        if ($row['post_type'] === 'page') $pages_count = $row['total'];
    }

    echo '<div class="sidebar">';

    // Recent Posts (5)
    echo '<h5><i class="fas fa-clock"></i> Últimos Posts</h5><ul class="list-unstyled">';
    $sql = "SELECT ID, post_title, post_name FROM {$prefix}posts 
            WHERE post_status='publish' AND post_type='post' 
            ORDER BY post_date DESC LIMIT 5";
    $res = mysqli_query($conn, $sql);
    while ($p = mysqli_fetch_assoc($res)) {
        echo "<li><a href='?slug={$p['post_name']}'>".htmlspecialchars($p['post_title'])."</a></li>";
    }
    echo '</ul>';

    // Archivo por mes o Categorías
    if ($posts_count > $pages_count) {
        // Archivo por mes
        echo '<h5><i class="fas fa-archive"></i> Archivo</h5>';
        $sql = "SELECT YEAR(post_date) as y, MONTH(post_date) as m, COUNT(*) as c 
                FROM {$prefix}posts 
                WHERE post_status='publish' AND post_type='post'
                GROUP BY y, m ORDER BY y DESC, m DESC";
        $res = mysqli_query($conn, $sql);
        echo '<ul class="list-unstyled">';
        while ($row = mysqli_fetch_assoc($res)) {
            $month_name = date('F', mktime(0,0,0,$row['m'],1));
            echo "<li><a href='?year={$row['y']}&month={$row['m']}'>{$month_name} {$row['y']} ({$row['c']})</a></li>";
        }
        echo '</ul>';
    } else {
        // Categorías
        echo '<h5><i class="fas fa-tags"></i> Categorías</h5>';
        // ... (puedo ampliar si quieres)
    }

    echo '</div>';
}

// Featured Image
function get_featured_image($post_id) {
    global $conn;
    $prefix = TABLE_PREFIX;
    
    $sql = "SELECT pm2.meta_value as file 
            FROM {$prefix}postmeta pm1
            JOIN {$prefix}postmeta pm2 ON pm1.meta_value = pm2.post_id
            WHERE pm1.post_id = ? 
              AND pm1.meta_key = '_thumbnail_id'
              AND pm2.meta_key = '_wp_attached_file'";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return '/wp-content/uploads/' . $row['file'];
    }
    return null;
}
