<?php
/**
 * WP-Shield Viewer — newconfig.php
 * Modelo: Claude Sonnet 4.6 (Anthropic)
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

define('WPSHIELD_VERSION', '1.1.0');
define('WPSHIELD_MODEL',   'Claude Sonnet 4.6 (Anthropic)');

// ─── RUTA A WP-CONFIG ────────────────────────────────────────────────────────
$wp_config_path = __DIR__ . '/../wp-config.php';

if (!file_exists($wp_config_path)) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR: No se encontró wp-config.php en: '
        . htmlspecialchars($wp_config_path) .
        '</div>');
}

// ─── STUBS MÍNIMOS ───────────────────────────────────────────────────────────
// wp-config.php puede llamar funciones de WP que no existen fuera del entorno.
// Definimos stubs vacíos para evitar errores fatales al hacer el include.
if (!function_exists('add_filter')) {
    function add_filter() {}
}
if (!function_exists('add_action')) {
    function add_action() {}
}
if (!function_exists('do_action')) {
    function do_action() {}
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) { return $value; }
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// ─── INCLUDE CONTROLADO DE WP-CONFIG ─────────────────────────────────────────
// ob_start() captura cualquier output accidental que wp-config pudiera generar.
ob_start();
try {
    include $wp_config_path;
} catch (Throwable $e) {
    ob_end_clean();
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR al cargar wp-config.php: '
        . htmlspecialchars($e->getMessage()) .
        '</div>');
}
ob_end_clean();

// ─── VERIFICAR CONSTANTES EXTRAÍDAS ──────────────────────────────────────────
$required = ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'];
$missing  = [];

foreach ($required as $const) {
    if (!defined($const)) {
        $missing[] = $const;
    }
}

if (!empty($missing)) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR: Constantes no encontradas en wp-config.php: '
        . htmlspecialchars(implode(', ', $missing)) .
        '</div>');
}

// $table_prefix viene directo del include
if (!isset($table_prefix)) {
    $table_prefix = 'wp_';
}

// ─── CONEXIÓN A BASE DE DATOS ─────────────────────────────────────────────────
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!$conn) {
    die('<div style="font-family:monospace;color:red;padding:20px;">
        [WP-Shield] ERROR DE CONEXIÓN: '
        . htmlspecialchars(mysqli_connect_error()) .
        '<br>Host: ' . htmlspecialchars(DB_HOST) .
        ' | DB: '   . htmlspecialchars(DB_NAME) .
        '</div>');
}

mysqli_set_charset($conn, 'utf8mb4');

define('DB_PREFIX', $table_prefix);
