<?php
require __DIR__ . '/../config/database.php';

if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT co.*, coi.service_id, coi.price as item_price, coi.duration, 
               cw.completed_at,
               e.name as employee_name
        FROM client_orders co
        JOIN client_order_items coi ON co.id = coi.client_order_id
        JOIN completed_orders cw ON co.id = cw.client_order_id
        JOIN employees e ON co.employee_id = e.id
        WHERE co.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("Работа не найдена");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $employee_id = $order['employee_id'];
        
        $stmt = $pdo->prepare("DELETE FROM client_orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        header('Location: completed_works.php?employee_id=' . $employee_id);
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<h1>Удалить работу - <?= htmlspecialchars($order['employee_name']) ?></h1>

<p>Вы уверены, что хотите удалить выполненную работу?</p>
<p>Клиент: <?= htmlspecialchars($order['client_name']) ?></p>
<p>Стоимость: <?= $order['item_price'] ?> руб.</p>
<p>Дата начала: <?= $order['start_at'] ?></p>
<p>Дата выполнения: <?= $order['completed_at'] ?></p>
<form method="POST">
    <input type="submit" value="Да, удалить">
    <a href="completed_works.php?employee_id=<?= $order['employee_id'] ?>">Отмена</a>
</form>