
-- Database presets
PRAGMA trusted_schema = OFF;	-- Preemptive defense
PRAGMA cell_size_check = ON;	-- Integrity check
PRAGMA encoding = "UTF-8";	-- Default encoding set to UTF-8
PRAGMA auto_vacuum = "2";	-- File size improvement
PRAGMA temp_store = "2";	-- Memory temp storage for performance
PRAGMA journal_mode = "WAL";	-- Performance improvement
PRAGMA secure_delete = "1";	-- Privacy improvement


-- Core configuration settings
CREATE TABLE configs (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	label TEXT NOT NULL COLLATE NOCASE,
	setting TEXT NOT NULL,
	render TEXT NOT NULL DEFAULT 'text',
	description TEXT NOT NULL DEFAULT ''
);-- --
CREATE UNIQUE INDEX idx_config_label ON configs ( label );-- --

-- Core init
INSERT INTO configs ( label, setting, render ) VALUES 
	( 'APP_NAME', 'PubCabin', 'text' ),
	( 'APP_START', CURRENT_TIMESTAMP, 'text' ),
	( 'CACHE', '{store}cache/', 'text' ), 
	( 'CACHE_TTL', '3200', 'int' ),
	( 'FILE_PATH', '{path}htdocs/', 'text' ),
	( 'ERROR', '{store}cache/error.log', 'text' ), 
	( 'NOTICE', '{store}cache/notice.log', 'text' ), 
	( 'ERROR_ROOT', '{path}errors/', 'text' ), 
	( 'ERROR_visit', '{cache}visitor_errors.log', 'text' ), 
	( 'PLUGINS', '{path}plugins/', 'text' ), 
	( 'PLUGINS_ENABLED', '', 'text' ), 
	( 'PLUGIN_DATA', '{store}cache/plugins/', 'text' ),
	( 'PLUGIN_ASSETS', 'assets/', 'text' ),
	( 'SITE_WHITE', 
'{
	"localhost" : []
}', 'json' ), 
	( 'DEFAULT_BASEPATH', 
'{
	"basepath"		: "\/",
	"is_active"		: 1,
	"is_maintenance"	: 0,
	"settings"		: []
}', 'json' ),
	( 'MAIL_WHITELIST', '', 'text' ), 
	( 'MAIL_FROM', 'www@localhost', 'text' ), 
	( 'MAX_URL_SIZE', '512', 'int' ),
	( 'EXT_WHITELIST', 
'{
	"text"		: "css, js, txt, html",
	"images"	: "ico, jpg, jpeg, gif, bmp, png, tif, tiff, svg", 
	"fonts"		: "ttf, otf, woff, woff2",
	"audio"		: "ogg, oga, mpa, mp3, m4a, wav, wma, flac",
	"video"		: "avi, mp4, mkv, mov, ogg, ogv"
}', 'json' ),
	( 'LANGUAGE', 'en', 'text' ),
	( 'LOCALE', 'US', 'text' ),
	( 'TIMEZONE', 'America/New_York', 'text' ),
	( 'DATE_NICE', 'l, F j, Y', 'text' ),
	( 'TOKEN_BYTES', '8', 'int' ), 
	( 'NONCE_HASH', 'tiger160,4', 'text' ),
	( 'MAX_SEARCH_WORDS', '10', 'int' ),
	( 'STYLE_LIMIT', '20', 'int' ),
	( 'SCRIPT_LIMIT', '10', 'int' ),
	( 'META_LIMIT', '15', 'int' ),
	( 'FOLDER_LIMIT', '15', 'int' ),
	( 'SHARED_ASSETS', '/', 'text' ),
	( 'FRAME_WHITELIST', '', 'text' ),
	( 'DEFAULT_STYLESHEETS', '', 'text' ),
	( 'DEFAULT_SCRIPTS', '', 'text' ),
	( 'DEFAULT_META', 
'{
	"meta" : [
		{ "name" : "generator", "content" : 
			"Bare; https:\/\/github.com\/cypnk\/PubCabin" }
	]
}', 'json' ),
	( 'DEFAULT_CLASSES', 
'{
	"body_classes"			: "",
	
	"heading_classes"		: "",
	"heading_wrap_classes"		: "content", 
	"heading_h_classes"		: "",
	"heading_a_classes"		: "",
	"tagline_classes"		: "",
	"items_wrap_classes"		: "content", 
	"no_posts_wrap"			: "content",
	
	"main_nav_classes"		: "main",
	"main_ul_classes"		: "", 
	
	"pagination_wrap_classes"	: "content", 
	"list_wrap_classes"		: "content", 
	
	"home_classes"			: "content",
	"home_wrap_classes"			: "",
	"about_classes"			: "content",
	"about_wrap_classes"		: "",
	
	"post_index_wrap_classes"	: "content",
	"post_index_ul_wrap_classes"	: "index",
	"post_index_header_classes"	: "",
	"post_index_header_h_classes"	: "",
	"post_index_item_classes"	: "",
	
	"post_classes"			: "",
	"post_wrap_classes"		: "",
	"post_heading_classes"		: "",
	"post_heading_h_classes"	: "",
	"post_heading_a_classes"	: "",
	"post_heading_wrap_classes"	: "content",
	"post_body_wrap_classes"	: "content",
	"post_body_content_classes"	: "",
	"post_body_tag_classes"		: "",
	"post_pub_classes"		: "",
	
	"post_idx_classes"		: "",
	"post_idx_wrap_classes"		: "",
	"post_idx_heading_classes"	: "",
	"post_idx_heading_h_classes"	: "",
	"post_idx_heading_a_classes"	: "",
	"post_idx_heading_wrap_classes"	: "content",
	"post_idx_body_wrap_classes"	: "content",
	"post_idx_body_content_classes"	: "",
	"post_idx_body_tag_classes"	: "",
	"post_idx_pub_classes"		: "",
	
	"footer_classes"		: "",
	"footer_wrap_classes"		: "content", 
	"footer_nav_classes"		: "",
	"footer_ul_classes"		: "",
	
	"crumb_classes"			: "",
	"crumb_wrap_classes"		: "",
	"crumb_sub_classes"		: "",
	"crumb_sub_wrap_classes"	: "",
	
	"crumb_item_classes"		: "",
	"crumb_link_classes"		: "",
	"crumb_current_classes"		: "",
	"crumb_current_item"		: "",
	"pagination_classes"		: "",
	"pagination_ul_classes"		: "",
	
	"nav_link_classes"		: "",
	"nav_link_a_classes"		: "",
	
	"list_classes"			: "related",
	"list_h_classes"		: "",
	
	"tag_wrap_classes"		: "tags",
	"tag_heading_classes"		: "",
	"tag_index_wrap_classes"	: "tags",
	"tag_index_heading_classes"	: "",
	"tag_ul_classes"		: "tags",
	"tag_item_classes"		: "",
	"tag_item_a_classes"		: "",
	"tag_index_item_classes"	: "",
	"tag_index_item_a_classes"	: "",
	
	"sibling_wrap_classes"		: "content",
	"sibling_nav_classes"		: "siblings",
	"sibling_nav_ul_classes"	: "",
	
	"related_wrap_classes"		: "content",
	"related_h_classes"		: "",
	"related_nav_classes"		: "related",
	"related_ul_classes"		: "related",
	
	"nextprev_wrap_classes"		: "content", 
	"nextprev_nav_classes"		: "siblings",
	"nextprev_ul_classes"		: "",
	"nextprev_next_classes"		: "",
	"nextprev_next_a_classes"	: "",
	"nextprev_prev_classes"		: "",
	"nextprev_prev_a_classes"	: "",
	
	"nav_home_link_classes"		: "",
	"nav_home_link_a_classes"	: "",
	
	"nav_current_classes"		: "",
	"nav_current_s_classes"		: "",
	"nav_prev_classes"		: "",
	"nav_prev_a_classes"		: "",
	"nav_noprev_classes"		: "",
	"nav_noprev_s_classes"		: "",
	"nav_next_classes"		: "",
	"nav_next_a_classes"		: "",
	"nav_nonext_classes"		: "",
	"nav_nonext_s_classes"		: "",
	
	"nav_first1_classes"		: "",
	"nav_first1_a_classes"		: "",
	"nav_first2_classes"		: "",
	"nav_first2_a_classes"		: "",
	"nav_first_s_classes"		: "",
	
	"nav_last_s_classes"		: "",
	"nav_last1_classes"		: "",
	"nav_last1_a_classes"		: "",
	"nav_last2_classes"		: "",
	"nav_last2_a_classes"		: "",
	
	"form_classes"			: "",
	"fieldset_classes"		: "",
	"search_form_classes"		: "",
	"search_form_wrap_classes"	: "",
	"search_fieldset_classes"	: "",
	"field_wrap"			: "",
	"button_wrap"			: "",
	"label_classes"			: "",
	"special_classes"		: "",
	"input_classes"			: "",
	"desc_classes"			: "",
	"search_input_classes"		: "",
	"search_button_classes"		: "",
	
	"submit_classes"		: "",
	"alt_classes"			: "",
	"warn_classes"			: "",
	"action_classes"		: ""
}', 'json' ),
	( 'DEFAULT_LANGUAGE', '{}', 'json' ),
	( 'DEFAULT_JCSP', 
'{
	"default-src"		: "''none''",
	"img-src"		: "*",
	"base-uri"		: "''self''",
	"style-src"		: "''self''",
	"script-src"		: "''self''",
	"font-src"		: "''self''",
	"form-action"		: "''self''",
	"frame-ancestors"	: "''self''",
	"frame-src"		: "*",
	"media-src"		: "''self''",
	"connect-src"		: "''self''",
	"worker-src"		: "''self''",
	"child-src"		: "''self''",
	"require-trusted-types-for" : "''script''"
}', 'json' ),
	( 'TAG_WHITE', 
'{
	"p"		: [ "style", "class", "align", 
				"data-pullquote", "data-video", 
				"data-media" ],
	
	"div"		: [ "style", "class", "align" ],
	"span"		: [ "style", "class" ],
	"br"		: [ "style", "class" ],
	"hr"		: [ "style", "class" ],
	
	"h1"		: [ "style", "class" ],
	"h2"		: [ "style", "class" ],
	"h3"		: [ "style", "class" ],
	"h4"		: [ "style", "class" ],
	"h5"		: [ "style", "class" ],
	"h6"		: [ "style", "class" ],
	
	"strong"	: [ "style", "class" ],
	"em"		: [ "style", "class" ],
	"u"	 	: [ "style", "class" ],
	"strike"	: [ "style", "class" ],
	"del"		: [ "style", "class", "cite" ],
	
	"ol"		: [ "style", "class" ],
	"ul"		: [ "style", "class" ],
	"li"		: [ "style", "class" ],
	
	"code"		: [ "style", "class" ],
	"pre"		: [ "style", "class" ],
	
	"sup"		: [ "style", "class" ],
	"sub"		: [ "style", "class" ],
	
	"a"		: [ "style", "class", "rel", 
				"title", "href" ],
	"img"		: [ "style", "class", "src", "height", "width", 
				"alt", "longdesc", "title", "hspace", 
				"vspace", "srcset", "sizes"
				"data-srcset", "data-src", 
				"data-sizes" ],
	"figure"	: [ "style", "class" ],
	"figcaption"	: [ "style", "class" ],
	"picture"	: [ "style", "class" ],
	"table"		: [ "style", "class", "cellspacing", 
					"border-collapse", 
					"cellpadding" ],
	
	"thead"		: [ "style", "class" ],
	"tbody"		: [ "style", "class" ],
	"tfoot"		: [ "style", "class" ],
	"tr"		: [ "style", "class" ],
	"td"		: [ "style", "class", "colspan", 
				"rowspan" ],
	"th"		: [ "style", "class", "scope", 
				"colspan", "rowspan" ],
	
	"caption"	: [ "style", "class" ],
	"col"		: [ "style", "class" ],
	"colgroup"	: [ "style", "class" ],
	
	"summary"	: [ "style", "class" ],
	"details"	: [ "style", "class" ],
	
	"q"		: [ "style", "class", "cite" ],
	"cite"		: [ "style", "class" ],
	"abbr"		: [ "style", "class" ],
	"blockquote"	: [ "style", "class", "cite" ],
	"body"		: []
}', 'json' ),
	( 'FORM_WHITE', 
'{
	"form"		: [ "id", "method", "action", "enctype", "style", "class" ], 
	"input"		: [ "id", "type", "name", "required", , "max", "min", 
				"value", "size", "maxlength", "checked", 
				"disabled", "style", "class" ],
	"label"		: [ "id", "for", "style", "class" ], 
	"textarea"	: [ "id", "name", "required", "rows", "cols",  
				"style", "class" ],
	"select"	: [ "id", "name", "required", "multiple", "size", 
				"disabled", "style", "class" ],
	"option"	: [ "id", "value", "disabled", "style", "class" ],
	"optgroup"	: [ "id", "label", "style", "class" ]
}', 'json' ),
	( 'ROUTE_MARK', 
'{
	"*"	: "(?<all>.+)",
	":id"	: "(?<id>[1-9][0-9]*)",
	":page"	: "(?<page>[1-9][0-9]*)",
	":label": "(?<label>[\\pL\\pN\\s_\\-]{1,30})",
	":nonce": "(?<nonce>[a-z0-9]{10,30})",
	":token": "(?<token>[a-z0-9\\+\\=\\-\\%]{10,255})",
	":meta"	: "(?<meta>[a-z0-9\\+\\=\\-\\%]{7,255})",
	":tag"	: "(?<tag>[\\pL\\pN\\s_\\,\\-]{1,30})",
	":tags"	: "(?<tags>[\\pL\\pN\\s_\\,\\-]{1,255})",
	":year"	: "(?<year>[2][0-9]{3})",
	":month": "(?<month>[0-3][0-9]{1})",
	":day"	: "(?<day>[0-9][0-9]{1})",
	":slug"	: "(?<slug>[\\pL\\-\\d]{1,100})",
	":tree"	: "(?<tree>[\\pL\\/\\-\\d]{1,255})",
	":file"	: "(?<file>[\\pL_\\-\\d\\.\\s]{1,120})",
	":find"	: "(?<find>[\\pL\\pN\\s\\-_,\\.\\:\\+]{2,255})",
	":redir": "(?<redir>[a-z_\\:\\/\\-\\d\\.\\s]{1,120})"
}', 'json' ),
	( 'SESSION_EXP', '300', 'int' ),
	( 'SESSION_BYTES', '12', 'int' ),
	( 'SESSION_LIMIT_COUNT', '5', 'int' ),
	( 'SESSION_LIMIT_MEDIUM', '3', 'int' ),
	( 'SESSION_LIMIT_HEAVY', '1', 'int' ),
	( 'COOKIE_EXP', '86400', 'int' ),
	( 'COOKIE_PATH', '/', 'text' ),
	( 'COOKIE_RESTRICT', '1', 'int' ),
	( 'FORM_DELAY', '30', 'int' ),
	( 'FORM_EXPIRE', '7200', 'int' ),
	( 'SUPPORTED_PHP', '7.2, 7.3, 7.4, 8.0', 'text' );


