-- postgresql

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


-- mysql

-- CREATE DATABASE test CHARACTER SET 'UTF8';
-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'localhost' IDENTIFIED BY 'test';
CREATE TABLE test_table (
    id INT(11) PRIMARY KEY auto_increment,
    title char(255),
    znak char(1),
    an_integer INT(11),
    a_big_integer BIGINT,
    price decimal(20,2),
    cena decimal(12,2),
    cena2 float,
    text text,
    perex text,
    flag boolean,
    binary_data binary,
    binary_data2 binary,
    create_date date,
    create_time timestamp DEFAULT now()
);


-- oracle

CREATE SEQUENCE test_table_id_seq START WITH 1 INCREMENT BY 1 ORDER NOMAXVALUE;
CREATE TABLE test_table(
  id NUMBER(20,0) NOT NULL ,
  title VARCHAR2(255),
  an_integer INTEGER,
  a_big_integer NUMBER(20,0),
  price NUMBER(20,2),
  text CLOB,
  perex CLOB,
  binary_data BLOB,
  binary_data2 BLOB,
  flag CHAR(1),
  create_date DATE,
  create_time DATE DEFAULT SYSDATE,
  CONSTRAINT pk_test_table_id PRIMARY KEY (id)
);

-- autoincrement
CREATE OR REPLACE TRIGGER test_table_id_trg
 BEFORE INSERT
 ON test_table
 FOR EACH ROW
BEGIN
    IF :NEW.id IS NULL THEN
       SELECT test_table_id_seq.NEXTVAL
       INTO   :NEW.id
       FROM   dual;
    END IF;
END;
/
