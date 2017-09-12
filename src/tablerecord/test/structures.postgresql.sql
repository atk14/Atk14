-- postgresql

-- $ cat testing_structures.postgresql.sql | psql -U test test

DROP TABLE IF EXISTS article_redactors CASCADE;
DROP TABLE IF EXISTS redactors CASCADE;
DROP TABLE IF EXISTS article_authors CASCADE;
DROP TABLE IF EXISTS authors CASCADE;
DROP TABLE IF EXISTS articles CASCADE;
DROP TABLE IF EXISTS images CASCADE;
DROP TABLE IF EXISTS test_table CASCADE;

DROP SEQUENCE IF EXISTS seq_article_redactors;
DROP SEQUENCE IF EXISTS seq_redactors;
DROP SEQUENCE IF EXISTS seq_article_authors;
DROP SEQUENCE IF EXISTS seq_authors;
DROP SEQUENCE IF EXISTS seq_articles;
DROP SEQUENCE IF EXISTS seq_images;
DROP SEQUENCE IF EXISTS test_table_id_seq;

CREATE SEQUENCE test_table_id_seq;
CREATE TABLE test_table (
    id integer DEFAULT NEXTVAL('test_table_id_seq') NOT NULL PRIMARY KEY,
    title VARCHAR(255),
    znak CHARACTER(1),
    an_integer INTEGER,
    a_big_integer BIGINT,
    price NUMERIC(20,2),
    cena NUMERIC(12,2),
    cena2 DOUBLE PRECISION,
    text TEXT,
    perex TEXT,
    flag BOOLEAN,
    binary_data BYTEA,
    binary_data2 BYTEA,
    create_date DATE,
    create_time TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
);

CREATE SEQUENCE seq_images;
CREATE TABLE images (
	id INTEGER DEFAULT NEXTVAL('seq_images') NOT NULL PRIMARY KEY,
	url VARCHAR(255),
	width INT,
	height INT
);

CREATE SEQUENCE seq_articles;
CREATE TABLE articles(
    id INTEGER DEFAULT NEXTVAL('seq_articles') NOT NULL PRIMARY KEY,
		title VARCHAR(255),
		body TEXT,
		image_id INT,
		created_at TIMESTAMP,
		updated_at TIMESTAMP,
		CONSTRAINT fk_articles_images FOREIGN KEY (image_id) REFERENCES images
);

CREATE SEQUENCE seq_authors;
CREATE TABLE authors(
   	id INTEGER DEFAULT NEXTVAL('seq_authors') NOT NULL PRIMARY KEY,
		name VARCHAR(255),
		email VARCHAR(255),
		created_at TIMESTAMP,
		updated_at TIMESTAMP
);

-- No unique constraint in the table
CREATE SEQUENCE seq_article_authors;
CREATE TABLE article_authors(
	id INTEGER DEFAULT NEXTVAL('seq_article_authors') NOT NULL PRIMARY KEY,
	article_id INTEGER NOT NULL,
	author_id INTEGER NOT NULL,
	rank INTEGER DEFAULT 999 NOT NULL,
	CONSTRAINT fk_article_authors_articles FOREIGN KEY (article_id) REFERENCES articles ON DELETE CASCADE,
	CONSTRAINT fk_author_authors_authors FOREIGN KEY (author_id) REFERENCES authors ON DELETE CASCADE
);

CREATE SEQUENCE seq_redactors;
CREATE TABLE redactors(
	id INTEGER DEFAULT NEXTVAL('seq_redactors') NOT NULL PRIMARY KEY,
	name VARCHAR(255),
	email VARCHAR(255),
	created_at TIMESTAMP,
	updated_at TIMESTAMP
);

-- There is a unique constraint
CREATE SEQUENCE seq_article_redactors;
CREATE TABLE article_redactors(
	id INTEGER DEFAULT NEXTVAL('seq_article_redactors') NOT NULL PRIMARY KEY,
	article_id INTEGER NOT NULL,
	redactor_id INTEGER NOT NULL,
	rank INTEGER DEFAULT 999 NOT NULL,
	CONSTRAINT unq_article_redactor UNIQUE(redactor_id, article_id),
	CONSTRAINT fk_article_redactors_articles FOREIGN KEY (article_id) REFERENCES articles ON DELETE CASCADE,
	CONSTRAINT fk_author_redactors_redactors FOREIGN KEY (redactor_id) REFERENCES redactors ON DELETE CASCADE
);
