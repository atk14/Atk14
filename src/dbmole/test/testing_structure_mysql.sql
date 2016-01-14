-- $ cat testing_structure_mysql.sql | mysql -utest test -p

-- CREATE DATABASE test CHARACTER SET 'UTF8';
-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'localhost' IDENTIFIED BY 'test';
-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'127.0.0.1' IDENTIFIED BY 'test';
-- GRANT ALL PRIVILEGES ON test.* TO 'test'@'::1' IDENTIFIED BY 'test';

DROP TABLE IF EXISTS test_table;
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
