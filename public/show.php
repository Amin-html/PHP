<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = $id ? (new Post(Database::connect()))->find($id) : null;

if ($post === null) {
    http_response_code(404);
    exit('Пост не найден');
}

$pageTitle = $post['title'];
require __DIR__ . '/../templates/header.php';
?>

<article>
    <p><?= nl2br(e($post['content'])) ?></p>
    <small>Создан: <?= e($post['created_at']) ?> · Изменён: <?= e($post['updated_at']) ?></small>
</article>

<p class="actions">
    <a class="btn" href="edit.php?id=<?= (int) $post['id'] ?>">Редактировать</a>

    <form method="post" action="delete.php"
          onsubmit="return confirm('Удалить пост?')">
        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
        <button type="submit" class="danger">Удалить</button>
    </form>
</p>

<?php require __DIR__ . '/../templates/footer.php'; ?>