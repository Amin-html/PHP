<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Post.php';

/** Экранирование вывода — защита от XSS */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}