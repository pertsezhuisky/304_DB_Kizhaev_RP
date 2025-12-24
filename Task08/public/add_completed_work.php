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

$stmt = $pdo->query("SELECT id, name, price FROM services ORDER BY name");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = $_POST['client_name'];
    $service_id = $_POST['service_id'];
    $start_at = date('Y-m-d H:i:s', strtotime($_POST['start_at']));
    $completed_at = date('Y-m-d H:i:s', strtotime($_POST['completed_at']));
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO client_orders (employee_id, client_name, start_at, status) VALUES (?, ?, ?, 'completed')");
    $stmt->execute([$employee_id, $client_name, $start_at]);
    $order_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT price, duration FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("INSERT INTO client_order_items (client_order_id, service_id, price, duration) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $service_id, $service['price'], $service['duration']]);
    
    $stmt = $pdo->prepare("INSERT INTO completed_orders (client_order_id, completed_at) VALUES (?, ?)");
    $stmt->execute([$order_id, $completed_at]);
    
    $pdo->commit();
    
    header('Location: completed_works.php?employee_id=' . $employee_id);
    exit;
}
?>

<h1>Добавить выполненную работу - <?= htmlspecialchars($employee['name']) ?></h1>

<form method="POST">
    <p>Клиент: <input type="text" name="client_name" required></p>
    <p>Услуга: 
        <select name="service_id" required>
            <option value="">Выберите услугу</option>
            <?php foreach ($services as $service): ?>
                <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?> (<?= $service['price'] ?> руб.)</option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>Дата начала: <input type="datetime-local" name="start_at" required></p>
    <p>Дата выполнения: <input type="datetime-local" name="completed_at" required></p>
    <p><input type="submit" value="Сохранить"></p>
    <p><a href="completed_works.php?employee_id=<?= $employee_id ?>">Отмена</a></p>
</form>