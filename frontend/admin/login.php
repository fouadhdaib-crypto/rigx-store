<?php
require_once __DIR__ . '/includes/config.php';
// Demo interface only: wire this to a real backend and password_verify() before enabling authentication.
$error = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
 ? 'Authentication is not configured. Connect your existing login backend before signing in.' : '';
?>
