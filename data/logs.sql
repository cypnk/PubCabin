
-- Database presets
PRAGMA trusted_schema = OFF;	-- Preemptive defense
PRAGMA cell_size_check = ON;	-- Integrity check
PRAGMA encoding = "UTF-8";	-- Default encoding set to UTF-8
PRAGMA page_size = "16384";	-- Blob performance improvement
PRAGMA auto_vacuum = "2";	-- File size improvement
PRAGMA temp_store = "2";	-- Memory temp storage for performance
PRAGMA journal_mode = "WAL";	-- Performance improvement
PRAGMA secure_delete = "1";	-- Privacy improvement


CREATE TABLE logfields(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	description TEXT DEFAULT NULL COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_log_label ON logfields ( label );-- --

CREATE TABLE logdata(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label_id INTEGER NOT NULL,
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	content DEFAULT NULL COLLATE NOCASE,
	is_fulltext INTEGER NOT NULL DEFAULT 1,
	
	CONSTRAINT fk_log_label
		FOREIGN KEY ( label_id ) 
		REFERENCES logfields ( id )
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_logdata_label ON logdata ( label_id );-- --
CREATE INDEX idx_logdata_fulltext ON logdata( is_fulltext );-- --
CREATE INDEX idx_logdata_content ON logdata( content ) 
	WHERE content IS NOT NULL;-- --

CREATE VIRTUAL TABLE log_content_search 
	USING fts4( body, tokenize=unicode61 );-- --

CREATE VIEW log_content_view AS SELECT
	d.id AS id, 
	d.label_id AS meta_id, 
	d.content AS content, 
	d.is_fulltext AS is_fulltext,
	f.label AS label,
	f.description AS description
	
	FROM logdata d
	LEFT JOIN logfields f ON d.label_id = f.id;-- --

CREATE TRIGGER log_content_insert INSTEAD OF INSERT ON log_content_view 
WHEN NEW.is_fulltext IS NOT 1
BEGIN
	INSERT INTO logdata ( label_id, content ) 
	VALUES ( NEW.label_id, NEW.content );
	
END;-- --

CREATE TRIGGER log_content_insert INSTEAD OF INSERT ON log_content_view 
WHEN NEW.is_fulltext IS 1
BEGIN
	INSERT INTO logdata ( label_id ) VALUES ( NEW.label_id );
	INSERT INTO log_content_insert( docid, body ) 
		VALUES( SELECT last_insert_rowid(), NEW.content );
END;-- --


