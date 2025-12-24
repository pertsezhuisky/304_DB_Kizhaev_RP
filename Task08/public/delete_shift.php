<?php
require __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM employee_shift WHERE id = ?");
    $stmt->execute([$id]);
    $shift = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shift) {
        die("Смена не найдена");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$shift['employee_id']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $employee_id = $shift['employee_id'];
        $stmt = $pdo->prepare("DELETE FROM employee_shift WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: shifts.php?employee_id=' . $employee_id);
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<h1>Удалить смену - <?= htmlspecialchars($employee['name']) ?></h1>

<p>Вы уверены, что хотите удалить смену на <?= $shift['date'] ?> с <?= $shift['start_time'] ?> до <?= $shift['end_time'] ?>?</p>
<form method="POST">
    <input type="submit" value="Да, удалить">
    <a href="shifts.php?employee_id=<?= $shift['employee_id'] ?>">Отмена</a>
</form>