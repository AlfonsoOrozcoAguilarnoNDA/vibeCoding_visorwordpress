<?php
/**
 * WP-Shield Viewer — editor.php
 * Modelo: Claude Sonnet 4.6 (Anthropic)
 * Licencia: MIT License — Copyright (c) 2025 WP-Shield Viewer
 *
 * ADVERTENCIA DE SEGURIDAD:
 * Este editor escribe directamente en la BD de WordPress.
 * Protégelo con autenticación HTTP básica o elimínalo cuando no lo uses.
 */

require_once __DIR__ . '/newconfig.php';
require_once __DIR__ . '/functions.php';

// ─── PROTECCIÓN MÍNIMA CON CONTRASEÑA ────────────────────────────────────────
// Cambia esta contraseña antes de usar el editor en producción.
define('EDITOR_PASSWORD', 'cambia_esta_clave_2025');

session_start();

$auth_error = '';
if (!isset($_SESSION['wpshield_editor_auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editor_pass'])) {
        if ($_POST['editor_pass'] === EDITOR_PASSWORD) {
            $_SESSION['wpshield_editor_auth'] = true;
        } else {
            $auth_error = 'Contraseña incorrecta.';
        }
    }
    if (!isset($_SESSION['wpshield_editor_auth'])) {
        // Mostrar formulario de autenticación
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Editor — WP-Shield Viewer</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="style.css">
        </head>
        <body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:#f4f6fb;">
        <div class="card" style="max-width:380px;width:100%;border-radius:10px;box-shadow:0 4px 24px rgba(26,31,54,.15);">
            <div class="card-header wpshield-card-header text-center">
                <i class="fas fa-lock mr-2"></i>Acceso al Editor
            </div>
            <div class="card-body p-4">
                <?php if ($auth_error): ?>
                <div class="alert alert-danger py-2"><?= esc_html($auth_error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="editor_pass">Contraseña de editor</label>
                        <input type="password" class="form-control" id="editor_pass"
                               name="editor_pass" placeholder="Contraseña" autofocus required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt mr-1"></i>Entrar
                    </button>
                </form>
            </div>
        </div>
        </body></html>
        <?php
        exit;
    }
}

// ─── PROCESAMIENTO DEL FORMULARIO DE GUARDADO ────────────────────────────────
$msg   = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_post') {
    $post_title   = trim(strip_tags($_POST['post_title']   ?? ''));
    $post_content = trim($_POST['post_content'] ?? '');  // Contenido puede tener HTML
    $post_status  = in_array($_POST['post_status'] ?? '', ['publish','draft']) ? $_POST['post_status'] : 'draft';
    $post_name    = !empty($_POST['post_name'])
                    ? preg_replace('/[^a-z0-9\-]/', '', strtolower(trim($_POST['post_name'])))
                    : preg_replace('/[^a-z0-9\-]/', '-', strtolower($post_title));

    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($post_title)) {
        $msg = 'El título no puede estar vacío.';
        $msg_type = 'danger';
    } else {
        if ($edit_id > 0) {
            // Actualizar post existente
            $t  = mysqli_real_escape_string($conn, $post_title);
            $c  = mysqli_real_escape_string($conn, $post_content);
            $n  = mysqli_real_escape_string($conn, $post_name);
            $st = mysqli_real_escape_string($conn, $post_status);
            $sql = "UPDATE {$table_prefix}posts SET
                        post_title   = '{$t}',
                        post_content = '{$c}',
                        post_name    = '{$n}',
                        post_status  = '{$st}',
                        post_modified = NOW(),
                        post_modified_gmt = UTC_TIMESTAMP()
                    WHERE ID = {$edit_id}";
            if (mysqli_query($conn, $sql)) {
                $msg = 'Post actualizado correctamente (ID: ' . $edit_id . ')';
            } else {
                $msg = 'Error al actualizar: ' . mysqli_error($conn);
                $msg_type = 'danger';
            }
        } else {
            // Nuevo post
            $t  = mysqli_real_escape_string($conn, $post_title);
            $c  = mysqli_real_escape_string($conn, $post_content);
            $n  = mysqli_real_escape_string($conn, $post_name);
            $st = mysqli_real_escape_string($conn, $post_status);
            // Usamos author_id = 1 (admin por defecto). Ajústalo si necesitas.
            $sql = "INSERT INTO {$table_prefix}posts
                        (post_author, post_date, post_date_gmt, post_content, post_title,
                         post_status, post_name, post_type, post_modified, post_modified_gmt,
                         comment_status, ping_status, to_ping, pinged, post_content_filtered)
                    VALUES
                        (1, NOW(), UTC_TIMESTAMP(), '{$c}', '{$t}',
                         '{$st}', '{$n}', 'post', NOW(), UTC_TIMESTAMP(),
                         'open', 'open', '', '', '')";
            if (mysqli_query($conn, $sql)) {
                $new_id = mysqli_insert_id($conn);
                $msg = "Post creado correctamente (ID: {$new_id})";
            } else {
                $msg = 'Error al insertar: ' . mysqli_error($conn);
                $msg_type = 'danger';
            }
        }
    }
}

