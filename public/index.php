<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$posts = (new Post(Database::connect()))->all();

$pageTitle = 'Все посты';
require __DIR__ . '/../templates/header.php';
?>

<p><a class="btn" href="create.php">+ Новый пост</a></p>

<?php if (!$posts): ?>
    <p>Пока постов нет. Создай первый!</p>
<?php endif; ?>

<?php foreach ($posts as $post): ?>
    <article>
        <h2><a href="show.php?id=<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a></h2>
        <p><?= e(mb_strimwidth($post['content'], 0, 150, '…')) ?></p>
        <small><?= e($post['created_at']) ?></small>
    </article>
<?php endforeach; ?>

<?php require __DIR__ . '/../templates/footer.php'; ?>