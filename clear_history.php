<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

$_SESSION['history'] = [];

echo json_encode(['ok' => true]);