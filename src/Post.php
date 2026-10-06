<?php
declare(strict_types=1);

final class Post
{
    public function __construct(private PDO $pdo)
    {
    }

    /** READ: все посты, новые сверху */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM posts ORDER BY created_at DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /** READ: один пост или null */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** CREATE: возвращает id нового поста */
    public function create(string $title, string $content): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (title, content) VALUES (:title, :content)'
        );
        $stmt->execute(['title' => $title, 'content' => $content]);

        return (int) $this->pdo->lastInsertId();
    }

    /** UPDATE */
    public function update(int $id, string $title, string $content): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE posts
             SET title = :title, content = :content, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'      => $id,
            'title'   => $title,
            'content' => $content,
        ]);
    }

    /** DELETE */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}