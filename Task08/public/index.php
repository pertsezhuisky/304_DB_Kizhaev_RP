<?php
require __DIR__ . '/../config/database.php';

$stmt = $pdo->prepare("
    SELECT e.id, e.name, e.percent, e.is_active
    FROM employees e
    ORDER BY SUBSTR(e.name, INSTR(e.name, ' ') + 1)
");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Мастера парикмахерской</h1>

<table border="1">
    <tr>
        <th>Имя</th>
        <th>Процент</th>
        <th>Статус</th>
        <th>График</th>
        <th>Выполненные работы</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($employees as $employee): ?>
    <tr>
        <td><?= htmlspecialchars($employee['name']) ?></td>
        <td><?= $employee['percent'] ?>%</td>
        <td><?= $employee['is_active'] ? 'Активен' : 'Неактивен' ?></td>
        <td><a href="shifts.php?employee_id=<?= $employee['id'] ?>">График</a></td>
        <td><a href="completed_works.php?employee_id=<?= $employee['id'] ?>">Выполненные работы</a></td>
        <td>
            <a href="edit_employee.php?id=<?= $employee['id'] ?>">Редактировать</a> |
            <a href="delete_employee.php?id=<?= $employee['id'] ?>">Удалить</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<a href="add_employee.php">Добавить мастера</a> 