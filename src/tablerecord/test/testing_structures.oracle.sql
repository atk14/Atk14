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

-- TODO: there are more tables in testing_structures.postgresql.sql
