-- postgresql

CREATE SEQUENCE test_table_id_seq;
CREATE TABLE test_table (
    id integer DEFAULT nextval('test_table_id_seq') NOT NULL PRIMARY KEY,
    title character varying(255),
    znak character(1),
    an_integer integer,
    price numeric(20,2),
    cena numeric(12,2),
    cena2 double precision,
    text text,
    perex text,
		flag boolean,
    binary_data bytea,
    binary_data2 bytea,
    create_date date,
    create_time timestamp without time zone DEFAULT now()
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
		created_at DATE,
		updated_at DATE,
		CONSTRAINT fk_articles_images FOREIGN KEY (image_id) REFERENCES images
);

CREATE SEQUENCE seq_authors;
CREATE TABLE authors(
   	id INTEGER DEFAULT NEXTVAL('seq_authors') NOT NULL PRIMARY KEY,
		name VARCHAR(255),
		email VARCHAR(255),
		created_at DATE,
		updated_at DATE
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
	created_at DATE,
	updated_at DATE
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
