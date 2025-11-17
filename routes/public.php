<?php
// Products - Public
$router->get('/api/products', ['ProductController', 'index']);
$router->get('/api/products/featured', ['ProductController', 'featured']);
$router->get('/api/products/{id}', ['ProductController', 'show']);
// Categories - Public
$router->get('/api/categories', ['CategoryController', 'index']);
$router->get('/api/categories/{id}', ['CategoryController', 'show']);

// Cart
$router->get('/api/cart',['CartController','index']);
$router->post('/api/cart',['CartController','addItem']);
$router->put('/api/cart/{id}',['CartController','updateQuantity']);
$router->delete('/api/cart/{id}',['CartController','removeItem']);
$router->delete('/api/cart',['CartController','clear']);

// Order
$router->get('/api/orders', ['OrderController', 'index']);
$router->get('/api/orders/{id}', ['OrderController', 'show']);
$router->post('/api/orders', ['OrderController', 'store']);
$router->put('/api/orders/{id}/status', ['OrderController', 'updateStatus']);

// Comment
$router->get('/api/comments', ['CommentController', 'index']);
$router->post('/api/comments', ['CommentController', 'store']);
$router->put('/api/comments/{id}', ['CommentController', 'update']);
$router->delete('/api/comments/{id}', ['CommentController', 'destroy']);

// Blog - Public
$router->get('/api/blogs', ['BlogController', 'index']);
$router->get('/api/blogs/{id}', ['BlogController', 'show']);

// Contact - Public
$router->post('/api/contacts', ['ContactController', 'store']);

// FAQ - Public
$router->get('/api/faqs', ['FAQController', 'index']);
$router->get('/api/faqs/{id}', ['FAQController', 'show']);
?>