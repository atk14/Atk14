CREATE SEQUENCE seq_sessions;
CREATE TABLE sessions(
        id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_sessions'),
        security VARCHAR(255) NOT NULL,
        session_name VARCHAR(255) NOT NULL,
        --
        remote_addr VARCHAR(255) NOT NULL,
        --
        created TIMESTAMP DEFAULT NOW() NOT NULL,
        last_access TIMESTAMP DEFAULT NOW() NOT NULL
);
CREATE INDEX in_sessions_lastaccess ON sessions (last_access);

CREATE SEQUENCE seq_session_values;
CREATE TABLE session_values(
        id INT NOT NULL PRIMARY KEY DEFAULT NEXTVAL('seq_session_values'),
        session_id INT NOT NULL,
        section VARCHAR(255) NOT NULL,
        --
        key VARCHAR(255) NOT NULL,
        value TEXT,
        expiration TIMESTAMP,
        --
        CONSTRAINT unq_sessionvalues UNIQUE(session_id,section,key),
        CONSTRAINT fk_sessionvalues_sessions FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);
CREATE INDEX in_sessionvalues_sessionid ON session_values(session_id);
CREATE INDEX in_sessionvalues_expiration ON session_values(expiration);
