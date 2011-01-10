-- just a sample table for demonstrating ATK14 functions (see CreaturesController)
-- you can `DROP TALE creatures` in your future migration
CREATE SEQUENCE seq_creatures;
CREATE TABLE creatures(
	id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_creatures'),
	name VARCHAR(255),
	description TEXT,
	image_url VARCHAR(255)
);
