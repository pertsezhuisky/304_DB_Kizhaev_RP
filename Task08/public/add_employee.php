<?php
require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $percent = $_POST['percent'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $pdo->prepare("INSERT INTO employees (name, percent, is_active) VALUES (?, ?, ?)");
    $stmt->execute([$name, $percent, $is_active]);
    
    header('Location: index.php');
    exit;
}
?>

<h1>Добавить мастера</h1>

<form method="POST">
    <p>Имя: <input type="text" name="name" required></p>
    <p>Процент: <input type="number" name="percent" min="0" max="100" step="0.01" required></p>
    <p><input type="checkbox" name="is_active" checked> Активен</p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="index.php">Отмена</a></p>
</form>