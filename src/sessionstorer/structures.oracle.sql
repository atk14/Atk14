CREATE SEQUENCE seq_sessions START WITH 1 INCREMENT BY 1 ORDER NOMAXVALUE;
CREATE TABLE sessions(
	id NUMBER(20,0) NOT NULL,
	security VARCHAR2(255) NOT NULL,
	session_name VARCHAR2(255) NOT NULL,
	--
	remote_addr VARCHAR2(255) NOT NULL,
	--
	created DATE DEFAULT SYSDATE NOT NULL,
	last_access DATE DEFAULT SYSDATE NOT NULL,
	--
	CONSTRAINT pk_sessions_id PRIMARY KEY (id) USING INDEX TABLESPACE index_tablespace
) TABLESPACE default_tablespace;
CREATE INDEX in_sessions_lastaccess ON sessions (last_access) TABLESPACE index_tablespace;

CREATE SEQUENCE seq_session_values START WITH 1 INCREMENT BY 1 ORDER NOMAXVALUE;
CREATE TABLE session_values(
	id NUMBER(20,0) NOT NULL,
	session_id INT NOT NULL,
	section VARCHAR2(255) NOT NULL,
	--
	key VARCHAR2(255) NOT NULL,
	value CLOB,
	expiration DATE,
	--
	CONSTRAINT pk_sessionvalues_id PRIMARY KEY (id) USING INDEX TABLESPACE index_tablespace,
	CONSTRAINT unq_sessionvalues UNIQUE(session_id,section,key) USING INDEX TABLESPACE index_tablespace,
	CONSTRAINT fk_sessionvalues_sessions FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) TABLESPACE default_tablespace;
CREATE INDEX in_sessionvalues_sessionid ON session_values(session_id) TABLESPACE index_tablespace;
CREATE INDEX in_sessionvalues_expiration ON session_values(expiration) TABLESPACE index_tablespace;

