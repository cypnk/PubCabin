
-- Default settings
INSERT INTO settings( id, label, info ) 
VALUES ( 1, 'default_site_settings', '{ 
	"page_title" : "Rustic Cyberpunk",
	"page_sub" : "Coffee. Code. Cabins.",
	"timezone" : "America\/New_York",
	"language" : "en",
	"locale" : "US",
	"mail_from" : "domain@localhost", 
	"mail_whitelist" : [
		"root@localhost",
		"www@localhost"
	],
	"frame_whitelist" : [
		"https:\/\/www.youtube.com",
		"https:\/\/player.vimeo.com",
		"https:\/\/archive.org",
		"https:\/\/peertube.mastodon.host",
		"https:\/\/lbry.tv",
		"https:\/\/odysee.com"
	], 
	"app_name": "PubCabin",
	"app_start": "2017-03-14T04:30:55Z",
	"skip_local": 1,
	"enable_register": 1,
	"auto_approve_reg": 1,
	"shared_assets": "\/",
	"default_stylesheets": [],
	"default_scripts": [],
	"default_meta": {
		"meta" : [
			{ "name" : "generator", "content" : 
				"PubCabin; https:\/\/github.com\/cypnk\/PubCabin" }
			]
	}
}' );-- --


-- Default page types
INSERT INTO page_types (
	id, label, render, behavior
) VALUES 
( 1, 'page', '', '{
	"parents":	["*", "page"],
	"children":	["page"],
 	"label":	"{lang:ptypes:page}",
 	"description":	"{lang:ptypes:pagedesc}",
 	"allow_children": 1,
 	"allow_comments": 1,
 	"privileges"	: {
		"create_roles"	: [],
		"edit_roles"	: [],
		"delete_roles"	: [],
		"upload_roles"	: [],
		"mod_roles"	: [],
		"comment_roles"	: []
	},
	"settings"	: {}
}' );-- --


-- Default roles
INSERT INTO roles( id, label, description )
VALUES 
( 1, '{lang:roles:admin:label}', '{lang:roles:admin:desc}' ), 
( 2, '{lang:roles:siteadmin:label}', '{lang:roles:siteadmin:desc}' ), 
( 3, '{lang:roles:sitemod:label}', '{lang:roles:sitemod:desc}' );-- --


-- Homepage URL
INSERT INTO page_paths ( id, url ) 
VALUES ( 1, '/' );-- --

-- Base website
INSERT INTO sites ( id, label, basename, basepath, settings_id ) 
VALUES ( 1, 'localhost', 'localhost', '', 1 );-- --

-- Test site
INSERT INTO site_aliases( id, site_id, basename ) 
VALUES ( 1, 1, 'pubcabin.local' );-- --

-- Main viewable render area 
INSERT INTO areas ( id, label, site_id ) 
VALUES ( 1, 'main', 1 );-- --

-- Default content
INSERT INTO pages( id, site_id, type_id, is_home ) 
VALUES( 1, 1, 1, 1 );-- --

-- Page area
INSERT INTO page_area( page_id, area_id ) 
VALUES ( 1, 1 );-- --

-- Page content
INSERT INTO page_texts( 
	id, page_id, lang_id, path_id, slug, title, body, bare 
) VALUES (
	1, 1, 1, 1, 'home', 'Home', 
	'<h1>Home</h1><p>Welcome to your default homepage</p>', 
	'Home Welcome to your default homepage' 
);

