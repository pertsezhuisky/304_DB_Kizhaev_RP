#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < .\db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они ценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT DISTINCT
    u1.name AS user1_name,
    u2.name AS user2_name,
    m.title AS movie_title
FROM ratings r1
JOIN ratings r2 
    ON r1.movie_id = r2.movie_id 
    AND r1.user_id < r2.user_id
JOIN users u1 ON r1.user_id = u1.id
JOIN users u2 ON r2.user_id = u2.id
JOIN movies m ON r1.movie_id = m.id
LIMIT 100;"
echo " "

echo "2. Найти 10 самых старых оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    u.name AS user_name,
    m.title AS movie_title,
    r.rating,
    strftime('%Y-%m-%d', datetime(r.timestamp, 'unixepoch')) AS review_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
WHERE r.id IN (
    SELECT MIN(id)
    FROM ratings
    GROUP BY user_id
)
ORDER BY r.timestamp ASC
LIMIT 10;"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке 'Рекомендуем' для фильмов должно быть написано 'Да' или 'Нет'."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH movie_stats AS (
    SELECT 
        m.title,
        m.year,
        ROUND(AVG(r.rating), 2) AS avg_rating
    FROM movies m
    JOIN ratings r ON m.id = r.movie_id
    GROUP BY m.id, m.title, m.year
),
extremes AS (
    SELECT MAX(avg_rating) AS max_rating, MIN(avg_rating) AS min_rating FROM movie_stats
)
SELECT 
    s.title,
    s.year,
    s.avg_rating,
    CASE 
        WHEN s.avg_rating = (SELECT max_rating FROM extremes) THEN 'Да'
        ELSE 'Нет'
    END AS Рекомендуем
FROM movie_stats s, extremes
WHERE s.avg_rating IN (extremes.max_rating, extremes.min_rating)
ORDER BY s.year, s.title;"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-мужчины в период с 2011 по 2014 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    COUNT(r.id) AS ratings_count,
    ROUND(AVG(r.rating), 2) AS average_rating
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE 
    u.gender = 'male'
    AND strftime('%Y', datetime(r.timestamp, 'unixepoch')) BETWEEN '2011' AND '2014';"
echo " "

echo "5. Составить список фильмов с указанием средней оценки и количества пользователей, которые их оценили. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    m.title AS movie_title,
    m.year AS release_year,
    ROUND(AVG(r.rating), 2) AS average_rating,
    COUNT(DISTINCT r.user_id) AS users_rated
FROM movies m
JOIN ratings r ON m.id = r.movie_id
GROUP BY m.id, m.title, m.year
ORDER BY m.year ASC, m.title ASC
LIMIT 20;"
echo " "

echo "6. Определить самый распространенный жанр фильма и количество фильмов в этом жанре. Отдельную таблицу для жанров не использовать, жанры нужно извлекать из таблицы movies."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH split_genres AS (
    SELECT 
        TRIM(value) AS genre
    FROM movies, 
         json_each('[' || REPLACE(genres, '|', '","') || ']')
)
SELECT 
    genre,
    COUNT(*) AS movie_count
FROM split_genres
GROUP BY genre
ORDER BY movie_count DESC
LIMIT 1;"
echo " "

echo "7. Вывести список из 10 последних зарегистрированных пользователей в формате 'Фамилия Имя|Дата регистрации' (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "SELECT 
    TRIM(substr(u.name, instr(u.name, ' ') + 1)) || ' ' || 
    TRIM(substr(u.name, 1, instr(u.name, ' ') - 1)) || '|' || 
    u.register_date AS 'Фамилия Имя|Дата регистрации'
FROM users u
ORDER BY datetime(u.register_date) DESC
LIMIT 10;"
echo " "

echo "8. С помощью рекурсивного CTE определить, на какие дни недели приходился ваш день рождения в каждом году."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE years(y) AS (
    SELECT 2004
    UNION ALL
    SELECT y + 1
    FROM years
    WHERE y + 1 <= CAST(strftime('%Y', 'now') AS INTEGER)
)
SELECT 
    y AS year,
    strftime('%Y-%m-%d', y || '-12-23') AS birthday_date,
    CASE strftime('%w', y || '-12-23')
        WHEN '0' THEN 'Воскресенье'
        WHEN '1' THEN 'Понедельник'
        WHEN '2' THEN 'Вторник'
        WHEN '3' THEN 'Среда'
        WHEN '4' THEN 'Четверг'
        WHEN '5' THEN 'Пятница'
        WHEN '6' THEN 'Суббота'
    END AS weekday
FROM years;"
echo " "
