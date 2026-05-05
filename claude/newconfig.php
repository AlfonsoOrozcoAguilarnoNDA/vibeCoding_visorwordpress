<?php
/**
 * WP-Shield Viewer — newconfig.php
 * Modelo: Claude Sonnet 4.6 (Anthropic)
 * Licencia: MIT License
 *
 * MIT License
 * Copyright (c) 2025 WP-Shield Viewer
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND.
 */

// ─── CONFIGURACIÓN DE RUTA ──────────────────────────────────────────────────
// El visor está en /viewer/ dentro del WP. wp-config.php está un nivel arriba.
define('WPSHIELD_VERSION', '1.0.0');
define('WPSHIELD_MODEL',   'Claude Sonnet 4.6 (Anthropic)');

$wp_config_path = __DIR__ . '/../wp-config.php';

if (!file_exists($wp_config_path)) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR: No se encontró wp-config.php en: ' . htmlspecialchars($wp_config_path) . '
        </div>');
}

// ─── LECTURA Y PARSEO DE wp-config.php ──────────────────────────────────────
$config_raw = file_get_contents($wp_config_path);

/**
 * Extrae el valor de una constante define('KEY', 'VALUE') del contenido de wp-config.php
 */
function wpshield_extract_constant(string $content, string $constant_name): string|false {
    // Soporta comillas simples y dobles, espacios variables
    $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant_name, '/') . "['\"]\s*,\s*['\"]([^'\"]*)['\"\s*]\)/";
    if (preg_match($pattern, $content, $matches)) {
        return $matches[1];
    }
    return false;
}

/**
 * Extrae $table_prefix del contenido de wp-config.php
 */
function wpshield_extract_table_prefix(string $content): string {
    if (preg_match('/\$table_prefix\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
        return $m[1];
    }
    return 'wp_'; // fallback estándar
}

// Extraer constantes de conexión
$db_name = wpshield_extract_constant($config_raw, 'DB_NAME');
$db_user = wpshield_extract_constant($config_raw, 'DB_USER');
$db_pass = wpshield_extract_constant($config_raw, 'DB_PASSWORD');
$db_host = wpshield_extract_constant($config_raw, 'DB_HOST');

// Validar que se extrajeron todos los valores necesarios
$extraction_errors = [];
if ($db_name === false) $extraction_errors[] = 'DB_NAME';
if ($db_user === false) $extraction_errors[] = 'DB_USER';
if ($db_pass === false) $extraction_errors[] = 'DB_PASSWORD';
if ($db_host === false) $extraction_errors[] = 'DB_HOST';

if (!empty($extraction_errors)) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR: No se pudieron extraer las constantes: ' . implode(', ', $extraction_errors) . '<br>
        Verifique el formato de wp-config.php.
        </div>');
}

// Extraer table_prefix
$table_prefix = wpshield_extract_table_prefix($config_raw);

// ─── CONEXIÓN A BASE DE DATOS ────────────────────────────────────────────────
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR DE CONEXIÓN: ' . htmlspecialchars(mysqli_connect_error()) . '<br>
        Host: ' . htmlspecialchars($db_host) . ' | DB: ' . htmlspecialchars($db_name) . '
        </div>');
}

// Forzar charset UTF-8
mysqli_set_charset($conn, 'utf8mb4');

// Definir constante de prefijo para uso global
define('DB_PREFIX', $table_prefix);
