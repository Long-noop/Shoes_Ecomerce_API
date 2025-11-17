<?php
// Dashboard
$router->get('/api/admin/dashboard/statistics', ['DashboardController', 'statistics']);
$router->get('/api/admin/dashboard/recent-orders', ['DashboardController', 'recentOrders']);

// Products Management
$router->post('/api/admin/products', ['ProductController', 'store']);
$router->put('/api/admin/products/{id}', ['ProductController', 'update']);
$router->delete('/api/admin/products/{id}', ['ProductController', 'destroy']);

// Categories Management
$router->post('/api/admin/categories', ['CategoryController', 'store']);
$router->put('/api/admin/categories/{id}', ['CategoryController', 'update']);
$router->delete('/api/admin/categories/{id}', ['CategoryController', 'destroy']);

// Orders Management

// Users Management
$router->get('/api/admin/users', ['UserController', 'index']);
$router->get('/api/admin/users/{id}', ['UserController', 'show']);
$router->put('/api/admin/users/{id}', ['UserController', 'update']);
$router->delete('/api/admin/users/{id}', ['UserController', 'destroy']);
$router->post('/api/admin/users/{id}/ban', ['UserController', 'ban']);
$router->post('/api/admin/users/{id}/activate', ['UserController', 'activate']);

// Blog Management
$router->post('/api/admin/blogs', ['BlogController', 'store']);
$router->put('/api/admin/blogs/{id}', ['BlogController', 'update']);
$router->delete('/api/admin/blogs/{id}', ['BlogController', 'destroy']);

// Comments Management
$router->put('/api/admin/comments/{id}/approvation', ['CommentController', 'approve']);
$router->put('/api/admin/comments/{id}/rejection', ['CommentController', 'reject']);

// Contacts Management
$router->get('/api/admin/contacts', ['ContactController', 'index']);
$router->get('/api/admin/contacts/{id}', ['ContactController', 'show']);
$router->put('/api/admin/contacts/{id}/status', ['ContactController', 'updateStatus']);
$router->delete('/api/admin/contacts/{id}', ['ContactController', 'destroy']);

// FAQ Management
$router->post('/api/admin/faqs', ['FAQController', 'store']);
$router->put('/api/admin/faqs/{id}/status', ['FAQController', 'update']);
$router->delete('/api/admin/faqs/{id}', ['FAQController', 'destroy']);

// File Upload
$router->post('/api/admin/upload', ['UploadController', 'upload']);
?>