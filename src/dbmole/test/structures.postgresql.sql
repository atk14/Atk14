-- $ cat testing_structure_postgresql.sql | psql -U test test 
DROP TABLE IF EXISTS test_table;
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
