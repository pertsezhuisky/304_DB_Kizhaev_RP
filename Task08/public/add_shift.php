<?php
require __DIR__ . '/../config/database.php';

if (!isset($_GET['employee_id'])) {
    header('Location: index.php');
    exit;
}

$employee_id = $_GET['employee_id'];

$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Мастер не найден");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $pdo->prepare("INSERT INTO employee_shift (employee_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$employee_id, $date, $start_time, $end_time]);
    
    header('Location: shifts.php?employee_id=' . $employee_id);
    exit;
}
?>

<h1>Добавить смену - <?= htmlspecialchars($employee['name']) ?></h1>

<form method="POST">
    <p>Дата: <input type="date" name="date" required></p>
    <p>Время начала: <input type="time" name="start_time" required></p>
    <p>Время окончания: <input type="time" name="end_time" required></p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="shifts.php?employee_id=<?= $employee_id ?>">Отмена</a></p>
</form>