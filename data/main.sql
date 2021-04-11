
-- Database presets
PRAGMA trusted_schema = OFF;	-- Preemptive defense
PRAGMA cell_size_check = ON;	-- Integrity check
PRAGMA encoding = "UTF-8";	-- Default encoding set to UTF-8
PRAGMA page_size = "16384";	-- Blob performance improvement
PRAGMA auto_vacuum = "2";	-- File size improvement
PRAGMA temp_store = "2";	-- Memory temp storage for performance
PRAGMA journal_mode = "WAL";	-- Performance improvement
PRAGMA secure_delete = "1";	-- Privacy improvement



-- Generate a random unique string
-- Usage:
-- SELECT id FROM rnd;
CREATE VIEW rnd AS 
SELECT lower( hex( randomblob( 16 ) ) ) AS id;-- --

-- GUID/UUID generator helper
-- Usage:
-- SELECT id FROM uuid;
CREATE VIEW uuid AS SELECT lower(
	hex( randomblob( 4 ) ) || '-' || 
	hex( randomblob( 2 ) ) || '-' || 
	'4' || substr( hex( randomblob( 2 ) ), 2 ) || '-' || 
	substr( 'AB89', 1 + ( abs( random() ) % 4 ) , 1 )  ||
	substr( hex( randomblob( 2 ) ), 2 ) || '-' || 
	hex( randomblob( 6 ) )
) AS id;-- --

