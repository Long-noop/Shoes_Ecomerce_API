<?php
// Products - Public
$router->get('/api/products', ['ProductController', 'index']);
$router->get('/api/products/featured', ['ProductController', 'featured']);
$router->get('/api/products/{id}', ['ProductController', 'show']);
// Categories - Public
$router->get('/api/categories', ['CategoryController', 'index']);
$router->get('/api/categories/{id}', ['CategoryController', 'show']);

// Blog - Public
$router->get('/api/blogs', ['BlogController', 'index']);
$router->get('/api/blogs/{id}', ['BlogController', 'show']);

// Contact - Public
$router->post('/api/contact', ['ContactController', 'store']);

// FAQ - Public
$router->get('/api/faqs', ['FAQController', 'index']);
?>