<?php
// Generate slug from string
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Format price
function formatPrice($price) {
    return number_format($price, 2);
}

// Generate random string
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Check if file is image
function isImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return in_array($file['type'], $allowedTypes);
}

// Upload file
function uploadFile($file, $directory = 'products') {
    if (!isImage($file)) {
        return false;
    }

    $uploadDir = UPLOAD_PATH . '/' . $directory . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = generateRandomString() . '_' . basename($file['name']);
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/uploads/' . $directory . '/' . $filename;
    }

    return false;
}

// Log error
function logError($message) {
    $logFile = ROOT_PATH . '/storage/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
?>