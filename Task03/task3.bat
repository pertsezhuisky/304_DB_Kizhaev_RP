#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Составить список фильмов, имеющих хотя бы одну оценку. Список фильмов отсортировать по году выпуска и по названиям. В списке оставить первые 10 фильмов."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT m.id, m.title, m.year, COUNT(r.rating) AS rating_count
FROM movies m
INNER JOIN ratings AS r ON m.id = r.movie_id
GROUP BY m.id, m.title, m.year
HAVING COUNT(r.rating) >= 1
ORDER BY m.year, m.title
LIMIT 10;
"
echo " "

echo "2. Вывести список всех пользователей, фамилии (не имена!) которых начинаются на букву 'A'. Полученный список отсортировать по дате регистрации. В списке оставить первых 5 пользователей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT *
FROM users AS u
WHERE substr(u.name, instr(u.name, ' ') + 1) LIKE 'A%'
ORDER BY u.register_date
LIMIT 5;
"
echo " "

echo "3. Информация о рейтингах в более читаемом формате: имя и фамилия эксперта, название фильма, год выпуска, оценка и дата оценки в формате ГГГГ-ММ-ДД. Отсортировать по имени эксперта, названию фильма и оценке. Показать первые 50 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT u.name, m.title, m.year, r.rating, strftime('%Y-%m-%d', r.timestamp, 'unixepoch') AS rating_date
FROM ratings AS r
INNER JOIN users AS u ON r.user_id = u.id
INNER JOIN movies AS m ON r.movie_id = m.id
ORDER BY u.name, m.title, r.rating
LIMIT 50;
"
echo " "

echo "4. Список фильмов с указанием тегов, которые были им присвоены пользователями. Сортировка по году выпуска, названию фильма и тегу. Показать первые 40 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT m.title, t.tag, m.year
FROM tags AS t
INNER JOIN movies AS m ON t.movie_id = m.id
ORDER BY m.year, m.title, t.tag
LIMIT 40;
"
echo " "

echo "5. Список самых свежих фильмов (последний год выпуска)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT title, year
FROM movies
WHERE year = (SELECT MAX(year) FROM movies);
"
echo " "

echo "6. Все драмы, выпущенные после 2005 года, которые понравились женщинам (оценка >= 4.5). Название, год выпуска и количество таких оценок. Сортировка по году выпуска и названию."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT m.title, m.year, COUNT(r.rating) AS rating_count
FROM movies AS m
INNER JOIN ratings AS r ON m.id = r.movie_id
INNER JOIN users AS u ON r.user_id = u.id
WHERE m.genres LIKE '%Drama%'
  AND m.year > 2005
  AND u.gender = 'female'
  AND r.rating >= 4.5
GROUP BY m.id, m.title, m.year
ORDER BY m.year, m.title;
"
echo " "

echo "7. Анализ востребованности ресурса: количество пользователей, регистрировавшихся в каждом году. Годы с максимальным и минимальным количеством регистраций."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
-- Количество пользователей по годам
SELECT strftime('%Y', register_date) AS reg_year, COUNT(*) AS user_count
FROM users
GROUP BY reg_year
ORDER BY reg_year;
"

sqlite3 movies_rating.db -box -echo "
-- Год с максимальной регистрацией
SELECT strftime('%Y', register_date) AS reg_year, COUNT(*) AS user_count
FROM users
GROUP BY reg_year
ORDER BY user_count DESC
LIMIT 1;
"

sqlite3 movies_rating.db -box -echo "
-- Год с минимальной регистрацией
SELECT strftime('%Y', register_date) AS reg_year, COUNT(*) AS user_count
FROM users
GROUP BY reg_year
ORDER BY user_count ASC
LIMIT 1;
"