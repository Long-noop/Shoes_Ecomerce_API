<?php
// Authentication routes
$router->post('/api/auth/register', ['AuthController', 'register']);
$router->post('/api/auth/login', ['AuthController', 'login']);
$router->get('/api/auth/me', ['AuthController', 'me']);
$router->post('/api/auth/logout', ['AuthController', 'logout']);
$router->post('/api/auth/change-password', ['AuthController', 'changePassword']);
?>