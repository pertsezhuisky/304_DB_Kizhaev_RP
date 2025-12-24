<?php
require __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $percent = $_POST['percent'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE employees SET name = ?, percent = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $percent, $is_active, $id]);
        
        header('Location: index.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        die("Мастер не найден");
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<h1>Редактировать мастера</h1>

<form method="POST">
    <p>Имя: <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required></p>
    <p>Процент: <input type="number" name="percent" min="0" max="100" step="0.01" value="<?= $employee['percent'] ?>" required></p>
    <p><input type="checkbox" name="is_active" <?= $employee['is_active'] ? 'checked' : '' ?>> Активен</p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="index.php">Отмена</a></p>
</form>