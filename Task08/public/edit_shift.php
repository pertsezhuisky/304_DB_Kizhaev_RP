<?php
require __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        $stmt = $pdo->prepare("UPDATE employee_shift SET date = ?, start_time = ?, end_time = ? WHERE id = ?");
        $stmt->execute([$date, $start_time, $end_time, $id]);
        
        $stmt = $pdo->prepare("SELECT employee_id FROM employee_shift WHERE id = ?");
        $stmt->execute([$id]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Location: shifts.php?employee_id=' . $shift['employee_id']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM employee_shift WHERE id = ?");
    $stmt->execute([$id]);
    $shift = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shift) {
        die("Смена не найдена");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$shift['employee_id']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header('Location: index.php');
    exit;
}
?>

<h1>Редактировать смену - <?= htmlspecialchars($employee['name']) ?></h1>

<form method="POST">
    <p>Дата: <input type="date" name="date" value="<?= $shift['date'] ?>" required></p>
    <p>Время начала: <input type="time" name="start_time" value="<?= $shift['start_time'] ?>" required></p>
    <p>Время окончания: <input type="time" name="end_time" value="<?= $shift['end_time'] ?>" required></p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="shifts.php?employee_id=<?= $shift['employee_id'] ?>">Отмена</a></p>
</form>