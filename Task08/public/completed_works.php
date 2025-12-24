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

$stmt = $pdo->prepare("
    SELECT co.id as order_id, co.client_name, co.start_at, 
           s.name as service_name, coi.price, coi.duration, cw.completed_at
    FROM client_orders co
    JOIN completed_orders cw ON co.id = cw.client_order_id
    LEFT JOIN client_order_items coi ON co.id = coi.client_order_id
    LEFT JOIN services s ON coi.service_id = s.id
    WHERE co.employee_id = ?
    ORDER BY co.start_at DESC
");

$stmt->execute([$employee_id]);
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1>Выполненные работы - <?= htmlspecialchars($employee['name']) ?></h1>

<table border="1">
    <tr>
        <th>Клиент</th>
        <th>Дата начала</th>
        <th>Вид услуги</th>
        <th>Стоимость</th>
        <th>Продолжительность</th>
        <th>Дата выполнения</th>
        <th>Действия</th>
    </tr>
    <?php foreach ($works as $work): ?>
    <tr>
        <td><?= htmlspecialchars($work['client_name']) ?></td>
        <td><?= $work['start_at'] ?></td>
        <td><?= htmlspecialchars($work['service_name']) ?></td>
        <td><?= $work['price'] ?></td>
        <td><?= $work['duration'] ?> мин.</td>
        <td><?= $work['completed_at'] ?></td>
        <td>
            <a href="edit_completed_work.php?id=<?= $work['order_id'] ?>">Редактировать</a> |
            <a href="delete_completed_work.php?id=<?= $work['order_id'] ?>">Удалить</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<a href="add_completed_work.php?employee_id=<?= $employee_id ?>">Добавить работу</a> |
<a href="index.php">Назад</a>