// ─── CARGAR POST PARA EDICIÓN ────────────────────────────────────────────────
$edit_post = null;
if (isset($_GET['edit']) && (int)$_GET['edit'] > 0) {
    $eid = (int)$_GET['edit'];
    $sql = "SELECT * FROM {$table_prefix}posts WHERE ID = {$eid} LIMIT 1";
    $r   = mysqli_query($conn, $sql);
    $edit_post = mysqli_fetch_assoc($r);
}

// ─── LOGOUT ──────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    unset($_SESSION['wpshield_editor_auth']);
    header('Location: editor.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editor — WP-Shield Viewer</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        #post_content { min-height: 340px; font-family: monospace; font-size: .9rem; }
        .editor-toolbar .btn { border-radius: 3px; font-size: .8rem; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav id="wpshield-navbar" class="navbar navbar-expand-md">
    <a class="navbar-brand" href="index.php">
        <i class="fas fa-shield-alt mr-1"></i><span>WP-Shield</span>
    </a>
    <div class="ml-auto d-flex align-items-center">
        <a href="index.php" class="nav-link mr-2">
            <i class="fas fa-home mr-1"></i>Visor
        </a>
        <a href="editor.php?logout=1" class="nav-link text-warning">
            <i class="fas fa-sign-out-alt mr-1"></i>Salir
        </a>
    </div>
</nav>

<main id="wpshield-main">
<div class="container-fluid px-3 px-md-4">
<div class="row">

    <!-- ── COLUMNA EDITOR 8 ── -->
    <div class="col-md-8">
        <div class="card wpshield-card mb-4">
            <div class="card-header wpshield-card-header">
                <i class="fas fa-edit mr-2"></i>
                <?= $edit_post ? 'Editar post #' . (int)$edit_post['ID'] : 'Nuevo post' ?>
            </div>
            <div class="card-body">

                <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type ?> py-2"><?= esc_html($msg) ?></div>
                <?php endif; ?>

                <form method="POST" action="editor.php">
                    <input type="hidden" name="action" value="save_post">
                    <input type="hidden" name="edit_id"
                           value="<?= $edit_post ? (int)$edit_post['ID'] : 0 ?>">

                    <!-- Título -->
                    <div class="form-group">
                        <label for="post_title"><strong>Título</strong></label>
                        <input type="text" class="form-control" id="post_title"
                               name="post_title"
                               value="<?= esc_html($edit_post['post_title'] ?? '') ?>"
                               placeholder="Título del post" required>
                    </div>

                    <!-- Slug -->
                    <div class="form-group">
                        <label for="post_name">Slug <small class="text-muted">(URL amigable, auto-generado si vacío)</small></label>
                        <input type="text" class="form-control" id="post_name"
                               name="post_name"
                               value="<?= esc_html($edit_post['post_name'] ?? '') ?>"
                               placeholder="mi-nuevo-post">
                    </div>

                    <!-- Barra de formato básico -->
                    <div class="editor-toolbar mb-1 d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="wrapTag('strong')"><b>B</b></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="wrapTag('em')"><i>I</i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="wrapTag('h2')">H2</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="wrapTag('h3')">H3</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="wrapTag('p')">¶</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="insertLink()"><i class="fas fa-link fa-xs"></i> Link</button>
                    </div>

                    <!-- Contenido -->
                    <div class="form-group">
                        <label for="post_content"><strong>Contenido</strong> <small class="text-muted">(HTML permitido)</small></label>
                        <textarea class="form-control" id="post_content" name="post_content"
                                  rows="16"><?= esc_html($edit_post['post_content'] ?? '') ?></textarea>
                    </div>

                    <!-- Estado -->
                    <div class="form-group">
                        <label><strong>Estado</strong></label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" class="custom-control-input"
                                       id="status_pub" name="post_status" value="publish"
                                       <?= (!$edit_post || $edit_post['post_status'] === 'publish') ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="status_pub">
                                    <i class="fas fa-eye mr-1 text-success"></i>Publicado
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input"
                                       id="status_draft" name="post_status" value="draft"
                                       <?= ($edit_post && $edit_post['post_status'] === 'draft') ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="status_draft">
                                    <i class="fas fa-eye-slash mr-1 text-warning"></i>Borrador
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            <?= $edit_post ? 'Actualizar post' : 'Guardar post' ?>
                        </button>
                        <?php if ($edit_post): ?>
                        <a href="editor.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus mr-1"></i>Nuevo post
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /col-8 -->

    <!-- ── SIDEBAR 4: listado de posts recientes para edición ── -->
    <aside class="col-md-4">
        <div class="card wpshield-card mb-3">
            <div class="card-header wpshield-card-header">
                <i class="fas fa-list mr-2"></i>Posts para editar
            </div>
            <ul class="list-group list-group-flush">
                <?php
                $edit_list = wpshield_get_posts([], 20, 0);
                if (empty($edit_list)):
                ?>
                <li class="list-group-item text-muted small">Sin posts.</li>
                <?php else: foreach ($edit_list as $ep): ?>
                <li class="list-group-item wpshield-list-item d-flex justify-content-between align-items-center">
                    <div>
                        <a href="editor.php?edit=<?= (int)$ep['ID'] ?>" class="wpshield-link d-block">
                            <?= esc_html(mb_strimwidth($ep['post_title'], 0, 40, '…')) ?>
                        </a>
                        <small class="text-muted"><?= esc_html(date('d M Y', strtotime($ep['post_date']))) ?></small>
                    </div>
                    <a href="index.php?slug=<?= urlencode($ep['post_name']) ?>"
                       class="btn btn-outline-secondary btn-sm" title="Ver post" target="_blank">
                        <i class="fas fa-eye fa-xs"></i>
                    </a>
                </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>

        <!-- Info del modelo generador -->
        <div class="card wpshield-card">
            <div class="card-header wpshield-card-header">
                <i class="fas fa-robot mr-2"></i>Generado por
            </div>
            <div class="card-body py-2 small text-muted">
                <?= WPSHIELD_MODEL ?> · v<?= WPSHIELD_VERSION ?><br>
                <span class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>
                Protege este archivo con .htaccess o Basic Auth.</span>
            </div>
        </div>
    </aside><!-- /col-4 -->

</div><!-- /row -->
</div><!-- /container -->
</main>

<footer id="wpshield-footer">
    <span><i class="fas fa-edit mr-1"></i>Editor — WP-Shield Viewer</span>
    <span><?= WPSHIELD_MODEL ?></span>
</footer>

<!-- Bootstrap + jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
<script>
// ─── Helpers de edición básica ──────────────────────────────────────────────
function wrapTag(tag) {
    var ta   = document.getElementById('post_content');
    var start = ta.selectionStart, end = ta.selectionEnd;
    var sel  = ta.value.substring(start, end);
    var rep  = '<' + tag + '>' + (sel || 'texto') + '</' + tag + '>';
    ta.value = ta.value.substring(0, start) + rep + ta.value.substring(end);
    ta.focus();
    ta.selectionStart = start;
    ta.selectionEnd   = start + rep.length;
}
function insertLink() {
    var url = prompt('URL del enlace:', 'https://');
    if (!url) return;
    var ta   = document.getElementById('post_content');
    var start = ta.selectionStart, end = ta.selectionEnd;
    var text = ta.value.substring(start, end) || 'texto del enlace';
    var rep  = '<a href="' + url + '">' + text + '</a>';
    ta.value = ta.value.substring(0, start) + rep + ta.value.substring(end);
    ta.focus();
}
// Auto-slug desde título
document.getElementById('post_title').addEventListener('input', function() {
    var slug_field = document.getElementById('post_name');
    if (slug_field.dataset.manual) return; // No sobreescribir si el usuario lo editó
    slug_field.value = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
});
document.getElementById('post_name').addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
</body>
</html>
