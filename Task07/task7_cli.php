<?php

const DB_PATH = 'barbershop.db';

if (php_sapi_name() !== 'cli') {
    echo "Ошибка: Этот скрипт должен быть запущен из командной строки (CLI).\n";
    echo "Пожалуйста, используйте команду: php task7_cli.php\n";
    exit(1);
}
function print_table(array $headers, array $rows): void
{
    if (empty($rows)) {
        echo "Нет данных для отображения.\n";
        return;
    }

    $col_widths = array_fill(0, count($headers), 0);
    foreach ($headers as $i => $header) {
        $col_widths[$i] = max($col_widths[$i], mb_strlen($header, 'UTF-8'));
    }
    foreach ($rows as $row) {
        foreach ($row as $i => $cell) {
            $col_widths[$i] = max($col_widths[$i], mb_strlen((string)$cell, 'UTF-8'));
        }
    }

    $separator = '+';
    foreach ($col_widths as $width) {
        $separator .= str_repeat('-', $width + 2) . '+';
    }
    $separator .= "\n";

    $print_row = function (array $data, string $padding_char = ' ') use ($col_widths) {
        $line = '|';
        foreach ($data as $i => $cell) {
            $cell_str = (string)$cell;
            $padding = $col_widths[$i] - mb_strlen($cell_str, 'UTF-8');
            $line .= ' ' . $cell_str . str_repeat($padding_char, $padding) . ' |';
        }
        return $line . "\n";
    };

    echo $separator;
    echo $print_row($headers);
    echo $separator;
    foreach ($rows as $row) {
        echo $print_row($row);
    }
    echo $separator;
}

function get_masters_list(SQLite3 $db): array
{
    $masters = [];
    $query = "SELECT id, name FROM employees WHERE is_active = 1 ORDER BY name";
    $result = $db->query($query);

    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $masters[$row['id']] = $row['name'];
        }
    }
    return $masters;
}

function get_services_list(SQLite3 $db, ?int $master_id = null): array
{

    $query = "
        SELECT
            e.id AS master_id,
            e.name AS master_name,
            strftime('%Y-%m-%d', co.start_at) AS work_date,
            s.name AS service_name,
            coi.price AS service_cost,
            co.client_name AS client_name_col
        FROM client_orders co
        JOIN employees e ON co.employee_id = e.id
        JOIN client_order_items coi ON coi.client_order_id = co.id
        JOIN services s ON coi.service_id = s.id
        WHERE co.status = 'completed'
    ";

    $conditions = [];
    $params = [];

    if ($master_id !== null) {
        $conditions[] = "e.id = :master_id";
        $params[':master_id'] = $master_id;
    }


    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= "
        ORDER BY
            SUBSTR(e.name, INSTR(e.name, ' ') + 1),
            co.start_at
    ";

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $type = is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT;
        $stmt->bindValue($key, $value, $type);
    }

    $result = $stmt->execute();
    $services = [];

    if ($result) {
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $services[] = [
                $row['master_id'],
                $row['master_name'],
                $row['work_date'],
                $row['service_name'],
                number_format($row['service_cost'], 2, '.', ' ') . ' руб.',
                $row['client_name_col']
            ];
        }
    }

    return $services;
}


try {
    $db = new SQLite3(DB_PATH);

    $masters = get_masters_list($db);

    if (empty($masters)) {
        echo "В базе данных нет активных мастеров.\n";
        exit();
    }

    echo "--- Список мастеров ---\n";
    $master_output = [];
    foreach ($masters as $id => $name) {
        $master_output[] = "ID {$id}: {$name}";
    }
    echo implode("\n", $master_output) . "\n";
    echo "----------------------\n";

    $filter_master_id = null;
    while (true) {
        echo "Введите ID мастера для фильтрации (или нажмите Enter для вывода всех): ";
        $input = trim(fgets(STDIN));

        if (empty($input)) {
            echo "Вывод услуг для ВСЕХ мастеров.\n";
            break;
        }

        if (!is_numeric($input) || !isset($masters[(int)$input])) {
            echo "Ошибка: Введен некорректный ID мастера. Попробуйте снова.\n";
            continue;
        }
        $filter_master_id = (int)$input;
        echo "Выбраны услуги для мастера ID: {$filter_master_id} ({$masters[$filter_master_id]})\n";
        break;
    }

    $services_data = get_services_list($db, $filter_master_id);

    $headers = ['№ Мастера', 'ФИО Мастера', 'Дата работы', 'Услуга', 'Стоимость', 'Имя Клиента'];
    print_table($headers, $services_data);

    $db->close();

} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
?>
