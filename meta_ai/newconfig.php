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
define('DB_NAME', 'cambia_nombre_db');
define('DB_USER', 'cambia_usuario_db');
define('DB_PASSWORD', 'cambia_pass_db');
define('DB_HOST', 'localhost');
$table_prefix = 'wp_'; // Cambia si tu prefijo es diferente

// Credenciales para editor.php - CAMBIA ESTO YA
define('SHIELD_USER', 'admin');
define('SHIELD_PASS_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // password: password

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (!$conn) {
    error_log('WP-Shield DB Error: '. mysqli_connect_error());
    http_response_code(500);
    die('Error de conexión. Contacte al administrador.');
}
mysqli_set_charset($conn, 'utf8mb4');
?>
