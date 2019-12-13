-- Database presets
PRAGMA encoding = "UTF-8";	-- Default encoding set to UTF-8
PRAGMA auto_vacuum = "2";	-- File size improvement
PRAGMA temp_store = "2";	-- Memory temp storage for performance
PRAGMA journal_mode = "WAL";	-- Performance improvement
PRAGMA secure_delete = "1";	-- Privacy improvement
PRAGMA foreign_keys = "1";	-- Enable foreign key relationships

-- Content and access filters
CREATE TABLE filters(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	term TEXT NOT NULL COLLATE NOCASE,
	label TEXT NOT NULL COLLATE NOCASE,
	-- Filter action
	response INTEGER NOT NULL DEFAULT 0,
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	-- abs( strotime( $var ) - time() )
	duration INTEGER DEFAULT 0
);-- --
CREATE UNIQUE INDEX idx_filter_term ON filters( term, label );-- --
CREATE INDEX idx_filter_label ON filters( label );-- --

-- Expanded filter data. E.G IP block expanded to every IP for direct match
CREATE TABLE filter_data(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	filter_id INTEGER NOT NULL REFERENCES filters( id )
		ON DELETE CASCADE,
	
	term TEXT NOT NULL COLLATE NOCASE
);
CREATE UNIQUE INDEX idx_filter_data ON filter_data( term ASC );-- --


-- Filter searching
CREATE VIRTUAL TABLE filter_search 
	USING fts4( body, tokenize = unicode61 );-- --


-- Filter search insert
CREATE TRIGGER filter_insert AFTER INSERT ON filters FOR EACH ROW
BEGIN
	INSERT INTO filter_search ( docid, body ) 
		VALUES ( NEW.id, NEW.term );
END;-- --


-- Filter search update
CREATE TRIGGER filter_update AFTER UPDATE ON filters FOR EACH ROW
BEGIN
	UPDATE filter_search SET body = NEW.term WHERE docid = NEW.id;
END;-- --


