
-- Database presets
PRAGMA trusted_schema = OFF;	-- Preemptive defense
PRAGMA cell_size_check = ON;	-- Integrity check
PRAGMA encoding = "UTF-8";	-- Default encoding set to UTF-8
PRAGMA page_size = "16384";	-- Blob performance improvement
PRAGMA auto_vacuum = "2";	-- File size improvement
PRAGMA temp_store = "2";	-- Memory temp storage for performance
PRAGMA journal_mode = "WAL";	-- Performance improvement
PRAGMA secure_delete = "1";	-- Privacy improvement

CREATE TABLE firewall (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	reason TEXT NOT NULL,
	ip TEXT NOT NULL, 
	ua TEXT NOT NULL, 
	uri TEXT NOT NULL, 
	method TEXT NOT NULL, 
	headers TEXT NOT NULL, 
	expires DATETIME DEFAULT NULL,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE INDEX idx_firewall_on_reason ON firewall ( reason );-- --
CREATE INDEX idx_firewall_on_ip ON firewall ( ip ASC );-- --
CREATE INDEX idx_firewall_on_ua ON firewall ( ua ASC );-- --
CREATE INDEX idx_firewall_on_uri ON firewall ( uri ASC );-- --
CREATE INDEX idx_firewall_on_method ON firewall ( method ASC );-- --
CREATE INDEX idx_firewall_on_expires ON firewall ( expires DESC );-- --
CREATE INDEX idx_firewall_on_created ON firewall ( created ASC );-- --


CREATE TRIGGER firewall_exp_insert AFTER INSERT ON firewall FOR EACH ROW 
WHEN NEW.expires IS NULL 
BEGIN
	UPDATE firewall SET 
		expires = datetime( 
			( strftime( '%s','now' ) + 604800 ), 
			'unixepoch'
		) WHERE rowid = NEW.rowid;
END;-- --


CREATE TRIGGER firewall_insert AFTER INSERT ON firewall FOR EACH ROW 
BEGIN
	DELETE FROM firewall WHERE 
		strftime( '%s', expires ) < 
		strftime( '%s', updated );
END;

