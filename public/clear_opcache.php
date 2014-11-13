<?php 

header('content-type: application/json');

if (!strlen($_GET['k']) OR $_GET['k'] != getenv('CLEAR_OPCACHE_KEY')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'invalid key', 'timestamp' => time()], 192);
    exit();
}


opcache_reset();

echo json_encode(['success' => true, 'timestamp' => time()], 192);