-- Shared settings
CREATE TABLE settings(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	
	-- serialized JSON
	info TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_settings_label ON settings( label );-- --


-- Site data and content
CREATE TABLE sites (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE,
	
	-- Domain name
	basename TEXT NOT NULL,
	
	-- Relative path
	basepath TEXT NOT NULL, 
	
	-- Serialized JSON
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	is_active INTEGER NOT NULL DEFAULT 1,
	is_maintenance INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_site_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_site_path ON sites ( basename, basepath );-- --
CREATE UNIQUE INDEX idx_site_label ON sites ( label );-- --
CREATE INDEX idx_site_settings ON sites ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

-- Mirrored sites
CREATE TABLE site_aliases (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	site_id INTEGER NOT NULL,
	basename TEXT NOT NULL,
	
	CONSTRAINT fk_pages_site 
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_site_alias ON site_aliases ( site_id, basename );-- --


CREATE VIEW sites_enabled AS SELECT 
	s.id AS id, 
	s.label AS label, 
	s.basename AS basename, 
	s.basepath AS basepath, 
	s.is_active AS is_active,
	s.is_maintenance AS is_maintenance,
	s.settings AS settings_override, 
	a.basename AS base_alias,
	g.settings AS settings
	
	FROM sites s 
	LEFT JOIN settings g ON p.settings_id = g.id
	LEFT JOIN site_aliases a ON s.site_id = s.id;-- --

-- Localization
CREATE TABLE languages (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE,
	display TEXT NOT NULL COLLATE NOCASE,
	iso_code TEXT NOT NULL COLLATE NOCASE,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- CMS Default interface language
	is_default INTEGER NOT NULL DEFAULT 0,
	lang_group TEXT NOT NULL DEFAULT '' COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_lang_label ON languages ( label );-- --
CREATE UNIQUE INDEX idx_lang_iso ON languages ( iso_code );-- --
CREATE INDEX idx_lang_default ON languages ( is_default );-- --
CREATE INDEX idx_lang_sort ON languages ( sort_order );-- --
CREATE INDEX idx_lang_group ON languages ( lang_group );-- --

-- Unset previous default language if new default is set
CREATE TRIGGER language_default_insert BEFORE INSERT ON 
	languages FOR EACH ROW 
WHEN NEW.is_default <> 0 AND NEW.is_default IS NOT NULL
BEGIN
	UPDATE languages SET is_default = 0 
		WHERE is_default IS NOT 0;
END;-- --

CREATE TRIGGER language_default_update BEFORE UPDATE ON 
	languages FOR EACH ROW 
WHEN NEW.is_default <> 0 AND NEW.is_default IS NOT NULL
BEGIN
	UPDATE languages SET is_default = 0 
		WHERE is_default IS NOT 0 AND id IS NOT NEW.id;
END;-- --

CREATE TABLE translations (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	locale TEXT NOT NULL COLLATE NOCASE,
	lang_id INTEGER NOT NULL,
	
	definitions TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	
	-- Default locale for the language
	is_default INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_date_language
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_translation_local ON translations( locale );-- --
CREATE INDEX idx_translation_lang ON translations( lang_id );-- --
CREATE INDEX idx_translation_default ON translations( is_default );-- --

-- Unset any previous default language locales if new default is set
CREATE TRIGGER locale_default_insert BEFORE INSERT ON 
	translations FOR EACH ROW 
WHEN NEW.is_default <> 0 AND NEW.is_default IS NOT NULL
BEGIN
	UPDATE translations SET is_default = 0 
		WHERE is_default IS NOT 0 AND lang_id = NEW.lang_id;
END;-- --

CREATE TRIGGER locale_default_update BEFORE UPDATE ON 
	translations FOR EACH ROW 
WHEN NEW.is_default <> 0 AND NEW.is_default IS NOT NULL
BEGIN
	UPDATE translations SET is_default = 0 
		WHERE is_default IS NOT 0 AND id IS NOT NEW.id 
			AND lang_id = OLD.lang_id;
END;-- --

CREATE VIEW locale_view AS SELECT
	t.id AS id,
	l.label AS label,
	l.iso_code AS iso_code,
	l.is_default AS is_lang_default,
	l.display AS lang_display,
	l.lang_group AS lang_group,
	t.label AS locale,
	t.is_default AS is_locale_default,
	t.definitions AS definitions
	
	FROM translations t
	JOIN languages l ON t.lang_id = l.id;-- --

-- Localized date presentation formats
CREATE TABLE date_formats(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	lang_id INTEGER NOT NULL,
	locale_id INTEGER NOT NULL,
	
	 -- Excluding pipe ( | )
	render TEXT NOT NULL COLLATE NOCASE,
		
	CONSTRAINT fk_date_language
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id )
		ON DELETE CASCADE,
	
	CONSTRAINT fk_date_locale
		FOREIGN KEY ( locale_id ) 
		REFERENCES translations ( id )
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_date_lang ON date_formats( lang_id );-- --
CREATE INDEX idx_date_locals ON date_formats( locale_id );-- --


-- User profiles
CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	uuid TEXT DEFAULT NULL COLLATE NOCASE,
	username TEXT NOT NULL COLLATE NOCASE,
	password TEXT NOT NULL,
	display TEXT DEFAULT NULL COLLATE NOCASE,
	bio TEXT DEFAULT NULL COLLATE NOCASE,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_user_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_username ON users( username );-- --
CREATE UNIQUE INDEX idx_user_uuid ON users( uuid )
	WHERE uuid IS NOT NULL;-- --
CREATE INDEX idx_user_settings ON users ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

-- User searching
CREATE VIRTUAL TABLE user_search 
	USING fts4( username, tokenize=unicode61 );-- --


-- Cookie based login tokens
CREATE TABLE logins(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	lookup TEXT NOT NULL NULL COLLATE NOCASE,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	hash TEXT DEFAULT NULL,
	
	CONSTRAINT fk_logins_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_login_user ON logins( user_id );-- --
CREATE UNIQUE INDEX idx_login_lookup ON logins( lookup );-- --
CREATE INDEX idx_login_hash ON logins( hash )
	WHERE hash IS NOT NULL;-- --


-- Secondary identity providers E.G. two-factor
CREATE TABLE id_providers( 
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL NULL COLLATE NOCASE,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- Serialized JSON
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_provider_label ON id_providers( label );-- --
CREATE INDEX idx_provider_sort ON id_providers( sort_order ASC );-- --


-- User authentication and activity metadata
CREATE TABLE user_auth(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	provider_id INTEGER DEFAULT NULL,
	email TEXT DEFAULT NULL COLLATE NOCASE,
	mobile_pin TEXT DEFAULT NULL COLLATE NOCASE,
	info TEXT DEFAULT NULL,
	
	-- Activity
	last_ip TEXT DEFAULT NULL COLLATE NOCASE,
	last_active DATETIME DEFAULT NULL,
	last_login DATETIME DEFAULT NULL,
	last_pass_change DATETIME DEFAULT NULL,
	last_lockout DATETIME DEFAULT NULL,
	
	-- Auth status,
	is_approved INTEGER NOT NULL DEFAULT 0,
	is_locked INTEGER NOT NULL DEFAULT 0,
	
	-- Authentication tries
	failed_attempts INTEGER NOT NULL DEFAULT 0,
	failed_last_start DATETIME DEFAULT NULL,
	failed_last_attempt DATETIME DEFAULT NULL,
	
	CONSTRAINT fk_auth_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE, 
		
	CONSTRAINT fk_auth_provider
		FOREIGN KEY ( provider_id ) 
		REFERENCES providers ( id )
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_user_email ON user_auth( email );-- --
CREATE INDEX idx_user_auth_user ON user_auth( user_id );-- --
CREATE INDEX idx_user_provider ON user_auth( provider_id )
	WHERE provider_id IS NOT NULL;-- --
CREATE INDEX idx_user_pin ON user_auth( mobile_pin ) 
	WHERE mobile_pin IS NOT NULL;-- --
CREATE INDEX idx_user_ip ON user_auth( last_ip )
	WHERE last_ip IS NOT NULL;-- --
CREATE INDEX idx_user_active ON user_auth( last_active )
	WHERE last_active IS NOT NULL;-- --
CREATE INDEX idx_user_login ON user_auth( last_login )
	WHERE last_login IS NOT NULL;-- --
CREATE INDEX idx_user_failed_last ON user_auth( failed_last_attempt )
	WHERE failed_last_attempt IS NOT NULL;-- --


-- User auth last activity
CREATE VIEW auth_activity AS 
SELECT user_id, 
	provider_id,
	is_approved,
	is_locked,
	last_ip
	last_active,
	last_login,
	last_lockout,
	failed_attempts,
	failed_last_start,
	failed_last_attempt
	
	FROM user_auth;-- --


-- Auth activity helpers
CREATE TRIGGER user_last_login INSTEAD OF 
	UPDATE OF last_login ON auth_activity
BEGIN 
	UPDATE user_auth SET 
		last_ip		= NEW.last_ip,
		last_login	= CURRENT_TIMESTAMP, 
		last_active	= CURRENT_TIMESTAMP
		WHERE id	= OLD.id;
END;-- --

CREATE TRIGGER user_last_ip INSTEAD OF 
	UPDATE OF last_ip ON auth_activity
BEGIN 
	UPDATE user_auth SET 
		last_ip		= NEW.last_ip, 
		last_active	= CURRENT_TIMESTAMP 
		WHERE id	= OLD.id;
END;-- --

CREATE TRIGGER user_last_active INSTEAD OF 
	UPDATE OF last_active ON auth_activity
BEGIN 
	UPDATE user_auth SET last_active = CURRENT_TIMESTAMP
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_last_lockout INSTEAD OF 
	UPDATE OF last_lockout ON auth_activity
BEGIN 
	UPDATE user_auth SET last_lockout = CURRENT_TIMESTAMP 
		WHERE id = OLD.id;
END;-- --

CREATE TRIGGER user_failed_last_attempt INSTEAD OF 
	UPDATE OF failed_last_attempt ON auth_activity
BEGIN 
	UPDATE user_auth SET failed_last_attempt = CURRENT_TIMESTAMP, 
		failed_attempts = ( failed_attempts + 1 ) 
		WHERE id = OLD.id;
	
	-- Update current start window if it's been 24 hours since 
	-- last window
	UPDATE user_auth SET failed_last_start = CURRENT_TIMESTAMP 
		WHERE id = OLD.id AND ( 
		failed_last_start IS NULL OR ( 
		strftime( '%s', 'now' ) - 
		strftime( '%s', 'failed_last_start' ) ) > 86400 );
END;-- --



-- Login view
-- Usage:
-- SELECT * FROM login_view WHERE lookup = :lookup;
CREATE VIEW login_view AS 
SELECT 
	user_id AS id, 
	uuid AS uuid, 
	logins.lookup AS lookup, 
	logins.hash AS hash, 
	users.created AS created, 
	users.status AS status, 
	users.username AS name
	
	FROM logins
	JOIN users ON logins.user_id = users.id;-- --

-- Password login view
-- Usage:
-- SELECT * FROM login_pass WHERE username = :username;
CREATE VIEW login_pass AS 
SELECT id, uuid, username AS name, lookup, password, status 
	FROM users;-- --


-- Login regenerate. Not intended for SELECT
-- Usage:
-- UPDATE logout_view SET lookup = '' WHERE user_id = :user_id;
CREATE VIEW logout_view AS 
SELECT user_id, lookup FROM logins;-- --

-- Reset the lookup string to force logout a user
CREATE TRIGGER user_logout INSTEAD OF UPDATE OF lookup ON logout_view
BEGIN
	UPDATE logins SET lookup = ( SELECT id FROM rnd ), 
		updated = CURRENT_TIMESTAMP
		WHERE user_id = NEW.user_id;
END;-- --

-- New user, generate UUID, insert user search and create login lookups
CREATE TRIGGER user_insert AFTER INSERT ON users FOR EACH ROW 
BEGIN
	-- Create search data
	INSERT INTO user_search( docid, username ) 
		VALUES ( NEW.id, NEW.username );
	
	-- New login lookup
	INSERT INTO logins( user_id, lookup )
		VALUES( NEW.id, ( SELECT id FROM rnd ) );
	
	UPDATE users SET uuid = ( SELECT id FROM uuid )
		WHERE id = NEW.id;
END;-- --

-- Update last modified
CREATE TRIGGER user_update AFTER UPDATE ON users FOR EACH ROW
BEGIN
	UPDATE users SET updated = CURRENT_TIMESTAMP 
		WHERE id = OLD.id;
	
	UPDATE user_search 
		SET username = NEW.username || ' ' || NEW.display
		WHERE docid = OLD.id;
END;-- --


-- Delete user search data following user delete
CREATE TRIGGER user_delete BEFORE DELETE ON users FOR EACH ROW 
BEGIN
	DELETE FROM user_search WHERE rowid = OLD.rowid;
END;-- --



-- User roles
CREATE TABLE roles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	description TEXT DEFAULT NULL COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_role_label ON roles( label ASC );-- --

CREATE TABLE role_privileges(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	role_id INTEGER NOT NULL,
	
	-- Serialized JSON
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_privilege_role 
		FOREIGN KEY ( role_id ) 
		REFERENCES roles ( id )
		ON DELETE CASCADE, 
	
	CONSTRAINT fk_role_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_privilege_role ON role_privileges( role_id );-- --
CREATE INDEX idx_privilege_settings ON role_privileges ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE TABLE user_roles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	role_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	
	CONSTRAINT fk_user_roles_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id )
		ON DELETE CASCADE,
	
	CONSTRAINT fk_user_roles_role 
		FOREIGN KEY ( role_id ) 
		REFERENCES roles ( id )
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_user_role ON 
	user_roles( role_id, user_id );-- --


-- Template style groups
CREATE TABLE styles(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE,
	description TEXT NOT NULL COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_styles_label ON styles ( label );-- --

-- HTML render templates
CREATE TABLE style_templates(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE, 
	render TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	style_id INTEGER NOT NULL, 
	
	CONSTRAINT fk_render_style
		FOREIGN KEY ( style_id ) 
		REFERENCES styles ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_template ON style_templates ( style_id, label );-- --
CREATE INDEX idx_template_style ON style_templates ( style_id );-- --
CREATE INDEX idx_template_label ON style_templates ( label );-- --


-- Site content regions/paged collections
CREATE TABLE areas (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE,
	site_id INTEGER NOT NULL,
	
	permissions TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_area_site 
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id )
		ON DELETE CASCADE,
	
	CONSTRAINT fk_area_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_area_label ON areas ( label );-- --
CREATE INDEX idx_area_settings ON areas ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

-- Area render regions
CREATE TABLE area_render (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	area_id INTEGER NOT NULL,
	style_id INTEGER DEFAULT NULL,
	
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_area_render
		FOREIGN KEY ( area_id ) 
		REFERENCES areas ( id )
		ON DELETE CASCADE,
	
	CONSTRAINT fk_area_render_style
		FOREIGN KEY ( style_id ) 
		REFERENCES styles ( id )
		ON DELETE SET NULL,
	
	CONSTRAINT fk_area_render_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_area_render_style ON area_render ( style_id )
	WHERE style_id IS NOT NULL;-- --
CREATE INDEX idx_area_render_settings ON area_render ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE VIEW area_view AS SELECT
	a.id AS id,
	a.label AS label,
	a.site_id AS site_id,
	a.permissions AS permissions, 
	a.settings AS settings_override,
	ag.settings AS settings,
	GROUP_CONCAT( st.label, '|' ) AS templates,
	GROUP_CONCAT( st.render, '|' ) AS template_render,
	ar.status AS status,
	ar.settings AS render_settings_override,
	rg.settings AS render_settings
	
	FROM area_render ar
	JOIN areas a ON ar.area_id = a.id 
	LEFT JOIN styles sy ON ar.style_id = sy.id 
	LEFT JOIN style_templates st ON sy.id = st.style_id 
	LEFT JOIN settings ag ON a.settings_id = ag.id
	LEFT JOIN settings rg ON ar.settings_id = rg.id;-- --

CREATE TABLE pages (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	uuid TEXT DEFAULT NULL,
	site_id INTEGER NOT NULL,
	ptype TEXT NOT NULL COLLATE NOCASE,
	
	-- Template override
	render TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	parent_id INTEGER DEFAULT NULL,
	is_home INTEGER NOT NULL DEFAULT 0,
	allow_children INTEGER NOT NULL DEFAULT 0,
	allow_comments INTEGER NOT NULL DEFAULT 0,
	sort_order INTEGER NOT NULL DEFAULT 0,
	child_count INTEGER NOT NULL DEFAULT 0,
	comment_count INTEGER NOT NULL DEFAULT 0,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	published DATETIME DEFAULT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_pages_site 
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id )
		ON DELETE RESTRICT,
		
	CONSTRAINT fk_pages_parent 
		FOREIGN KEY ( parent_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_page_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE UNIQUE INDEX idx_page_uuid ON pages ( uuid )
	WHERE uuid IS NOT NULL;-- --
CREATE INDEX idx_page_parent ON pages ( parent_id );-- --
CREATE INDEX idx_page_site ON pages ( site_id );-- --
CREATE INDEX idx_page_ptype ON pages ( ptype );-- --
CREATE INDEX idx_page_home ON pages ( is_home );-- --
CREATE INDEX idx_page_type ON pages ( ptype );-- --
CREATE INDEX idx_page_sort ON pages ( sort_order );-- --
CREATE INDEX idx_page_created ON pages ( created );-- --
CREATE INDEX idx_page_updated ON pages ( updated );-- --
CREATE INDEX idx_page_status ON pages ( status );-- --
CREATE INDEX idx_page_published ON pages ( published )
	WHERE published IS NOT NULL;-- --
CREATE INDEX idx_page_settings ON pages ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

-- Page type templates
CREATE TABLE ptype_render (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	label TEXT NOT NULL COLLATE NOCASE,
	render TEXT NOT NULL DEFAULT '' COLLATE NOCASE
);-- --
CREATE INDEX idx_ptype_label ON ptype_render ( label );-- --

-- Unset previous default hompage
CREATE TRIGGER page_default_insert BEFORE INSERT ON 
	pages FOR EACH ROW 
WHEN NEW.is_home <> 0 OR NEW.is_home IS NOT NULL
BEGIN
	UPDATE pages SET is_home = 0 
		WHERE is_home IS NOT 0 
			AND site_id = NEW.site_id;
END;-- --

-- One homepage per site
CREATE TRIGGER page_default_update BEFORE UPDATE ON 
	pages FOR EACH ROW 
WHEN NEW.is_home <> 0 OR NEW.is_home IS NOT NULL
BEGIN
	UPDATE pages SET is_home = 0 
		WHERE is_home IS NOT 0 AND id IS NOT NEW.id 
			AND site_id = OLD.site_id;
END;-- --

-- Create page unique identifier
CREATE TRIGGER page_insert AFTER INSERT ON pages FOR EACH ROW
BEGIN
	UPDATE pages SET uuid = ( SELECT id FROM uuid )
		WHERE id = NEW.id;
END;-- --


-- If this is a child post
CREATE TRIGGER page_child_insert AFTER INSERT ON pages FOR EACH ROW
WHEN NEW.parent_id IS NOT NULL
BEGIN
	UPDATE pages SET child_count = ( child_count + 1 ) 
		WHERE id = NEW.parent_id;
END;-- --

-- Update child count statistics
CREATE TRIGGER page_child_delete BEFORE DELETE ON pages FOR EACH ROW
WHEN OLD.parent_id IS NOT NULL
BEGIN
	UPDATE pages SET child_count = ( child_count - 1 ) 
		WHERE id = OLD.parent_id;
END;-- --


-- URL Slug prefix paths
CREATE TABLE page_paths (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	url TEXT NOT NULL DEFAULT '/' COLLATE NOCASE
);-- --
CREATE UNIQUE INDEX idx_page_paths ON page_paths ( url ASC );-- --

-- Render clusters
CREATE TABLE page_area(
	page_id INTEGER NOT NULL, 
	area_id INTEGER NOT NULL, 
	
	CONSTRAINT fk_page_area_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_page_area_area 
		FOREIGN KEY ( area_id ) 
		REFERENCES areas ( id ) 
		ON DELETE RESTRICT
);-- --


-- Page content data
CREATE TABLE page_texts (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	page_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	path_id INTEGER NOT NULL,
	slug TEXT DEFAULT NULL COLLATE NOCASE,
	title TEXT NOT NULL COLLATE NOCASE,
	
	-- Exactly as entered
	body TEXT NOT NULL COLLATE NOCASE,
	
	-- HTML stripped
	bare TEXT NOT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_page_texts_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_texts_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE RESTRICT,
		
	CONSTRAINT fk_page_texts_path
		FOREIGN KEY ( path_id ) 
		REFERENCES page_paths ( id ) 
		ON DELETE RESTRICT
);-- --

-- Each language represented once per page text
CREATE UNIQUE INDEX idx_page_text ON 
	page_texts ( page_id, lang_id );-- --

-- Page with this slug can only occur once per path
CREATE UNIQUE INDEX idx_page_slug ON page_texts ( page_id, path_id, slug )
	WHERE slug IS NOT NULL;-- --

-- Generate a random slug if empty
CREATE TRIGGER page_texts_insert_slug AFTER INSERT ON 
	page_texts FOR EACH ROW
WHEN NEW.slug = '' OR NEW.slug IS NULL
BEGIN
	UPDATE page_texts SET slug = ( SELECT id FROM uuid ) 
		WHERE id = NEW.id;
END;-- --

CREATE TRIGGER page_texts_update_slug AFTER UPDATE ON 
	page_texts FOR EACH ROW
WHEN NEW.slug = '' OR NEW.slug IS NULL
BEGIN
	UPDATE page_texts SET slug = ( SELECT id FROM uuid ) 
		WHERE id = NEW.id;
END;-- --


-- Remote content
CREATE TABLE text_sources (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	text_id INTEGER NOT NULL,
	url TEXT NOT NULL,
	new_auth TEXT NOT NULL,
	edit_auth TEXT NOT NULL, 
	ttl INTEGER NOT NULL DEFAULT 0, 
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		
	CONSTRAINT fk_page_texts
		FOREIGN KEY ( text_id ) 
		REFERENCES page_texts ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_text_source ON text_sources ( text_id );-- --
CREATE INDEX idx_term_source_url ON text_sources ( url );-- --
CREATE INDEX idx_term_source_ttl ON text_sources ( ttl );-- --
CREATE INDEX idx_term_source_created ON text_sources ( created );-- --
CREATE INDEX idx_term_source_updated ON text_sources ( updated );-- --

-- Content authorship
CREATE TABLE page_users(
	page_id INTEGER NOT NULL, 
	user_id INTEGER NOT NULL, 
	
	CONSTRAINT fk_page_users_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_page_users_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE RESTRICT
);-- --
CREATE UNIQUE INDEX idx_page_users ON 
	page_users( page_id, user_id );-- --

CREATE TABLE page_text_users(
	text_id INTEGER NOT NULL, 
	user_id INTEGER NOT NULL, 
	
	CONSTRAINT fk_page_texts_text 
		FOREIGN KEY ( text_id ) 
		REFERENCES page_texts ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_texts_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE RESTRICT
);-- --
CREATE UNIQUE INDEX idx_page_text_users ON 
	page_text_users( text_id, user_id );-- --

-- Page text revision history ( this should be append-only )
CREATE TABLE page_revisions (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	text_id INTEGER NOT NULL, 
	path_id INTEGER NOT NULL, 
	user_id INTEGER DEFAULT NULL, 
	author_name TEXT DEFAULT NULL COLLATE NOCASE,
	author_email TEXT DEFAULT NULL COLLATE NOCASE,
	author_ip TEXT DEFAULT NULL COLLATE NOCASE,
	title TEXT COLLATE NOCASE,
	body TEXT NULL COLLATE NOCASE,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	
	CONSTRAINT fk_page_revisions_text 
		FOREIGN KEY ( text_id ) 
		REFERENCES page_texts ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_texts_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE RESTRICT
);-- --
CREATE INDEX idx_page_revision_path ON page_revisions ( path_id );-- --
CREATE INDEX idx_page_revision_user ON page_revisions ( user_id )
	WHERE user_id IS NOT NULL;-- --
CREATE INDEX idx_page_revision_author_name ON page_revisions ( author_name ) 
	WHERE author_name IS NOT NULL;-- --
CREATE INDEX idx_page_revision_author_email ON page_revisions ( author_email ) 
	WHERE author_email IS NOT NULL;-- --
CREATE INDEX idx_page_revision_author_ip ON page_revisions ( author_ip ) 
	WHERE author_ip IS NOT NULL;-- --
CREATE INDEX idx_page_revision_text ON page_revisions ( text_id );-- --
CREATE INDEX idx_page_revision_created ON page_revisions ( created );-- --


CREATE VIEW page_area_view AS SELECT
	p.id AS id,
	p.uuid AS uuid,
	p.site_id AS site_id,
	p.is_home AS is_home,
	p.render AS render,
	p.ptype AS ptype,
	p.allow_children AS allow_children,
	p.allow_comments AS allow_comments,
	p.sort_order AS sort_order,
	p.child_count AS child_count,
	p.comment_count AS comment_count,
	p.created AS created,
	p.updated AS updated,
	p.published AS published,
	s.basename AS basename,
	s.basepath AS basepath,
	
	-- Timestamps
	strftime( '%Y-%m-%dT%H:%M:%SZ', 
		COALESCE( p.published, p.created ) 
	) AS date_utc, 
	strftime( '%Y-%m-%d', 
		COALESCE( p.published, p.created )
	) AS date_short, 
	
	-- Archive search
	strftime( '%Y', 
		COALESCE( p.published, p.created ) 
	) AS archive_y, 
	strftime( '%Y/%m', 
		COALESCE( p.published, p.created ) 
	) AS archive_ym, 
	strftime( '%Y/%m/%d', 
		COALESCE( p.published, p.created ) 
	) AS archive_ymd, 
	
	p.settings AS settings_override,
	g.settings AS settings,
	
	pa.area_id AS area_id,
	a.label AS area_label,
	a.permissions AS permissions,
	
	t.id AS text_id,
	t.title AS title,
	t.slug AS slug,
	pp.url AS url, 
	
	-- Permanent link
	( s.basename || s.basepath || COALESCE( pp.url, '/' ) || 
		strftime( '%Y/%m/%d/', 
			COALESCE( p.published, p.created ) ) || t.slug
	) AS permalink,
	
	( s.basename || s.basepath || COALESCE( pp.url, '/' ) || 
		t.slug ) AS baselink,
	
	-- Previously published sibling
	( SELECT id FROM pages prev
		WHERE prev.published IS NOT NULL AND
			strftime( '%s', prev.published ) < 
			strftime( '%s', 
				COALESCE( p.published, p.created ) 
			)
			ORDER BY prev.published DESC LIMIT 1 
	) AS prev_id, 
	
	-- Next published sibling
	( SELECT id FROM pages nxt 
		WHERE nxt.published IS NOT NULL AND 
			strftime( '%s', nxt.published ) > 
			strftime( '%s', 
				COALESCE( p.published, p.created ) 
			)
			ORDER BY nxt.published ASC LIMIT 1 
	) AS next_id
	
	FROM pages p
	LEFT JOIN page_texts t ON p.id = t.page_id
	LEFT JOIN page_paths pp ON t.path_id = pp.id
	LEFT JOIN page_area pa ON pa.page_id = p.id
	LEFT JOIN areas a ON pa.area_id = a.id
	LEFT JOIN sites s ON p.site_id = s.id
	LEFT JOIN settings g ON p.settings_id = g.id;-- --

CREATE VIEW page_text_view AS SELECT 
	t.id AS id,
	t.page_id AS page_id,
	t.lang_id AS lang_id,
	t.path_id AS path_id,
	t.title AS title,
	t.slug AS slug,
	t.body AS body,
	ts.url AS remote_url,
	ts.ttl AS remote_ttl,
	ts.created AS remote_created,
	ts.updated AS remote_updated,
	pp.url AS url
	
	FROM page_texts t
	LEFT JOIN page_paths pp ON t.path_id = pp.id
	LEFT JOIN text_sources ts ON t.id = ts.text_id;-- --

-- Edit history view
CREATE VIEW page_revision_view AS SELECT
	r.id AS id, 
	r.title AS title, 
	r.body AS body, 
	r.text_id AS text_id, 
	r.path_id AS path_id, 
	r.user_id AS user_id,
	r.created AS created,
	
	-- Authorship
	COALESCE( u.display, u.username, r.author_name, '.' ) AS author_name,
	COALESCE( e.email, r.author_email, '@' ) AS author_email,
	COALESCE( e.last_ip, r.author_ip, '::' ) AS author_ip,
	COALESCE( e.last_active, '0' ) AS author_last_active,
	COALESCE( e.last_login, '0' ) AS author_last_login,
	COALESCE( u.user_id, 0 ) AS author_id
	
	FROM page_revisions r
	LEFT JOIN users u ON r.user_id = u.id
	LEFT JOIN user_auth e ON e.user_id = u.id;-- --


-- Page text searching
CREATE VIRTUAL TABLE page_search 
	USING fts4( body, tokenize=unicode61 );-- --

CREATE TRIGGER page_text_insert AFTER INSERT ON page_texts FOR EACH ROW 
BEGIN
	INSERT INTO page_search( docid, body ) 
		VALUES ( NEW.id, NEW.title || ' ' || NEW.bare );
END;-- --

CREATE TRIGGER page_text_update AFTER UPDATE ON page_texts FOR EACH ROW 
BEGIN
	UPDATE page_search SET body = NEW.title || ' ' || NEW.bare 
		WHERE docid = NEW.id;
	
	UPDATE pages SET updated = CURRENT_TIMESTAMP 
		WHERE id = NEW.page_id;
END;-- --

CREATE TRIGGER page_text_delete BEFORE DELETE ON page_texts FOR EACH ROW 
BEGIN
	DELETE FROM page_text_search WHERE docid = OLD.id;
END;-- --

-- Content tagging and categorizing
CREATE TABLE terms (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	
	-- Any set of letter/number characters excluding "=", ",", "&"
	taxonomy TEXT NOT NULL COLLATE NOCASE,
	
	sort_order INTEGER NOT NULL DEFAULT 0,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE UNIQUE INDEX idx_term_taxo ON terms ( taxonomy );-- --
CREATE INDEX idx_term_created ON terms ( created );-- --
CREATE INDEX idx_term_updated ON terms ( updated );-- --
CREATE INDEX idx_term_sort ON terms ( sort_order );-- --

CREATE TABLE term_texts (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	term_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	
	-- Same character set as terms.taxonomy for slug, title, body
	slug TEXT NOT NULL COLLATE NOCASE,
	title TEXT COLLATE NOCASE,
	body TEXT NOT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_term_texts_term 
		FOREIGN KEY ( term_id ) 
		REFERENCES terms ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_term_texts_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --

-- Unique slug per taxonomy term
CREATE UNIQUE INDEX idx_term_text_slug ON 
	term_texts ( term_id, slug );-- --
CREATE INDEX idx_term_text_lang ON term_texts ( lang_id );-- --

-- Generate a random term text slug if empty
CREATE TRIGGER term_texts_insert_slug AFTER INSERT ON 
	term_texts FOR EACH ROW
WHEN slug = ''
BEGIN
	UPDATE term_texts SET slug = ( SELECT id FROM uuid ) 
		WHERE id = NEW.id;
END;-- --

CREATE TRIGGER term_texts_update_slug AFTER UPDATE ON 
	term_texts FOR EACH ROW
WHEN slug = ''
BEGIN
	UPDATE term_texts SET slug = ( SELECT id FROM uuid ) 
		WHERE id = NEW.id;
END;-- --


-- Page taxonomy terms
CREATE TABLE page_terms (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	page_id INTEGER NOT NULL,
	term_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_page_terms_term 
		FOREIGN KEY ( term_id ) 
		REFERENCES terms ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_terms_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE
);-- --

-- Unique term(s) per page
CREATE UNIQUE INDEX idx_page_term_slug ON 
	page_terms ( page_id, term_id );-- --
CREATE INDEX idx_page_term_sort ON page_terms ( sort_order );-- --

-- Page taxonomy
CREATE VIEW page_taxonomy_view AS SELECT
	pt.id AS id,
	pt.page_id AS page_id,
	pt.term_id AS term_id,
	terms.taxonomy AS term_label,
	pt.sort_order AS sort_order, 
	
	GROUP_CONCAT(
		'id='		|| texts.id		|| '&' || 
		'tid='		|| terms.id		|| '&' || 
		'label='	|| terms.taxonomy	|| '&' || 
		'term='		|| texts.body		|| '&' || 
		'title='	|| COALESCE( texts.title, '' ) || '&' || 
		'lang='		|| l.iso_code		|| '&' ||
		'slug='		|| texts.slug		|| '&' ||
		'sort='		|| pt.sort_order
	) AS taxonomy
	
	FROM page_terms pt
	JOIN terms ON terms.id = pt.term_id 
	LEFT JOIN term_texts texts ON texts.term_id = terms.id
	LEFT JOIN languages l ON l.id = texts.lang_id;-- --


-- Page feedback
CREATE TABLE comments (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	uuid TEXT DEFAULT NULL, 
	user_id INTEGER DEFAULT NULL, 
	author_name TEXT DEFAULT NULL COLLATE NOCASE,
	author_sign TEXT DEFAULT NULL COLLATE NOCASE,
	author_email TEXT DEFAULT NULL COLLATE NOCASE,
	author_url TEXT DEFAULT NULL COLLATE NOCASE,
	author_ip TEXT DEFAULT NULL COLLATE NOCASE,
	body TEXT NOT NULL COLLATE NOCASE,
	page_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	is_approved INTEGER NOT NULL DEFAULT 0,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	status INTEGER NOT NULL DEFAULT 0,
		
	CONSTRAINT fk_comments_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_comments_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_comment_uuid ON comments( uuid )
	WHERE uuid IS NOT NULL;-- --
CREATE INDEX idx_comment_user_id ON comments( user_id ) 
	WHERE user_id IS NOT NULL;-- --
CREATE INDEX idx_comment_author_name ON comments( author_name ) 
	WHERE author_name IS NOT NULL;-- --
CREATE INDEX idx_comment_author_email ON comments( author_email ) 
	WHERE author_email IS NOT NULL;-- --
CREATE INDEX idx_comment_author_ip ON comments( author_ip ) 
	WHERE author_ip IS NOT NULL;-- --
CREATE INDEX idx_comment_created ON comments ( created );-- --
CREATE INDEX idx_comment_updated ON comments ( updated );-- --

-- Comment searching
CREATE VIRTUAL TABLE comment_search 
	USING fts4( body, tokenize=unicode61 );-- --

CREATE TRIGGER comment_insert AFTER INSERT ON comments FOR EACH ROW 
BEGIN
	INSERT INTO comment_search( docid, body ) 
		VALUES ( NEW.id, NEW.bare );
	
	UPDATE pages SET comment_count = ( comment_count + 1 ), 
		uuid = ( SELECT id FROM uuid ) 
		WHERE id = NEW.page_id;
END;-- --

CREATE TRIGGER comment_update AFTER UPDATE ON comments FOR EACH ROW 
BEGIN
	UPDATE comment_search SET body = NEW.bare 
		WHERE docid = NEW.id;
END;-- --

CREATE TRIGGER comment_delete BEFORE DELETE ON comments FOR EACH ROW 
BEGIN
	DELETE FROM comment_search WHERE docid = OLD.id;
	UPDATE pages SET comment_count = ( comment_count - 1 )
		WHERE id = OLD.page_id;
END;-- --


-- Replies view
-- Usage:
-- SELECT * FROM comment_view WHERE page_id = :id
CREATE VIEW comment_view AS SELECT
	c.id AS id, 
	c.uuid AS uuid, 
	c.page_id AS page_id, 
	c.body AS body, 
	c.created AS created, 
	c.updated AS updated, 
	l.iso_code AS lang_iso,
	l.label AS lang_label,
	
	( strftime( '/%Y/%m/%d', c.created ) || 
		'/comment_' || c.id
	) AS id_link,
	
	-- Previous comment sibling
	( SELECT id FROM comments prev 
		WHERE strftime( '%s', prev.created ) < 
			strftime( '%s', c.created ) 
			AND prev.post_id = c.post_id 
			AND prev.is_approved > 0 
		ORDER BY prev.created DESC LIMIT 1 
	) AS prev_id, 
	
	-- Next comment sibling
	( SELECT id FROM comments nxt 
		WHERE strftime( '%s', nxt.created ) > 
			strftime( '%s', c.created ) 
			AND nxt.post_id = c.post_id
			AND nxt.is_approved > 0 
		ORDER BY nxt.created ASC LIMIT 1 
	) AS next_id,
	
	-- Authorship
	COALESCE( u.display, u.username, c.author_name, '.' ) AS author_name,
	COALESCE( e.email, c.author_email, '@' ) AS author_email,
	COALESCE( e.last_ip, c.author_ip, '::' ) AS author_ip,
	COALESCE( e.last_active, '0' ) AS author_last_active,
	COALESCE( e.last_login, '0' ) AS author_last_login,
	COALESCE( u.user_id, 0 ) AS author_id
	
	FROM comments c
	LEFT JOIN languages l ON c.lang_id = l.id
	LEFT JOIN users u ON c.user_id = u.id
	LEFT JOIN user_auth e ON e.user_id = u.id;-- --




-- Content uploads
CREATE TABLE attachments (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	
	 -- E.G. hash_file( 'algo', $src )
	hash TEXT NOT NULL,
	filename TEXT NOT NULL COLLATE NOCASE,
	
	-- Size in bytes
	filesize INTEGER NOT NULL DEFAULT 0,
	
	mime_type TEXT NOT NULL COLLATE NOCASE,
	meta TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	
	preview TEXT DEFAULT NULL COLLATE NOCASE, 
	sort_order INTEGER NOT NULL DEFAULT 0,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE UNIQUE INDEX idx_attach_file ON attachments ( filename );-- --
CREATE UNIQUE INDEX idx_attach_hash ON attachments ( hash );-- --
CREATE INDEX idx_attach_created ON attachments ( created );-- --
CREATE INDEX idx_attach_updated ON attachments ( updated );-- --
CREATE INDEX idx_attach_sort ON attachments ( sort_order );-- --

-- Attachment text searching
CREATE VIRTUAL TABLE attachment_search 
	USING fts4( body, tokenize=unicode61 );-- --

-- Upload descriptions
CREATE TABLE attachment_texts (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	attachment_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	body TEXT NOT NULL COLLATE NOCASE,
	bare TEXT NOT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_attachment_texts_attach 
		FOREIGN KEY ( attachment_id ) 
		REFERENCES attachments ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_attachment_texts_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_attachment_lang ON 
	attachment_texts ( attachment_id, lang_id );-- --

-- Page attachments
CREATE TABLE page_attachments (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	page_id INTEGER NOT NULL,
	attachment_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_page_attachments_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_attachments_attach 
		FOREIGN KEY ( attachment_id ) 
		REFERENCES attachments ( id ) 
		ON DELETE CASCADE
);-- --
-- Attachment listed once per page
CREATE UNIQUE INDEX idx_page_attachments ON 
	page_attachments ( page_id, attachment_id );-- --
CREATE INDEX idx_page_attach_sort ON 
	page_attachments ( sort_order );-- --


-- Comment attachments
CREATE TABLE comment_attachments (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	comment_id INTEGER NOT NULL,
	attachment_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_comment_attachments_comment 
		FOREIGN KEY ( comment_id ) 
		REFERENCES comments ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_attachments_attach 
		FOREIGN KEY ( attachment_id ) 
		REFERENCES attachments ( id ) 
		ON DELETE CASCADE
);-- --
-- Attachment listed once per comment
CREATE UNIQUE INDEX idx_comment_attachments ON 
	comment_attachments ( comment_id, attachment_id );-- --
CREATE INDEX idx_comment_attach_sort ON 
	comment_attachments ( sort_order );-- --


-- Taxonomy term upload attachments
CREATE TABLE term_attachments (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	term_id INTEGER NOT NULL,
	attachment_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_term_attachments_term 
		FOREIGN KEY ( term_id ) 
		REFERENCES terms ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_term_attachments_attach 
		FOREIGN KEY ( attachment_id ) 
		REFERENCES attachments ( id ) 
		ON DELETE CASCADE
);-- --
-- Attachment listed once per term
CREATE UNIQUE INDEX idx_term_attachments ON 
	term_attachments ( term_id, attachment_id );-- --
CREATE INDEX idx_term_attach_sort ON 
	term_attachments ( sort_order );-- --



CREATE TRIGGER attachment_text_insert AFTER INSERT ON 
	attachment_texts FOR EACH ROW 
BEGIN
	INSERT INTO attachment_search( docid, body ) 
		VALUES ( NEW.id, NEW.bare );
END;-- --

CREATE TRIGGER attachment_text_update AFTER UPDATE ON 
	attachment_texts FOR EACH ROW 
BEGIN
	UPDATE attachment_search SET body = NEW.bare 
		WHERE docid = NEW.id;
	
	UPDATE attachments SET updated = CURRENT_TIMESTAMP 
		WHERE id = NEW.attachment_id;
END;-- --

CREATE TRIGGER attachment_text_delete BEFORE DELETE ON 
	attachment_texts FOR EACH ROW 
BEGIN
	DELETE FROM attachment_search WHERE docid = OLD.id;
END;-- --


-- Content menues and navigation
CREATE TABLE menues(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	site_id INTEGER NOT NULL, 
	parent_id INTEGER DEFAULT NULL,
	url TEXT NOT NULL COLLATE NOCASE,
	
	 -- Serialized JSON
	permissions TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	
	CONSTRAINT fk_menu_site 
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_menu_parent 
		FOREIGN KEY ( parent_id ) 
		REFERENCES menues ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_menu_site ON menues( site_id );-- --
CREATE INDEX idx_menu_parent ON menues( parent_id );-- --
CREATE INDEX idx_menu_url ON menues( url );-- --

CREATE TABLE menu_texts (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	menu_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	body TEXT NOT NULL COLLATE NOCASE,
	bare TEXT NOT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_menue_texts_menu 
		FOREIGN KEY ( menu_id ) 
		REFERENCES menues ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_menu_texts_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_menu_lang ON 
	menu_texts ( menu_id, lang_id );-- --



-- Content locations
CREATE TABLE places(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	geo_lat REAL NOT NULL DEFAULT 0, 
	geo_lon REAL NOT NULL DEFAULT 0
);-- --
CREATE UNIQUE INDEX idx_places ON places( geo_lat, geo_lon );-- --
CREATE INDEX idx_place_settings ON places( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE TABLE place_labels(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	place_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	
	CONSTRAINT fk_place_label
		FOREIGN KEY ( place_id ) 
		REFERENCES places ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_place_label_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --

-- Place searching
CREATE VIRTUAL TABLE place_search 
	USING fts4( body, tokenize=unicode61 );-- --

-- Entry locations
CREATE TABLE page_places(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	page_id INTEGER NOT NULL,
	place_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_page_place_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_place
		FOREIGN KEY ( place_id ) 
		REFERENCES places ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_page_place_sort ON page_places( sort_order );-- --

CREATE VIEW page_place_view AS SELECT
	l.place_id AS place_id,
	l.page_id AS page_id,
	p.geo_lat AS geo_lat,
	p.geo_lon AS geo_lon,
	p.settings AS settings_override,
	g.settings AS settings
	
	FROM page_places l
	LEFT JOIN places p ON l.place_id = p.id
	LEFT JOIN settings g ON p.settings_id = g.id;-- --


CREATE TABLE comment_places(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	comment_id INTEGER NOT NULL,
	place_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	CONSTRAINT fk_comment_place_page 
		FOREIGN KEY ( comment_id ) 
		REFERENCES comments ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_comment_place
		FOREIGN KEY ( place_id ) 
		REFERENCES places ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_comment_place_sort ON comment_places( sort_order );-- --

CREATE VIEW comment_place_view AS SELECT
	l.place_id AS place_id,
	l.comment_id AS comment_id,
	p.geo_lat AS geo_lat,
	p.geo_lon AS geo_lon,
	p.settings AS settings_override,
	g.settings AS settings
	
	FROM comment_places l
	LEFT JOIN places p ON l.place_id = p.id
	LEFT JOIN settings g ON p.settings_id = g.id;-- --


CREATE TRIGGER place_label_insert AFTER INSERT ON place_labels FOR EACH ROW 
BEGIN
	INSERT INTO place_search( docid, body ) 
		VALUES ( NEW.id, NEW.label );
END;-- --

CREATE TRIGGER place_label_update AFTER UPDATE ON place_labels FOR EACH ROW 
BEGIN
	UPDATE place_search SET body = NEW.label 
		WHERE docid = NEW.id;
END;-- --

CREATE TRIGGER place_label_delete BEFORE DELETE ON place_labels FOR EACH ROW 
BEGIN
	DELETE FROM place_search WHERE docid = OLD.id;
END;-- --


-- Special actions
CREATE TABLE events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL
);-- --
CREATE UNIQUE INDEX idx_event_label ON events ( label );-- --

-- Callback triggers
CREATE TABLE event_triggers (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	callback TEXT NOT NULL
);-- -- 
CREATE UNIQUE INDEX idx_event_trigger ON event_triggers( callback );-- --

-- Global actions events
CREATE TABLE global_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- Serialized JSON parameters
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_global_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_global_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_global_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_global_event_sort ON global_events ( sort_order ASC );-- --
CREATE INDEX idx_global_event_event ON global_events( event_id );-- --
CREATE INDEX idx_global_event_trigger ON global_events( trigger_id );-- --
CREATE INDEX idx_global_event_settings ON global_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE VIEW global_event_view AS SELECT
	o.id AS id,
	o.event_id AS event_id, 
	o.sort_order AS sort_order,
	e.label AS event_label, 
	o.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	o.settings AS settings_override,
	g.settings AS settings
	
	FROM global_events o
	LEFT JOIN events e ON o.event_id = e.id
	LEFT JOIN triggers t ON o.trigger_id = t.id
	LEFT JOIN settings g ON o.settings_id = g.id;-- --

-- Site specific action events
CREATE TABLE site_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	site_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- Serialized JSON parameters
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_site_events_site 
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_site_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_site_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_site_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_site_event_sort ON site_events ( sort_order ASC );-- --
CREATE INDEX idx_site_event_site ON site_events( site_id );-- --
CREATE INDEX idx_site_event_event ON site_events( event_id );-- --
CREATE INDEX idx_site_event_trigger ON site_events( trigger_id );-- --
CREATE INDEX idx_site_event_settings ON site_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE VIEW site_event_view AS SELECT
	s.id AS site_id, 
	s.event_id AS event_id, 
	s.sort_order AS sort_order,
	e.label AS event_label, 
	s.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	s.settings AS settings_override,
	g.settings AS settings
	
	FROM site_events s
	LEFT JOIN events e ON s.event_id = e.id
	LEFT JOIN triggers t ON s.trigger_id = t.id
	LEFT JOIN settings g ON s.settings_id = g.id;-- --

-- User specific action events
CREATE TABLE user_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_user_events_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_user_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_user_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_user_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_user_event_sort ON user_events ( sort_order ASC );-- --
CREATE INDEX idx_user_event_user ON user_events( user_id );-- --
CREATE INDEX idx_user_event_event ON user_events( event_id );-- --
CREATE INDEX idx_user_event_trigger ON user_events( trigger_id );-- --
CREATE INDEX idx_user_event_settings ON user_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --


CREATE VIEW user_event_view AS SELECT
	u.id AS user_id, 
	u.event_id AS event_id, 
	u.sort_order AS sort_order,
	e.label AS event_label, 
	u.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	u.settings AS settings_override,
	g.settings AS settings
	
	FROM user_events u
	LEFT JOIN events e ON u.event_id = e.id
	LEFT JOIN triggers t ON u.trigger_id = t.id
	LEFT JOIN settings g ON u.settings_id = g.id;-- --

-- Page specific action events
CREATE TABLE page_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	page_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_page_events_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_page_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_page_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_page_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_page_event_sort ON page_events ( sort_order ASC );-- --
CREATE INDEX idx_page_event_page ON page_events( page_id );-- --
CREATE INDEX idx_page_event_event ON page_events( event_id );-- --
CREATE INDEX idx_page_event_trigger ON page_events( trigger_id );-- --
CREATE INDEX idx_page_event_settings ON page_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --


CREATE VIEW page_event_view AS SELECT
	p.id AS page_id, 
	p.event_id AS event_id, 
	p.sort_order AS sort_order,
	e.label AS event_label, 
	p.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	p.settings AS settings_override,
	g.settings AS settings
	
	FROM page_events p
	LEFT JOIN events e ON p.event_id = e.id
	LEFT JOIN triggers t ON p.trigger_id = t.id
	LEFT JOIN settings g ON p.settings_id = g.id;-- --

-- Comment specific action events
CREATE TABLE comment_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	comment_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_comment_events_comment
		FOREIGN KEY ( comment_id ) 
		REFERENCES comments ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_comments_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_comments_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_comments_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_comment_event_sort ON comment_events ( sort_order ASC );-- --
CREATE INDEX idx_comment_event_comment ON comment_events( comment_id );-- --
CREATE INDEX idx_comment_event_event ON comment_events( event_id );-- --
CREATE INDEX idx_comment_event_trigger ON comment_events( trigger_id );-- --
CREATE INDEX idx_comment_event_settings ON comment_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --


CREATE VIEW comment_event_view AS SELECT
	c.id AS comment_id, 
	c.event_id AS event_id, 
	c.sort_order AS sort_order,
	e.label AS event_label, 
	c.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	c.settings AS settings_override,
	g.settings AS settings
	
	FROM comment_events c
	LEFT JOIN events e ON c.event_id = e.id
	LEFT JOIN triggers t ON c.trigger_id = t.id
	LEFT JOIN settings g ON c.settings_id = g.id;-- --

-- Menu specific action events
CREATE TABLE menu_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	menu_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_menu_events_menu
		FOREIGN KEY ( menu_id ) 
		REFERENCES menues ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_menu_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_menu_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_menu_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_menu_event_sort ON menu_events ( sort_order ASC );-- --
CREATE INDEX idx_menu_event_event ON menu_events( event_id );-- --
CREATE INDEX idx_menu_event_trigger ON menu_events( trigger_id );-- --
CREATE INDEX idx_menu_event_settings ON menu_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --


CREATE VIEW menu_event_view AS SELECT
	m.id AS user_id, 
	m.event_id AS event_id, 
	m.sort_order AS sort_order,
	e.label AS event_label, 
	s.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	m.settings AS settings_override,
	g.settings AS settings
	
	FROM menu_events m
	LEFT JOIN events e ON m.event_id = e.id
	LEFT JOIN triggers t ON m.trigger_id = t.id
	LEFT JOIN settings g ON m.settings_id = g.id;-- --


-- Loadable modules
CREATE TABLE modules (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL,
	src TEXT NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);-- --
CREATE UNIQUE INDEX idx_module_label ON modules( label );-- --
CREATE UNIQUE INDEX idx_module_src ON modules( src );-- --
CREATE INDEX idx_module_sort ON modules( sort_order ASC );-- --

-- Special access
CREATE TABLE module_access(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	module_id INTEGER NOT NULL, 
	auth TEXT NOT NULL,
	reference TEXT NOT NULL DEFAULT '',
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	
	CONSTRAINT fk_module_src
		FOREIGN KEY ( module_id ) 
		REFERENCES modules ( id ) 
		ON DELETE CASCADE
);-- --

CREATE VIEW module_load AS SELECT 
	m.id AS id,
	m.label AS label,
	m.src AS src,
	m.sort_order AS sort_order,
	m.created AS created,
	ma.auth AS auth,
	ma.reference AS auth_reference,
	ma.created AS auth_created
	
	FROM modules m
	LEFT JOIN module_access ma ON m.id = ma.module_id;-- --



CREATE TABLE redirects (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	old_src TEXT NOT NULL,
	new_src TEXT NOT NULL,
	lang_id INTEGER NOT NULL,
		
	CONSTRAINT fk_redirect_lang
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_redirect ON redirects( old_src, new_src );-- --
CREATE UNIQUE INDEX idx_redirect_reverse ON redirects( new_src, old_src );-- --

-- Redirect specific action events
CREATE TABLE redirect_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	redirect_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_redirect_events_redirect 
		FOREIGN KEY ( redirect_id ) 
		REFERENCES redirects ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_redirect_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_redirect_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_redirect_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_redirect_event_sort ON redirect_events ( sort_order ASC );-- --
CREATE INDEX idx_redirect_event_event ON redirect_events( event_id );-- --
CREATE INDEX idx_redirect_event_trigger ON redirect_events( trigger_id );-- --
CREATE INDEX idx_redirect_event_settings ON redirect_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

CREATE VIEW redirect_event_view AS SELECT
	r.id AS user_id, 
	r.event_id AS event_id, 
	r.sort_order AS sort_order,
	e.label AS event_label, 
	s.trigger_id AS trigger_id, 
	GROUP_CONCAT( DISTINCT t.callback, ',' ) AS callback,
	r.settings AS settings_override,
	g.settings AS settings
	
	FROM redirect_events r
	LEFT JOIN events e ON r.event_id = e.id
	LEFT JOIN triggers t ON r.trigger_id = t.id
	LEFT JOIN settings g ON r.settings_id = g.id;-- --


-- Subject metadata fields
CREATE TABLE metadata (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	summary TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	sort_order INTEGER NOT NULL DEFAULT 0,
	lang_id INTEGER NOT NULL,
	
	-- E.G. int, bool, text, html etc...
	format TEXT NOT NULL DEFAULT 'text' COLLATE NOCASE,
	
	-- Full text searchable
	is_fulltext INTEGER NOT NULL DEFAULT 1,
	
	CONSTRAINT fk_meta_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_meta_label ON metadata( lang_id, label );-- --
CREATE INDEX idx_meta_sort ON metadata( sort_order );-- --
CREATE INDEX idx_meta_format ON metadata( format );-- --
CREATE INDEX idx_meta_lang ON metadata( lang_id );-- --
CREATE INDEX idx_meta_fulltext ON metadata( is_fulltext );-- --

-- Metadata content

-- Page meta
CREATE TABLE meta_content (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	meta_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	bare TEXT DEFAULT NULL COLLATE NOCASE,
	content TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	
	CONSTRAINT fk_meta_field
		FOREIGN KEY ( meta_id ) 
		REFERENCES metadata ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_meta_content_field ON meta_content( meta_id );-- --
CREATE INDEX idx_meta_content_sort ON meta_content( sort_order );-- --
CREATE INDEX idx_meta_content_created ON meta_content( created );-- --
CREATE INDEX idx_meta_content_updated ON meta_content( updated );-- --

-- Skip full text content and index only smaller values
CREATE INDEX idx_meta_content ON meta_content( content ) 
	WHERE bare IS NULL;-- --

-- Meta data search
CREATE VIRTUAL TABLE meta_content_search 
	USING fts4( body, tokenize=unicode61 );-- --

CREATE VIEW meta_content_view AS SELECT
	c.id AS id, 
	c.meta_id AS meta_id, 
	c.sort_order AS sort_order,
	c.bare AS bare, 
	c.content AS content, 
	m.label AS meta_label,
	m.format AS format,
	m.is_fulltext AS is_fulltext
	
	FROM meta_content c
	LEFT JOIN metadata m ON c.meta_id = m.id;-- --

-- Intercept page meta data insert
CREATE TRIGGER meta_content_insert INSTEAD OF INSERT ON meta_content_view 
WHEN is_fulltext IS NOT 1 
BEGIN
	INSERT INTO meta_content ( meta_id, bare, content, sort_order ) 
	VALUES ( 
		NEW.meta_id, 
		NULL, 
		NEW.content, 
		COALESCE( NEW.sort_order, 0 ) 
	);
END;-- --

-- Inercept meta data insert with full text
CREATE TRIGGER meta_content_search_insert INSTEAD OF INSERT ON meta_content_view
WHEN is_fulltext IS 1 
BEGIN
	INSERT INTO meta_content 
		( meta_id, sort_order, bare, content ) 
		VALUES ( 
			NEW.meta_id, 
			COALESCE( NEW.sort_order, 0 ), 
			COALESCE( NEW.bare, '' ), 
			NEW.content 
		);
	
	INSERT INTO meta_content_search( docid, body ) 
		VALUES ( ( SELECT last_insert_rowid() ), NEW.bare );
END;-- --

CREATE TRIGGER meta_content_search_update INSTEAD OF UPDATE ON meta_content_view
WHEN NEW.is_fulltext IS 1
BEGIN
	UPDATE meta_content_search SET body = NEW.bare 
		WHERE docid = NEW.id;
	
	UPDATE meta_content SET bare = NEW.bare, content = NEW.content, 
		sort_order = COALESCE( NEW.sort_order, 0 ), 
		updated = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;-- --

CREATE TRIGGER meta_content_update INSTEAD OF UPDATE ON meta_content_view
WHEN NEW.is_fulltext IS NOT 1
BEGIN
	UPDATE meta_content SET content = NEW.content, 
		sort_order = COALESCE( NEW.sort_order, 0 ), 
		updated = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;-- --

CREATE TRIGGER meta_content_search_delete BEFORE DELETE ON meta_content FOR EACH ROW 
BEGIN
	DELETE FROM meta_content_search WHERE docid = OLD.id;
END;-- --


-- Site metadata
CREATE TABLE site_meta (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	metacontent_id INTEGER NOT NULL,
	site_id INTEGER NOT NULL,
	
	CONSTRAINT fk_meta_site_meta
		FOREIGN KEY ( metacontent_id ) 
		REFERENCES meta_content ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_meta_content_site
		FOREIGN KEY ( site_id ) 
		REFERENCES sites ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_meta_site ON site_meta( metacontent_id );-- --
CREATE INDEX idx_meta_site_data ON site_meta( site_id );-- --

CREATE VIEW site_meta_view AS SELECT
	c.id AS id, 
	c.meta_id AS meta_id, 
	c.sort_order AS sort_order,
	c.bare AS bare, 
	c.content AS content, 
	m.label AS meta_label,
	m.format AS format,
	m.is_fulltext AS is_fulltext,
	r.site_id AS site_id
	
	FROM meta_content c
	LEFT JOIN metadata m ON c.meta_id = m.id
	LEFT JOIN site_meta r ON c.id = r.metacontent_id;-- --


-- User metadata
CREATE TABLE user_meta (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	metacontent_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	
	CONSTRAINT fk_meta_user_meta
		FOREIGN KEY ( metacontent_id ) 
		REFERENCES meta_content ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_meta_content_user
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_meta_user ON user_meta( metacontent_id );-- --
CREATE INDEX idx_meta_user_data ON user_meta( user_id );-- --

CREATE VIEW user_meta_view AS SELECT
	c.id AS id, 
	c.meta_id AS meta_id, 
	c.sort_order AS sort_order,
	c.bare AS bare, 
	c.content AS content, 
	m.label AS meta_label,
	m.format AS format,
	m.is_fulltext AS is_fulltext,
	r.user_id AS user_id
	
	FROM meta_content c
	LEFT JOIN metadata m ON c.meta_id = m.id
	LEFT JOIN user_meta r ON c.id = r.metacontent_id;-- --


-- Page metadata
CREATE TABLE page_meta (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	metacontent_id INTEGER NOT NULL,
	page_id INTEGER NOT NULL,
	
	CONSTRAINT fk_meta_page_meta
		FOREIGN KEY ( metacontent_id ) 
		REFERENCES meta_content ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_meta_content_page
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_meta_page ON page_meta( metacontent_id );-- --
CREATE INDEX idx_meta_page_data ON page_meta( page_id );-- --

CREATE VIEW page_meta_view AS SELECT
	c.id AS id, 
	c.meta_id AS meta_id, 
	c.sort_order AS sort_order,
	c.bare AS bare, 
	c.content AS content, 
	m.label AS meta_label,
	m.format AS format,
	m.is_fulltext AS is_fulltext,
	r.page_id AS page_id
	
	FROM meta_content c
	LEFT JOIN metadata m ON c.meta_id = m.id
	LEFT JOIN page_meta r ON c.id = r.metacontent_id;-- --


-- Comment metadata
CREATE TABLE comment_meta (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	metacontent_id INTEGER NOT NULL,
	comment_id INTEGER NOT NULL,
	
	CONSTRAINT fk_meta_page_meta
		FOREIGN KEY ( metacontent_id ) 
		REFERENCES meta_content ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_meta_content_comment
		FOREIGN KEY ( comment_id ) 
		REFERENCES comments ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_meta_comment ON comment_meta( metacontent_id );-- --
CREATE INDEX idx_meta_comment_data ON comment_meta( comment_id );-- --

CREATE VIEW comment_meta_view AS SELECT
	c.id AS id, 
	c.meta_id AS meta_id, 
	c.sort_order AS sort_order,
	c.bare AS bare, 
	c.content AS content, 
	m.label AS meta_label,
	m.format AS format,
	m.is_fulltext AS is_fulltext,
	r.comment_id AS comment_id
	
	FROM meta_content c
	LEFT JOIN metadata m ON c.meta_id = m.id
	LEFT JOIN comment_meta r ON c.id = r.metacontent_id;-- --



-- Content voting and feedback
CREATE TABLE content_votes (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	user_id INTEGER DEFAULT NULL, 
	
	-- E.G. poll, election, survey, flag etc...
	vote_type TEXT DEFAULT NULL COLLATE NOCASE,
	score REAL NOT NULL DEFAULT 0, 
	
	-- Additional comment or feedback E.G. report or reason
	note TEXT DEFAULT NULL COLLATE NOCASE,
	lang_id INTEGER NOT NULL,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	
	CONSTRAINT fk_vote_user 
		FOREIGN KEY ( user_id ) 
		REFERENCES users ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_vote_lang 
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_vote_user ON content_votes ( user_id )
	WHERE user_id IS NOT NULL;-- --
CREATE INDEX idx_vote_type ON content_votes ( vote_type );-- --
CREATE INDEX idx_vote_score ON content_votes ( score );-- --
CREATE INDEX idx_vote_created ON content_votes ( created );-- --

CREATE VIRTUAL TABLE vote_search 
	USING fts4( body, tokenize=unicode61 );-- --

CREATE TRIGGER vote_insert AFTER INSERT ON content_votes FOR EACH ROW 
WHEN NEW.note IS NOT NULL
BEGIN
	INSERT INTO vote_search( docid, body ) 
		VALUES ( NEW.id, NEW.note );
END;-- --

CREATE TRIGGER vote_update AFTER UPDATE ON content_votes FOR EACH ROW 
WHEN NEW.note IS NOT NULL
BEGIN
	UPDATE vote_search SET body = NEW.note
		WHERE docid = NEW.id;
END;-- --

CREATE TRIGGER vote_delete BEFORE DELETE ON content_votes FOR EACH ROW 
BEGIN
	DELETE FROM vote_search WHERE docid = OLD.id;
END;-- --


CREATE TABLE page_votes (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	page_id INTEGER DEFAULT NULL, 
	vote_id INTEGER DEFAULT NULL, 
		
	CONSTRAINT fk_vote_page 
		FOREIGN KEY ( page_id ) 
		REFERENCES pages ( id ) 
		ON DELETE CASCADE, 
		
	CONSTRAINT fk_vote_page_vote 
		FOREIGN KEY ( vote_id ) 
		REFERENCES content_votes ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_page_vote ON page_votes ( vote_id, page_id );-- --

CREATE VIEW page_vote_view AS SELECT 
	c.id AS id, 
	c.page_id AS page_id, 
	c.created AS created, 
	v.vote_type AS vote_type, 
	v.score AS score, 
	v.note AS note, 
	v.created AS created, 
	l.iso_code AS lang_iso, 
	l.label AS lang_label, 
	
	-- Authorship
	COALESCE( u.display, u.username, '.' ) AS voter_name,
	COALESCE( e.email, '@' ) AS voter_email,
	COALESCE( e.last_ip, '::' ) AS voter_ip,
	COALESCE( e.last_active, '0' ) AS voter_last_active,
	COALESCE( e.last_login, '0' ) AS voter_last_login,
	COALESCE( u.user_id, 0 ) AS voter_id
	
	FROM page_votes c
	LEFT JOIN content_votes v ON v.id = c.vote_id
	LEFT JOIN languages l ON v.lang_id = l.id
	LEFT JOIN users u ON v.user_id = u.id
	LEFT JOIN user_auth e ON e.user_id = u.id;-- --


CREATE TABLE comment_votes (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	comment_id INTEGER DEFAULT NULL, 
	vote_id INTEGER DEFAULT NULL, 
		
	CONSTRAINT fk_vote_comment 
		FOREIGN KEY ( comment_id ) 
		REFERENCES comments ( id ) 
		ON DELETE CASCADE, 
		
	CONSTRAINT fk_vote_comment_vote
		FOREIGN KEY ( vote_id ) 
		REFERENCES content_votes ( id ) 
		ON DELETE CASCADE
);-- --
CREATE INDEX idx_comment_vote ON comment_votes ( vote_id, comment_id );-- --


CREATE VIEW comment_vote_view AS SELECT 
	c.id AS id, 
	c.comment_id AS comment_id, 
	c.created AS created, 
	v.vote_type AS vote_type, 
	v.score AS score, 
	v.note AS note, 
	v.created AS created, 
	l.iso_code AS lang_iso, 
	l.label AS lang_label, 
	
	-- Authorship
	COALESCE( u.display, u.username, '.' ) AS voter_name,
	COALESCE( e.email, '@' ) AS voter_email,
	COALESCE( e.last_ip, '::' ) AS voter_ip,
	COALESCE( e.last_active, '0' ) AS voter_last_active,
	COALESCE( e.last_login, '0' ) AS voter_last_login,
	COALESCE( u.user_id, 0 ) AS voter_id
	
	FROM comment_votes c
	LEFT JOIN content_votes v ON v.id = c.vote_id
	LEFT JOIN languages l ON v.lang_id = l.id
	LEFT JOIN users u ON v.user_id = u.id
	LEFT JOIN user_auth e ON e.user_id = u.id;-- --

-- Input forms
CREATE TABLE forms(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title TEXT NOT NULL COLLATE NOCASE, 
	enctype TEXT NOT NULL DEFAULT 'multipart/form-data' COLLATE NOCASE,
	form_method TEXT NOT NULL DEFAULT 'post' COLLATE NOCASE,
	
	-- Submission path
	path_id INTEGER NOT NULL, 
	
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
	updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	
	CONSTRAINT fk_form_page_path
		FOREIGN KEY ( path_id ) 
		REFERENCES page_paths ( id ) 
		ON DELETE CASCADE
);-- --
CREATE UNIQUE INDEX idx_form_title ON forms( title );-- --
CREATE INDEX idx_form_path ON forms( path_id );-- --
CREATE INDEX idx_form_created ON forms( created );-- --
CREATE INDEX idx_form_updated ON forms( updated );-- --

CREATE TRIGGER form_update AFTER UPDATE ON forms FOR EACH ROW 
BEGIN
	UPDATE forms SET updated = CURRENT_TIMESTAMP 
		WHERE id = NEW.id;
END;-- --

-- Form specific action events
CREATE TABLE form_events (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	form_id INTEGER NOT NULL,
	event_id INTEGER NOT NULL,
	trigger_id INTEGER NOT NULL,
	sort_order INTEGER NOT NULL DEFAULT 0,
	
	-- Serialized JSON parameters
	settings TEXT NOT NULL DEFAULT '{}' COLLATE NOCASE,
	settings_id INTEGER DEFAULT NULL,
	
	CONSTRAINT fk_form_events_form
		FOREIGN KEY ( form_id ) 
		REFERENCES forms ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_form_events_trigger 
		FOREIGN KEY ( trigger_id ) 
		REFERENCES triggers ( id ) 
		ON DELETE CASCADE,
		
	CONSTRAINT fk_form_events_event
		FOREIGN KEY ( event_id ) 
		REFERENCES events ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_form_events_settings
		FOREIGN KEY ( settings_id ) 
		REFERENCES settings ( id )
		ON DELETE SET NULL
);-- --
CREATE INDEX idx_form_event_sort ON form_events( sort_order ASC );-- --
CREATE INDEX idx_form_event_form ON form_events( form_id );-- --
CREATE INDEX idx_form_event_event ON form_events( event_id );-- --
CREATE INDEX idx_form_event_trigger ON form_events( trigger_id );-- --
CREATE INDEX idx_form_event_settings ON form_events ( settings_id )
	WHERE settings_id IS NOT NULL;-- --

-- Property render templates for input fields
CREATE TABLE form_fields(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	form_id INTEGER NOT NULL, 
	field_name TEXT NOT NULL COLLATE NOCASE, 
	filter TEXT NOT NULL DEFAULT '' COLLATE NOCASE, 
	style_id INTEGER NOT NULL, 
	
	-- HTML templates
	create_template TEXT NOT NULL COLLATE NOCASE, 
	edit_template TEXT NOT NULL COLLATE NOCASE, 
	view_template TEXT NOT NULL COLLATE NOCASE,
	
	CONSTRAINT fk_field_form
		FOREIGN KEY ( form_id ) 
		REFERENCES forms ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_field_style
		FOREIGN KEY ( style_id ) 
		REFERENCES styles ( id )
		ON DELETE RESTRICT
);-- --
CREATE INDEX idx_form_field_form ON form_fields( form_id );-- --
CREATE INDEX idx_form_field_name ON form_fields( field_name );-- --

-- Form field descriptions
CREATE TABLE field_language(
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	field_id INTEGER NOT NULL,
	lang_id INTEGER NOT NULL,
	title TEXT NOT NULL COLLATE NOCASE, 
	label TEXT NOT NULL COLLATE NOCASE, 
	special TEXT NOT NULL DEFAULT '' COLLATE NOCASE, 
	description TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
	
	CONSTRAINT fk_field_template
		FOREIGN KEY ( field_id ) 
		REFERENCES form_fields ( id ) 
		ON DELETE CASCADE,
	
	CONSTRAINT fk_field_lang
		FOREIGN KEY ( lang_id ) 
		REFERENCES languages ( id ) 
		ON DELETE CASCADE 
);-- --
CREATE INDEX idx_form_lang_field ON field_language( field_id );-- --
CREATE INDEX idx_form_lang ON field_language( lang_id );-- --




-- Content installation moved to main_install.sql

