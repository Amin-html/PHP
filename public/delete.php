<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    (new Post(Database::connect()))->delete($id);
}

redirect('index.php');