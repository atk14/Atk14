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
