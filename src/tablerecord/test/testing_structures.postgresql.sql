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
    binary_data bytea,
    binary_data2 bytea,
    create_date date,
    create_time timestamp without time zone DEFAULT now(),
		flag boolean
);

CREATE SEQUENCE seq_articles;
CREATE TABLE articles(
    id INTEGER DEFAULT NEXTVAL('seq_articles') NOT NULL PRIMARY KEY,
		title VARCHAR(255),
		body TEXT,
		created_at DATE,
		updated_at DATE
);

CREATE SEQUENCE seq_authors;
CREATE TABLE authors(
   	id INTEGER DEFAULT NEXTVAL('seq_authors') NOT NULL PRIMARY KEY,
		name VARCHAR(255),
		email VARCHAR(255),
		created_at DATE,
		updated_at DATE
);

CREATE SEQUENCE seq_article_authors;
CREATE TABLE article_authors(
	id INTEGER DEFAULT NEXTVAL('seq_article_authors') NOT NULL PRIMARY KEY,
	article_id INTEGER NOT NULL,
	author_id INTEGER NOT NULL,
	rank INTEGER DEFAULT 999 NOT NULL,
	CONSTRAINT fk_article_authors_articles FOREIGN KEY (article_id) REFERENCES articles ON DELETE CASCADE,
	CONSTRAINT fk_author_authors_authors FOREIGN KEY (author_id) REFERENCES authors ON DELETE CASCADE
);
