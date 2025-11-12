<?php
// Load all route files
require_once ROOT_PATH . '/routes/auth.php';
require_once ROOT_PATH . '/routes/public.php';
require_once ROOT_PATH . '/routes/admin.php';

// Health check
$router->get('/api/health', function() {
    $response = new Response();
    $response->json([
        'status' => 'OK',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => API_VERSION
    ]);
});
?>