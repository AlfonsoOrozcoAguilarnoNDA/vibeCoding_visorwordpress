<?php
/**
 * WP-Shield Viewer - newconfig.php
 * Versión: 1.0
 * Autor: Grok 4 (xAI) - Asistente de Alfonso Orozco Aguilar
 * Licencia: MIT
 * 
 * NO carga wp-load.php - Conexión directa segura
 */

header('X-Robots-Tag: noindex, nofollow');

$wp_config_path = '../wp-config.php';

if (!file_exists($wp_config_path)) {
    die('<h1>Error crítico: No se encuentra wp-config.php</h1>');
}

$config_content = file_get_contents($wp_config_path);

$defines = [
    'DB_NAME'     => "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"](.+?)['\"]\s*\)/",
    'DB_USER'     => "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"](.+?)['\"]\s*\)/",
    'DB_PASSWORD' => "/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"](.+?)['\"]\s*\)/",
    'DB_HOST'     => "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"](.+?)['\"]\s*\)/",
];

$config = [];
foreach ($defines as $key => $pattern) {
    if (preg_match($pattern, $config_content, $matches)) {
        $config[$key] = $matches[1];
    } else {
        die("<h1>Error: No se pudo extraer {$key} de wp-config.php</h1>");
    }
}

// Table prefix
if (preg_match("/\\\$table_prefix\s*=\s*['\"](.+?)['\"]/", $config_content, $matches)) {
    $table_prefix = $matches[1];
} else {
    $table_prefix = 'wp_';
}

$conn = mysqli_connect($config['DB_HOST'], $config['DB_USER'], $config['DB_PASSWORD'], $config['DB_NAME']);

if (!$conn) {
    die('<h1>Error de conexión a la base de datos</h1><p>Revisa los datos en wp-config.php</p>');
}

mysqli_set_charset($conn, 'utf8mb4');

define('TABLE_PREFIX', $table_prefix);
define('CONN', $conn); // Para usar en otros archivos
