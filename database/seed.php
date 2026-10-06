<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$count = (int) ($argv[1] ?? 20);   // сколько постов создать, по умолчанию 20
$posts = new Post(Database::connect());

for ($i = 1; $i <= $count; $i++) {
    $posts->create(
        "Тестовый пост #$i",
        "Это текст тестового поста номер $i.\nВторая строка текста."
    );
}

echo "Создано постов: $count\n";