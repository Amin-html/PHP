<?php
/** @var string[] $errors */
/** @var string $title */
/** @var string $content */
/** @var string $submitLabel */
?>

<?php foreach ($errors as $error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Заголовок
        <input type="text" name="title" value="<?= e($title) ?>" maxlength="200">
    </label>

    <label>Текст
        <textarea name="content" rows="8"><?= e($content) ?></textarea>
    </label>

    <button type="submit"><?= e($submitLabel) ?></button>
</form>