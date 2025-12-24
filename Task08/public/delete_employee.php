<?php
require __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        
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

<h1>Удалить мастера</h1>

<p>Вы уверены, что хотите удалить мастера <?= htmlspecialchars($employee['name']) ?>?</p>
<form method="POST">
    <input type="submit" value="Да, удалить">
    <a href="index.php">Отмена</a>
</form>