<?php
const DB_PATH = 'sqlite:barbershop.db';
const DB_USER = '';
const DB_PASSWORD = '';

$masters = [];
$services = [];
$selectedMasterId = null;
$errorMessage = '';

try {
    $pdo = new PDO(DB_PATH, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $masterQuery = "SELECT id, name FROM employees WHERE is_active = 1 ORDER BY name";
    $masterStatement = $pdo->prepare($masterQuery);
    $masterStatement->execute();
    $masters = $masterStatement->fetchAll(PDO::FETCH_ASSOC);
    
    if (isset($_GET['master_id']) && !empty($_GET['master_id'])) {
        $selectedMasterId = (int)$_GET['master_id'];
        
        $validMasterIds = array_column($masters, 'id');
        if (!in_array($selectedMasterId, $validMasterIds)) {
            $errorMessage = "Invalid master ID selected.";
            $selectedMasterId = null;
        }
    }
    
    $serviceQuery = "
        SELECT
            e.id AS master_id,
            e.name AS master_name,
            strftime('%Y-%m-%d', co.start_at) AS work_date,
            s.name AS service_name,
            coi.price AS service_cost,
            co.client_name AS client_name
        FROM client_orders co
        JOIN employees e ON co.employee_id = e.id
        JOIN client_order_items coi ON coi.client_order_id = co.id
        JOIN services s ON coi.service_id = s.id
        WHERE co.status = 'completed'
    ";
    
    $params = [];
    
    if ($selectedMasterId !== null) {
        $serviceQuery .= " AND e.id = :master_id";
        $params[':master_id'] = $selectedMasterId;
    }
    
    $serviceQuery .= "
        ORDER BY
            SUBSTR(e.name, INSTR(e.name, ' ') + 1),
            co.start_at
    ";
    
    $serviceStatement = $pdo->prepare($serviceQuery);
    $serviceStatement->execute($params);
    $services = $serviceStatement->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errorMessage = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парикмахерская</title>
</head>
<body>
    <div>
        <h1>Парикмахерская</h1>
        <p>Показать таблицу с заказами наших мастеров</p>
        
        <?php if (!empty($errorMessage)): ?>
            <div>
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($masters)): ?>
            <div>
                <form method="GET" action="">
                    <div>
                        <label for="master_id">Выбрать мастера:</label>
                        <select name="master_id" id="master_id" onchange="this.form.submit()">
                            <option value="">-- Все мастера --</option>
                            <?php foreach ($masters as $master): ?>
                                <option value="<?php echo $master['id']; ?>" <?php echo ($selectedMasterId === $master['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($master['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p>Выберете конкретного мастера для просмотра сделанных работ или оставьте поле пустым для показа всех мастеров.</p>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($services)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя работника</th>
                        <th>Дата заказа</th>
                        <th>Наименование услуги</th>
                        <th>Цена</th>
                        <th>Имя клиента</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['master_id']); ?></td>
                            <td><?php echo htmlspecialchars($service['master_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['work_date']); ?></td>
                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                            <td><?php echo number_format($service['service_cost'], 2, '.', ' '); ?> руб.</td>
                            <td><?php echo htmlspecialchars($service['client_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div>
                <?php if ($selectedMasterId !== null): ?>
                    Для текущего мастера не найдены работы.
                <?php else: ?>
                    Для текущего мастера не существует таких выполненных работ.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
