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
    
    $stmt = $pdo->query("SELECT id, name, price FROM services ORDER BY name");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_name = $_POST['client_name'];
        $service_id = $_POST['service_id'];
        $start_at = $_POST['start_at'];
        $completed_at = $_POST['completed_at'];
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE client_orders SET client_name = ?, start_at = ? WHERE id = ?");
        $stmt->execute([$client_name, $start_at, $order_id]);
        
        $stmt = $pdo->prepare("SELECT price, duration FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("UPDATE client_order_items SET service_id = ?, price = ?, duration = ? WHERE client_order_id = ?");
        $stmt->execute([$service_id, $service['price'], $service['duration'], $order_id]);
        
        $stmt = $pdo->prepare("UPDATE completed_orders SET completed_at = ? WHERE client_order_id = ?");
        $stmt->execute([$completed_at, $order_id]);
        
        $pdo->commit();
        
        header('Location: completed_works.php?employee_id=' . $order['employee_id']);
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>

<h1>Редактировать работу - <?= htmlspecialchars($order['employee_name']) ?></h1>

<form method="POST">
    <p>Клиент: <input type="text" name="client_name" value="<?= htmlspecialchars($order['client_name']) ?>" required></p>
    <p>Услуга: 
        <select name="service_id" required>
            <option value="">Выберите услугу</option>
            <?php foreach ($services as $service): ?>
                <option value="<?= $service['id'] ?>" <?= $service['id'] == $order['service_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($service['name']) ?> (<?= $service['price'] ?> руб.)
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>Дата начала: <input type="datetime-local" name="start_at" value="<?= substr($order['start_at'], 0, 16) ?>" required></p>
    <p>Дата выполнения: <input type="datetime-local" name="completed_at" value="<?= substr($order['completed_at'], 0, 16) ?>" required></p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="completed_works.php?employee_id=<?= $order['employee_id'] ?>">Отмена</a></p>
</form>