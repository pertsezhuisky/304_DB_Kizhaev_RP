INSERT OR IGNORE INTO users (name, email, gender, register_date, occupation_id)
VALUES 
('Кижаев Роман Петрович', 'rkizhaev@gmail.com', 'male', date('now'), NULL),
('Киселев Никита Сергеевич', 'kiselevns@gmail.com', 'male', date('now'), NULL),
('Кувакин Роман Александрович', 'kuvakinra@gmail.com', 'male', date('now'), NULL),
('Курмакаев Ренард Анварович', 'renardra@gmail.com', 'male', date('now'), NULL),
('Луковатая Ксения Вадимовна', 'lukovatayakv@gmail.com', 'female', date('now'), NULL);

INSERT INTO movies (title, year)
VALUES
('Аватар: Путь воды (2022)', 2022),
('Брат (1997)', 1997),
('Темный рыцарь (2008)', 2008);


INSERT OR IGNORE INTO genres (name) VALUES ('Sci-Fi');
INSERT OR IGNORE INTO genres (name) VALUES ('Action');
INSERT OR IGNORE INTO genres (name) VALUES ('Crime');
INSERT OR IGNORE INTO genres (name) VALUES ('Thriller');
INSERT OR IGNORE INTO genres (name) VALUES ('Drama');

INSERT INTO movies_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Action'
WHERE m.title = 'Аватар: Путь воды (2022)';

INSERT INTO movies_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Crime'
WHERE m.title = 'Брат (1997)';

INSERT INTO movies_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Thriller'
WHERE m.title = 'Темный рыцарь (2008)';


INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 3.9, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Аватар: Путь воды (2022)'
WHERE u.email = 'rkizhaev@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.3, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Брат (1997)'
WHERE u.email = 'rkizhaev@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 5.0, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Темный рыцарь (2008)'
WHERE u.email = 'rkizhaev@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);