<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$errors  = [];
$title   = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '')   { $errors[] = 'Заголовок обязателен'; }
    if ($content === '') { $errors[] = 'Текст обязателен'; }

    if (!$errors) {
        $id = (new Post(Database::connect()))->create($title, $content);
        redirect('show.php?id=' . $id);
    }
}

$pageTitle   = 'Новый пост';
$submitLabel = 'Создать';
require __DIR__ . '/../templates/header.php';
require __DIR__ . '/../templates/form.php';
require __DIR__ . '/../templates/footer.php';