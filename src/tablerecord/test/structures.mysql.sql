-- mysql

-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'localhost' IDENTIFIED BY 'test';
DROP TABLE IF EXISTS test_table;
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

-- TODO: there are more tables in testing_structures.postgresql.sql
