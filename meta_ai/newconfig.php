<?php
/* WP-Shield Viewer v1.0 - Generado por IA - 2026-05-05 */
/*
MIT License

Copyright (c) 2026 WP-Shield Viewer

Permission is hereby granted, free of charge, to any person obtaining a copy...
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
