<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$posts = new Post(Database::connect());

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = $id ? $posts->find($id) : null;

if ($post === null) {
    http_response_code(404);
    exit('Пост не найден');
}

$errors  = [];
$title   = $post['title'];
$content = $post['content'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '')   { $errors[] = 'Заголовок обязателен'; }
    if ($content === '') { $errors[] = 'Текст обязателен'; }

    if (!$errors) {
        $posts->update($id, $title, $content);
        redirect('show.php?id=' . $id);
    }
}

$pageTitle   = 'Редактирование';
$submitLabel = 'Сохранить';
require __DIR__ . '/../templates/header.php';
require __DIR__ . '/../templates/form.php';
require __DIR__ . '/../templates/footer.php';