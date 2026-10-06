<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $path = __DIR__ . '/../database/blog.sqlite';

            self::$pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$pdo;
    }
}