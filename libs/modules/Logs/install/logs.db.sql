
CREATE TABLE logfiles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title TEXT NOT NULL COLLATE NOCASE,
	appname TEXT NOT NULL COLLATE NOCASE,
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE UNIQUE INDEX idx_log_title ON logfiles ( title );-- --

CREATE TRIGGER logfile_update AFTER UPDATE ON logfiles FOR EACH ROW
BEGIN
	UPDATE logfiles SET updated = CURRENT_TIMESTAMP
		WHERE id = NEW.id;
END;-- --

CREATE TABLE logfields(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	file_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	description TEXT DEFAULT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_log_file
		FOREIGN KEY ( file_id ) 
		REFERENCES logfiles ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_log_label ON logfields ( file_id, label );-- --
CREATE INDEX idx_log_sort ON logfields( sort_order ASC );-- --

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
	f.file_id AS file_id,
	f.description AS description,
	f.sort_order AS sort_order
	
	FROM logdata d
	LEFT JOIN logfields f ON d.label_id = f.id;-- --

CREATE TRIGGER log_content_insert INSTEAD OF INSERT ON log_content_view 
WHEN NEW.is_fulltext IS NOT 1
BEGIN
	INSERT INTO logdata ( label_id, content ) 
	VALUES ( NEW.label_id, NEW.content );
	
END;-- --

CREATE TRIGGER log_content_search_insert INSTEAD OF INSERT ON log_content_view 
WHEN NEW.is_fulltext IS 1
BEGIN
	INSERT INTO logdata ( label_id ) VALUES ( NEW.label_id );
	INSERT INTO log_content_insert( docid, body ) 
		VALUES( ( SELECT last_insert_rowid() ), NEW.content );
END;


