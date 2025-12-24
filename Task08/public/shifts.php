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

$stmt = $pdo->prepare("SELECT * FROM employee_shift WHERE employee_id = ? ORDER BY date, start_time");
$stmt->execute([$employee_id]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>График работы - <?= htmlspecialchars($employee['name']) ?></h1>

<table border="1">
    <tr>
        <th>Дата</th>
        <th>Время начала</th>
        <th>Время окончания</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($shifts as $shift): ?>
    <tr>
        <td><?= $shift['date'] ?></td>
        <td><?= $shift['start_time'] ?></td>
        <td><?= $shift['end_time'] ?></td>
        <td>
            <a href="edit_shift.php?id=<?= $shift['id'] ?>">Редактировать</a> |
            <a href="delete_shift.php?id=<?= $shift['id'] ?>">Удалить</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<a href="add_shift.php?employee_id=<?= $employee_id ?>">Добавить смену</a> |
<a href="index.php">Назад</a>