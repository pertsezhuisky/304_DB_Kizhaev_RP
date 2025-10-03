import csv
import os
import re

BASE_PATH = os.path.dirname(__file__)
DATASET_DIR = os.path.join(BASE_PATH, "dataset")
SQL_FILE = "db_init.sql"


def extract_year(title: str):
    if not title:
        return "NULL"

    match = re.search(r"\((\d{4})(?:[^\)]*)\)", title)
    return match.group(1) if match else "NULL"


def generate_sql():
    with open(SQL_FILE, "w", encoding="utf-8") as sql:

        sql.write(
            """
            DROP TABLE IF EXISTS movies;
            DROP TABLE IF EXISTS ratings;
            DROP TABLE IF EXISTS tags;
            DROP TABLE IF EXISTS users;

            """
        )

        sql.write(
            """
            CREATE TABLE movies (
                id INTEGER PRIMARY KEY,
                title TEXT,
                year INTEGER,
                genres TEXT
            );

            CREATE TABLE ratings (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                movie_id INTEGER,
                rating REAL,
                timestamp INTEGER
            );

            CREATE TABLE tags (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                movie_id INTEGER,
                tag TEXT,
                timestamp INTEGER
            );

            CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                name TEXT,
                email TEXT,
                gender TEXT,
                register_date TEXT,
                occupation TEXT
            );

            """
        )

        # movies
        with open(os.path.join(DATASET_DIR, "movies.csv"),
                  encoding="utf-8") as f:
            reader = csv.reader(f)
            for idx, row in enumerate(reader, start=1):
                title = row[1].replace("'", "''")
                year = extract_year(title)
                genres = row[1].replace("'", "''") if len(row) > 1 else ""
                sql.write(
                    f"INSERT INTO movies (id, title, year, genres) VALUES ({idx}, '{title}', {year}, '{genres}');\n"
                )

        # ratings
        with open(os.path.join(DATASET_DIR, "ratings.csv"),
                  encoding="utf-8") as f:
            reader = csv.reader(f)
            for idx, row in enumerate(reader, start=1):
                user_id, movie_id, rating, timestamp = row
                sql.write(
                    f"INSERT INTO ratings (id, user_id, movie_id, rating, timestamp) "
                    f"VALUES ({idx}, {user_id}, {movie_id}, {rating}, {timestamp});\n"
                )

        # tags
        with open(os.path.join(DATASET_DIR, "tags.csv"),
                  encoding="utf-8") as f:
            reader = csv.reader(f)
            for idx, row in enumerate(reader, start=1):
                user_id, movie_id, tag, timestamp = row
                tag = tag.replace("'", "''")
                sql.write(
                    f"INSERT INTO tags (id, user_id, movie_id, tag, timestamp) "
                    f"VALUES ({idx}, {user_id}, {movie_id}, '{tag}', {timestamp});\n"
                )

        # users
        with open(os.path.join(DATASET_DIR, "users.txt"),
                  encoding="utf-8") as f:
            reader = csv.reader(f, delimiter="|")
            for row in reader:
                user_id, name, email, gender, register_date, occupation = row
                name = name.replace("'", '"')
                sql.write(
                    f"INSERT INTO users (id, name, email, gender, register_date, occupation) "
                    f"VALUES ({user_id}, '{name}', '{email}', '{gender}', '{register_date}', '{occupation}');\n"
                )

    print(f"SQL-скрипт сгенерирован:{SQL_FILE}")


if __name__ == "__main__":
    generate_sql()
