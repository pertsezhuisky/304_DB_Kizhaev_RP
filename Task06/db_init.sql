DROP TABLE IF EXISTS completed_orders;
DROP TABLE IF EXISTS client_order_items;
DROP TABLE IF EXISTS client_orders;
DROP TABLE IF EXISTS employee_salary;
DROP TABLE IF EXISTS employee_shift;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    percent NUMERIC(5,2) NOT NULL CHECK (percent >= 0 AND percent <= 100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
    archived_date DATE
);

CREATE TABLE services (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration INTEGER NOT NULL CHECK (duration > 0),
    gender VARCHAR(10) NOT NULL CHECK (gender IN ('male','female','both')),
    price NUMERIC(10,2) NOT NULL CHECK (price >= 0)
);

CREATE TABLE client_orders (
    id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL REFERENCES employees(id) ON DELETE RESTRICT,
    client_name VARCHAR(100) NOT NULL,
    start_at TIMESTAMP NOT NULL,
    status VARCHAR(20) NOT NULL CHECK (status IN ('planned','completed','canceled'))
);

CREATE TABLE client_order_items (
    id SERIAL PRIMARY KEY,
    client_order_id INTEGER NOT NULL REFERENCES client_orders(id) ON DELETE CASCADE,
    service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE RESTRICT,
    price NUMERIC(10,2) NOT NULL CHECK (price >= 0),
    duration INTEGER NOT NULL CHECK (duration > 0)
);

CREATE TABLE completed_orders (
    id SERIAL PRIMARY KEY,
    client_order_id INTEGER NOT NULL UNIQUE REFERENCES client_orders(id) ON DELETE CASCADE,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employee_shift (
    id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    CHECK (start_time < end_time)
);

CREATE TABLE employee_salary (
    id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL REFERENCES employees(id) ON DELETE RESTRICT,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    total_revenue NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (total_revenue >= 0),
    salary NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (salary >= 0),
    generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (from_date <= to_date)
);



INSERT INTO employees (id, name, percent, is_active) VALUES
(1, 'Иван Петров', 40, TRUE),
(2, 'Анна Смирнова', 50, TRUE),
(3, 'Сергей Волков', 45, TRUE),
(4, 'Мария Степанова', 55, TRUE),
(5, 'Павел Сидоров', 35, TRUE),
(6, 'Елена Кузнецова', 60, TRUE),
(7, 'Олег Морозов', 42, FALSE),
(8, 'Светлана Орлова', 48, TRUE);


INSERT INTO services (id, name, duration, gender, price) VALUES
(1, 'Стрижка мужская', 30, 'male', 800),
(2, 'Стрижка женская', 45, 'female', 1500),
(3, 'Окрашивание', 120, 'female', 3500),
(4, 'Борода + Усы', 25, 'male', 600),
(5, 'Укладка универсальная', 20, 'both', 500),
(6, 'Тонирование', 40, 'both', 1200),
(7, 'Макияж', 60, 'female', 3000),
(8, 'Детская стрижка', 25, 'both', 700);


INSERT INTO employee_shift (id, employee_id, date, start_time, end_time) VALUES
(1, 1, '2025-02-01', '09:00', '18:00'),
(2, 2, '2025-02-01', '10:00', '19:00'),
(3, 3, '2025-02-01', '09:00', '17:00'),
(4, 4, '2025-02-01', '11:00', '20:00'),
(5, 5, '2025-02-01', '09:00', '18:00'),
(6, 6, '2025-02-01', '12:00', '21:00'),
(7, 1, '2025-02-02', '09:00', '18:00'),
(8, 2, '2025-02-02', '10:00', '19:00');


INSERT INTO client_orders (id, employee_id, client_name, start_at, status) VALUES
(1, 1, 'Алексей',  '2025-02-01 10:00', 'planned'),
(2, 1, 'Дмитрий',  '2025-02-01 11:00', 'completed'),
(3, 2, 'Мария',    '2025-02-01 12:00', 'completed'),
(4, 3, 'София',    '2025-02-01 13:00', 'completed'),
(5, 4, 'Константин','2025-02-01 14:00', 'planned'),
(6, 5, 'Светлана', '2025-02-01 15:00', 'completed'),
(7, 6, 'Леонид',   '2025-02-01 16:00', 'completed'),
(8, 2, 'Вероника', '2025-02-01 17:00', 'completed'),
(9, 3, 'Глеб',     '2025-02-01 18:00', 'completed'),
(10,4, 'Катерина', '2025-02-01 19:00', 'completed'),
(11,5, 'Михаил',   '2025-02-02 10:00', 'planned'),
(12,6, 'Оксана',   '2025-02-02 11:00', 'completed');


INSERT INTO client_order_items (id, client_order_id, service_id, price, duration) VALUES
(1, 1, 1, 800, 30),
(2, 2, 1, 800, 30),
(3, 2, 4, 600, 25),
(4, 3, 2, 1500, 45),
(5, 4, 3, 3500, 120),
(6, 5, 6, 1200, 40),
(7, 6, 5, 500, 20),
(8, 7, 1, 800, 30),
(9, 7, 4, 600, 25),
(10, 8, 2, 1500, 45),
(11, 9, 8, 700, 25),
(12, 10, 7, 3000, 60),
(13, 11, 5, 500, 20),
(14, 12, 3, 3500, 120);


INSERT INTO completed_orders (id, client_order_id, completed_at) VALUES
(1, 2, '2025-02-01 11:40'),
(2, 3, '2025-02-01 13:00'),
(3, 4, '2025-02-01 15:20'),
(4, 6, '2025-02-01 16:10'),
(5, 7, '2025-02-01 16:50'),
(6, 8, '2025-02-01 18:10'),
(7, 9, '2025-02-01 18:40'),
(8, 10,'2025-02-01 20:20'),
(9, 12,'2025-02-02 13:10');


-- Автоматический расчёт зарплаты за период
INSERT INTO employee_salary (employee_id, from_date, to_date, total_revenue, salary)
SELECT
    e.id AS employee_id,
    '2025-02-01' AS from_date,
    '2025-02-28' AS to_date,
    COALESCE(SUM(coi.price), 0) AS total_revenue,
    COALESCE(SUM(coi.price), 0) * (e.percent / 100.0) AS salary
FROM employees e
LEFT JOIN client_orders co ON co.employee_id = e.id
    AND co.start_at >= '2025-02-01'
    AND co.start_at <= '2025-02-28'
    AND co.status = 'completed'
LEFT JOIN client_order_items coi ON coi.client_order_id = co.id
GROUP BY e.id;
