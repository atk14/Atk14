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
    create_time timestamp without time zone DEFAULT now()
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

-- mysql

-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'localhost' IDENTIFIED BY 'test';
CREATE TABLE test_table (
    id INT(11) PRIMARY KEY auto_increment,
    title char(255),
    znak char(1),
    an_integer INT(11),
    price decimal(20,2),
    cena decimal(12,2),
    cena2 float,
    text text,
    perex text,
    binary_data binary,
    binary_data2 binary,
    create_date date,
    create_time timestamp DEFAULT now()
);


-- oracle

CREATE SEQUENCE test_table_id_seq START WITH 1 INCREMENT BY 1 ORDER NOMAXVALUE;
CREATE TABLE test_table(
  id NUMBER(20,0) NOT NULL,
  title VARCHAR2(255),
  an_integer INTEGER,
  price NUMBER(20,2),
  text CLOB,
  perex CLOB,
  binary_data BLOB,
  binary_data2 BLOB,
  create_date DATE,
  create_time DATE DEFAULT SYSDATE,
  CONSTRAINT pk_test_table_id PRIMARY KEY (id) USING INDEX TABLESPACE tbs_eol_indexes
)TABLESPACE tbs_eol_common;

CREATE OR REPLACE TRIGGER test_table_id_trg
 BEFORE INSERT
 ON test_table
 FOR EACH ROW
BEGIN
    IF :NEW.id IS NULL THEN
       SELECT test_table_id_seq.nextval
       INTO   :NEW.id
       FROM   dual;
    END IF;
END;
/
