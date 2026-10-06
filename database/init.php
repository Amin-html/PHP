<?php
declare(strict_types=1);

require __DIR__ . '/../src/Database.php';

$pdo = Database::connect();

$pdo->exec('
    CREATE TABLE IF NOT EXISTS posts (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        title      TEXT NOT NULL,
        content    TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
');

echo "База готова: database/blog.sqlite\n";