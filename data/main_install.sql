-- Default database content

-- Language and translations
INSERT INTO languages (
	id, iso_code, label, display, is_default
) VALUES 
( 1, 'en', 'English', 'English', 1 ), 
( 2, 'es', 'Español', 'Spanish', 0 ), 
( 3, 'ar', 'عربى', 'Arabic', 0 ),
( 4, 'bn', 'বাংলা', 'Bengali', 0 ),
( 5, 'de', 'Deutsch', 'German', 0 ),
( 6, 'el', 'ελληνικά', 'Greek', 0 ),
( 7, 'fa', 'فارسی', 'Farsi', 0 ),
( 8, 'fr', 'Français', 'French', 0 ),
( 9, 'gu', 'ગુજરાતી', 'Gujarati', 0 ),
( 10, 'he', 'עברית‬', 'Hebrew', 0 ),
( 11, 'hi', 'हिंदी', 'Hindi', 0 ),
( 12, 'hy', 'հայերեն', 'Armenian', 0 ),
( 13, 'it', 'Italiano', 'Italian', 0 ),
( 14, 'jp', '日本語', 'Japanese', 0 ),
( 15, 'ko', '조선말', 'Korean', 0 ),
( 16, 'lo', 'ພາສາລາວ', 'Lao', 0 ),
( 17, 'ml', 'Melayu', 'Malay', 0 ),
( 18, 'nl', 'Nederlands', 'Dutch', 0 ),
( 19, 'pa', 'ਪੰਜਾਬੀ', 'Punjabi', 0 ),
( 20, 'pt', 'Português', 'Portuguese', 0 ),
( 21, 'pl', 'Język polski', 'Polish', 0 ),
( 22, 'ru', 'русский', 'Russian', 0 ),
( 23, 'si', 'සිංහල', 'Sinhalese', 0 ),
( 24, 'ta', 'தமிழ்', 'Tamil', 0 ),
( 25, 'th', 'ภาษาไทย', 'Thai', 0 ),
( 26, 'ur', 'اُردُو‬', 'Urdu', 0 ),
( 27, 'vi', 'Tiếng Việt', 'Vietnamese', 0 ),
( 28, 'zh', '中文', 'Chinese', 0 );-- --

-- Translations are JSON which need placeholder replacements before parsing
INSERT INTO translations (
	id, locale, lang_id, is_default, definitions
) VALUES ( 1, 'us', 1, 1, '{
	"date_nice"	: "l, F j, Y",
	"headings"	: {
		"related"	: "Related", 
		"tags"		: "Tags:"
	}, 
	"nav"		: {
		"previous"	: "\u0026larr; Previous"
		"next"		: "Next \u0026rarr;",
		"home"		: "Home",
		"back"		: "Back",
		"about"		: "About",
		"archive"	: "Archive",
		"feed"		: "Feed",
		"search"	: "Search",
		"login"		: "Login",
		"logout"	: "Logout",
		"register"	: "Register"
	}, 
	"forms"		: {
		"search"	: {
			"placeholder"	: "Find by title or body",
			"button"	: "Search"
		},
		"postpage"	: {
			"create"	: "Create new message",
			"edit"		: "Editing message"
		},
		"login"		: {
			"page"		: "Login",
			"name"		: "Name <span>(required)<\/span>",
			"namedesc"	: "Between {name_min} and {name_max} characters. Letters, numbers, and spaces supported.",
			"pass"		: "Password <span>(required)<\/span>",
			"passdesc"	: "Minimum {pass_min} characters.",
			"rem"		: "Remember me",
			"submit"	: "Login"
		},
		"register"	: {
			"page"		: "Register",
			"name"		: "Name <span>(required)<\/span>",
			"namedesc"	: "Between {name_min} and {name_max} characters. Letters, numbers, and spaces supported.",
			"pass"		: "Password <span>(required)<\/span>",
			"passdesc"	: "Minimum {pass_min} characters.",
			"repeat"	: "Repeat password <span>(required)<\/span>",
			"repeatdesc"	: "Must match password entered above",
			"rem"		: "Remember me",
			"terms"		: "Agree to the <a href=\"{terms}\" target=\"_blank\">site terms</a>",
			"submit"	: "Register"
		},
		"password"	: {
			"page"		: "Change password",
			"old"		: "Old Password <span>(required)<\/span>",
			"olddesc"	: "Must match current password.",
			"new"		: "New password <span>(required)<\/span>",
			"newdesc"	: "Minimum {pass_min} characters. Must be different from old password.",
			"submit"	: "Change"
		},
		"profile"	: {
			"page"		: "Profile",
			"name"		: "Name",
			"display"	: "Display name <span>(optional)<\/span>",
			"displaydesc"	: "Between {display_min} and {display_max} characters. Letters, numbers, and spaces supported.",
			"bio"		: "Bio <span>(optional)<\/span>",
			"biodesc"	: "Simple HTML and a subset of <a href=\"{formatting}\">Markdown<\/a> supported.",
			"submit"	: "Save"
		},
		"createpage"	: {
			"page"		: "Create a new page",
			"title"		: "Page title title",
			"titledesc"	: "Between {title_min} and {title_max} characters. Letters, numbers, and spaces supported.",
			"msg"		: "Page body <span>(required)<\/span>",
			"msgdesc"	: "Simple HTML and a subset of <a href=\"{formatting}\">Markdown<\/a> supported.",
			"submit"	: "Post"
		},
		"editpage"	: {
			"page"		: "Edit page",
			"title"		: "Page title",
			"titledesc"	: "Between {title_min} and {title_max} characters. Letters, numbers, and spaces supported.",
			"msg"		: "Editing page body <span>(required)<\/span>",
			"msgdesc"	: "Simple HTML and a subset of <a href=\"{formatting}\">Markdown<\/a> supported.",
			"submit"	: "Save changes"
		}
	},
	"sections" : {
		"settings"  {
			"websettings"	: "Change website settings", 
			"fields" {
				"page_title" : "Website title",
				"page_sub" : "Subtitle or tagline",
				"timezone" : "Default timezone",
				"mail_from" : "Email sending address", 
				"mail_whitelist" : "List of allowed recipients",
				"frame_whitelist" : "Whitelist of embeddable URLs" 
			}
		},
		"user" : {
			"messages"	: "{message_count} Messages",
			"replies"	: "{reply_count} Replies",
			"password"	: "Password",
			"profile"	: "Profile",
			"deleteacct"	: "Delete account",
			"resetpass"	: "Reset password",
			"changeemail"	: "Change email address",		
			"changepass"	: "Change password"
		}, 
		"moderation" : {
			"recent"	: "{recent_count} Recent",
			"queue"		: "{queue_count} Queue",
			"add"		: "Add filter",
			"duration"	: "Duration",
			"delselect"	: "Delete selected",
			"durdesc"	: "E.G. \"5 hours\" (without quotes). Leave empty for no expiration",
			"drop"		: {
				"action"	: "Action",
				"hold"		: "Hold",
				"pub"		: "Publish",
				"del"		: "Delete",
				"holdsusp"	: "Hold, suspend user",
				"delsusp"	: "Delete, suspend user",
				"holdsuspip"	: "Hold, suspend IP",
				"delsuspip"	: "Delete, suspend IP",
				"holdsuspuip"	: "Hold, suspend user, suspend IP",
				"delsuspuip"	: "Delete, suspend user, suspend IP",
				"holdblock"	: "Hold, block user",
				"delblock"	: "Delete, block user",
				"holdblockip"	: "Hold, block IP",
				"delblockip"	: "Delete, block IP",
				"holdblockuip"	: "Hold, block user, block IP",
				"delblockuip"	: "Delete, block user, block IP",
				"noanon"	: "No anonymous comments",
				"close"		: "No new comments, show existing comments",
				"hide"		: "No new comments, hide existing comments",
				"dur"		: " - Duration: {duration}"
			},
			"filters"	: {
				"label"		: "Filters",
				"ip"		: "IP Ranges and Hostname filter",
				"iplbl"		: "IP Addresses",
				"ipdesc"	: "IPv4 or IPv6 range in CIDR notation or individual ip address. Separate by comma.",
				"hostlbl"	: "Host names and domains",
				"hostdesc"	: "Host names. Separate by comma.",
				"word"		: "Word / Phrase filter",
				"wordlbl"	: "Block Word or phrase",
				"worddesc"	: "Case insensitive.",
				"user"		: "Username filter",
				"userlbl"	: "Block username",
				"userdesc"	: "Case insensitive. Separate by comma.",
				"url"		: "Page comments",
				"urllbl"	: "URLs / Unique identifiers",
				"urldesc"	: "Case insensitive. Relative paths only."
			}
		}
	}, 
	"errors"	: {
		"error"		: "Error",
		"generic"	: "An error has occured",
		"returnhome"	: "<a href=\"{home}\">Return home<\/a>",
		"noposts"	: "No more posts. Return <a href=\"{home}\">home<\/a>.",
		"notfound"	: "Page not found",
		"noroute"	: "No route defined",
		"badmethod"	: "Method not allowed",
		"nomethod"	: "Method not implemented",
		"denied"	: "Access denied",
		"invalid"	: "Invalid request",
		"codedetect"	: "Server-side code detected",
		"expired"	: "This form has expired",
		"toomany"	: "Too many requests"
		"namereq"	: "Name is required",
		"nameinv"	: "Name is invalid",
		"nameexists"	: "User already exists",
		"passreq"	: "Password is required",
		"passinv"	: "Password is invalid",
		"passmatch"	: "Passwords must match",
		"loginfail"	: "Login unsuccessful",
		"loginwait"	: "Login unsuccessful, please wait a few minutes before trying again",
		"registerwait"	: "Please wait a few minutes before trying to register again",
		"messagereq"	: "Message is required",
		"messageinv"	: "Message is invalid"
	}
}' );-- --

-- Default settings
INSERT INTO settings( id, label, info ) 
VALUES ( 1, 'default_site_settings', '{ 
	"page_title" : "Rustic Cyberpunk",
	"page_sub" : "Coffee. Code. Cabins.",
	"timezone" : "America/New_York",
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
	"default_secpolicy" : {
		"content-security-policy": {
			"default-src"			: "''none''",
			"img-src"			: "*",
			"base-uri"			: "''self''",
			"style-src"			: "''self''",
			"script-src"			: "''self''",
			"font-src"			: "''self''",
			"form-action"			: "''self''",
			"frame-ancestors"		: "''self''",
			"frame-src"			: "*",
			"media-src"			: "''self''",
			"connect-src"			: "''self''",
			"worker-src"			: "''self''",
			"child-src"			: "''self''",
			"require-trusted-types-for"	: "''script''"
		},
		"permissions-policy": {
			"accelerometer"			: [ "none" ],
			"camera"			: [ "none" ],
			"fullscreen"			: [ "self" ],
			"geolocation"			: [ "none" ],
			"gyroscope"			: [ "none" ],
			"interest-cohort"		: [],
			"payment"			: [ "none" ],
			"usb"				: [ "none" ],
			"microphone"			: [ "none" ],
			"magnetometer"			: [ "none" ]
		}, 
		"common-policy": [
			"X-XSS-Protection: 1; mode=block",
			"X-Content-Type-Options: nosniff",
			"X-Frame-Options: SAMEORIGIN",
			"Referrer-Policy: no-referrer, strict-origin-when-cross-origin"
		]
	},
	"tag_white" : {
		"p"		: [ "style", "class", "align", 
					"data-pullquote", "data-video", 
					"data-media", "data-highlight", 
					"data-feature" ],
	
		"div"		: [ "style", "class", "align", "data-highlight", 
					"data-feature" ],
		"span"		: [ "style", "class", "data-highlight", 
					"data-feature", "data-validation" ],
		"br"		: [ "style", "class", "data-feature" ],
		"hr"		: [ "style", "class", "data-feature" ],
		
		"h1"		: [ "style", "class", "data-highlight", "data-feature" ],
		"h2"		: [ "style", "class", "data-highlight", "data-feature" ],
		"h3"		: [ "style", "class", "data-highlight", "data-feature" ],
		"h4"		: [ "style", "class", "data-highlight", "data-feature" ],
		"h5"		: [ "style", "class", "data-highlight", "data-feature" ],
		"h6"		: [ "style", "class", "data-highlight", "data-feature" ],
		
		"strong"	: [ "style", "class", "data-highlight", "data-feature" ],
		"em"		: [ "style", "class", "data-highlight", "data-feature" ],
		"u"	 	: [ "style", "class", "data-highlight", "data-feature" ],
		"strike"	: [ "style", "class", "data-highlight", "data-feature" ],
		"del"		: [ "style", "class", "cite", "data-highlight", "data-feature" ],
		
		"ol"		: [ "style", "class", "data-feature" ],
		"ul"		: [ "style", "class", "data-feature" ],
		"li"		: [ "style", "class", "data-highlight", "data-feature" ],
		
		"code"		: [ "style", "class", "data-highlight", "data-feature" ],
		"pre"		: [ "style", "class", "data-highlight", "data-feature" ],
		
		"sup"		: [ "style", "class", "data-highlight", "data-feature" ],
		"sub"		: [ "style", "class", "data-highlight", "data-feature" ],
		
		"a"		: [ "style", "class", "rel", 
					"title", "href", "data-highlight", "data-feature" ],
		"img"		: [ "style", "class", "src", "height", "width", "alt", 
					"longdesc", "title", "hspace", "vspace", "srcset", 
					"sizes" "data-srcset", "data-src", "data-sizes", 
					"data-feature" ],
		"figure"	: [ "style", "class", "data-highlight", "data-feature" ],
		"figcaption"	: [ "style", "class", "data-highlight", "data-feature" ],
		"picture"	: [ "style", "class", "data-highlight", "data-feature" ],
		"table"		: [ "style", "class", "cellspacing", "cellpadding", 
					"border-collapse", "data-feature" ],
		
		"thead"		: [ "style", "class", "data-highlight", "data-feature" ],
		"tbody"		: [ "style", "class", "data-highlight", "data-feature" ],
		"tfoot"		: [ "style", "class", "data-highlight", "data-feature" ],
		"tr"		: [ "style", "class", "data-feature" ],
		"td"		: [ "style", "class", "colspan", "rowspan", 
					"data-highlight", "data-feature" ],
		"th"		: [ "style", "class", "scope", "colspan", "rowspan", 
					"data-highlight", "data-feature" ],
		
		"caption"	: [ "style", "class", "data-highlight", "data-feature" ],
		"col"		: [ "style", "class", "data-feature" ],
		"colgroup"	: [ "style", "class", "data-feature" ],
		
		"summary"	: [ "style", "class", "data-highlight", "data-feature" ],
		"details"	: [ "style", "class", "data-highlight", "data-feature" ],
		
		"q"		: [ "style", "class", "cite", "data-highlight", "data-feature" ],
		"cite"		: [ "style", "class", "data-highlight", "data-feature" ],
		"abbr"		: [ "style", "class", "data-highlight", "data-feature" ],
		"blockquote"	: [ "style", "class", "cite", "data-highlight", "data-feature" ],
		"body"		: []
	}, 
	"form_white" : {
		"form"		: [ "id", "method", "action", "enctype", "style", "class", 
					"data-feature" ], 
		"input"		: [ "id", "type", "name", "required", , "max", "min", 
					"value", "size", "maxlength", "checked", "pattern", 
					"disabled", "style", "class", "data-highlight", 
					"aria-describedby", "data-feature" ],
		"label"		: [ "id", "for", "style", "class", "data-highlight", "data-feature" ], 
		"textarea"	: [ "id", "name", "required", "rows", "cols", "style", "class", 
					"aria-describedby", "data-highlight", "data-feature" ],
		"select"	: [ "id", "name", "required", "multiple", "size", "disabled", 
					"style", "class", "aria-describedby", "data-highlight", 
					"data-feature" ],
		"option"	: [ "id", "value", "disabled", "style", "class", "data-feature" ],
		"optgroup"	: [ "id", "label", "style", "class", "data-feature" ]
	}, 
	"ext_whitelist" : {
		"text"		: "css, js, txt, html",
		"images"	: "ico, jpg, jpeg, gif, bmp, png, tif, tiff, svg", 
		"fonts"		: "ttf, otf, woff, woff2",
		"audio"		: "ogg, oga, mpa, mp3, m4a, wav, wma, flac",
		"video"		: "avi, mp4, mkv, mov, ogg, ogv"
	}, 
	"route_mark" : {
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
	}
}' );-- --


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
INSERT INTO pages( id, site_id, ptype, is_home ) 
VALUES( 1, 1, 'html', 1 );-- --

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
);-- -- 



-- Rendering and styles

-- Default style template
INSERT INTO styles( id, label, description ) 
VALUES ( 1, 'default', 'Base PubCabin style' );-- --

-- Default template renders (must not contain any vertical pipes "|" )
INSERT INTO style_templates( style_id, label, render ) 
VALUES 
( 1, 'tpl_skeleton', '<!DOCTYPE html>
<html lang="{lang}">
<head>
	{head}
</head>
<body class="{body_classes}" {extra}>
	{body}
</body>
</html>' ), 

( 1, 'tpl_skeleton_title', '<title>{title}</title>' ), 

( 1, 'tpl_rel_tag', '<link rel="{rel}" type="{type}" title="{title}" href="{url}">' ),
( 1, 'tpl_rel_tag_nt', '<link rel="{rel}" href="{url}">' ), 

( 1, 'tpl_style_tag', '<link rel="stylesheet" href="{url}">' ), 
( 1, 'tpl_meta_tag', '<meta name="{name}" content="{content}">' ), 
( 1, 'tpl_script_tag', '<script src="{url}"></script>' ), 

( 1, 'tpl_anchor', '<a href="{url}">{text}</a>' ), 
( 1, 'tpl_para', '<p {extra}>{html}</p>' ), 
( 1, 'tpl_span', '<span {extra}>{html}</span>' ), 
( 1, 'tpl_div', '<div {extra}>{html}</div>' ), 
( 1, 'tpl_main', '<main {extra}>{html}</main>' ), 
( 1, 'tpl_article', '<article {extra}>{html}</article>' ), 
( 1, 'tpl_header', '<header {extra}>{html}</header>' ), 
( 1, 'tpl_aside', '<aside {extra}>{html}</aside>' ), 
( 1, 'tpl_footer', '<footer {extra}>{html}</footer>' ), 

( 1, 'tpl_full_page', '<!DOCTYPE html>
<html lang="{lang}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="alternate" type="application/xml" title="{feed_title}" href="{feedlink}">
<title>{page_title}</title>
{after_title}
{stylesheets}
{meta_tags}
</head>
<body class="{body_classes}" {extra}>
{body_before}
{body}
{body_after}
{body_before_lastjs}
{body_js}
{body_after_lastjs}
</body>
</html>' ), 

( 1, 'tpl_error_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{lang:errors:error} {code} - {page_title}</title>
<link rel="stylesheet" href="{home}style.css">
</head>
<body>
<header>
<div class="content">
	<h1><a href="{home}">{page_title}</a></h1>
	<p>{tagline}</p>
</div>
</header>
<main>
<div class="content">
{body}
<p>{lang:errors:returnhome}</p>
</div>
</main>
</body>
</html>' ), 

( 1, 'tpl_home_body', '<div class="{home_classes}">
<article class="{home_wrap_classes}">
{body}
</article>
</div>' ), 

( 1, 'tpl_about_body', '<div class="{about_classes}">
<article class="{about_wrap_classes}">
{body}
</article>
</div>' ), 

( 1, 'tpl_page_footer', '<footer class="{footer_classes}">
<div class="{footer_wrap_classes}">{footer_links}</div>
</footer>' ), 

( 1, 'tpl_page_heading', '{before_page_heading}<header class="{heading_classes}">
<div class="{heading_wrap_classes}">
{heading_before}
<h1 class="{heading_h_classes}">
	<a href="{home}" class="{heading_a_classes}">{page_title}</a>
</h1>
<p class="{tagline_classes}">{tagline}</p>
{main_links}
<div class="{search_form_wrap_classes}">{search_form}</div>
{heading_after}
</div>
</header>{after_page_heading}' ), 

( 1, 'tpl_home_heading', '{before_home_heading}<header class="{heading_classes}">
<div class="{heading_wrap_classes}">
<h1 class="{heading_h_classes}">
	<a href="{home}" class="{heading_a_classes}">{page_title}</a>
</h1>
<p class="{tagline_classes}">{tagline}</p>
{home_links}
<div class="{search_form_wrap_classes}">{search_form}</div>
{heading_after}
</div>
</header>{after_home_heading}' ), 

( 1, 'tpl_about_heading', '{before_about_heading}<header class="{heading_classes}">
<div class="{heading_wrap_classes}">{before_heading_h}
<h1 class="{heading_h_classes}">
	<a href="{home}" class="{heading_a_classes}">{page_title}</a>
</h1>{after_heading_h}
<p class="{tagline_classes}">{tagline}</p>
{about_links}
<div class="{search_form_wrap_classes}">{search_form}</div>
{heading_after}
</div>
</header>{after_about_heading}' ), 

( 1, 'tpl_input_xsrf', '{before_input_xsrf}
<input type="hidden" name="nonce" value="{nonce}">
<input type="hidden" name="token" value="{token}">
<input type="hidden" name="meta" value="{meta}">
{after_input_xsrf}' ), 

( 1, 'tpl_searchform', '{before_search_form}<form action="{home}" method="get" 
	class="{form_classes} {search_form_classes}">
	<fieldset class="{search_fieldset_classes}">
{xsrf}
{before_search_input}<input type="search" name="find" 
	placeholder="{lang:forms:search:placeholder}" 
	class="{input_classes} {search_input_classes}" 
	required>{after_search_input} 
{before_search_button}
<input type="submit" class="{submit_classes} {search_button_classes}" 
	value="{lang:forms:search:button}">{after_search_button}
	</fieldset>
</form>{after_search_form}' ), 

( 1, 'tpl_post', '{before_post}
<article class="{post_classes}">{before_full_post}
	<div class="{post_wrap_classes}">{before_post_heading}
	<header class="{post_heading_classes}">
	<div class="{post_heading_wrap_classes}">
		<h2 class="{post_heading_h_classes}">
			<a href="{permalink}" class="{post_heading_a_classes}">{title}</a>
		</h2>
		<time datetime="{date_utc}"
			class="{post_pub_classes}">{date_stamp}</time> {read_time}
	</div>
	</header>{before_post_body}
	<div class="{post_body_wrap_classes}">
		<div class="{post_body_content_classes">{body}</div>
		<div class="{post_body_tag_classes}">{tags}</div>
	</div>{after_post_body}
	</div>{after_full_post}
</article>{after_post}'),

( 1, 'tpl_index_post', '{before_index_post}
<article class="{post_idx_wrap_classes}">{before_item_post}
	<div class="{post_idx_wrap_classes}">{before_index_post_heading}
	<header class="{post_idx_heading_classes}">
	<div class="{post_idx_heading_wrap_classes}">
		<h2 class="{post_idx_heading_h_classes}">
			<a href="{permalink}" class="{post_idx_heading_a_classes}">{title}</a>
		</h2>
		<time datetime="{date_utc}"
			class="{post_idx_pub_classes}">{date_stamp}</time> {read_time}
	</div>
	</header>{after_index_post_heading}
	<div class="{post_idx_body_wrap_classes}">
		<div class="{post_idx_body_content_classes}">{body}</div>
		<div class="{post_idx_body_tag_classes}">{tags}</div>
	</div>
	</div>
{after_item_post}</article>{after_index_post}' ), 

( 1, 'tpl_read_time', '<span class="readtime">{lang:headings:readtime}</span>' ), 

( 1, 'tpl_index_tagwrap', '<nav class="{tag_index_wrap_classes}">
	<span class="{tag_index_heading_classes}">{lang:headings:tags}</span> 
	<ul class="{tag_index_ul_classes}">{tags}</ul></nav>' ),

( 1, 'tpl_tagwrap', '<nav class="{tag_wrap_classes}">
	<span class="{tag_heading_classes}">{lang:headings:tags}</span> 
	<ul class="{tag_ul_classes}">{tags}</ul></nav>' ), 

( 1, 'tpl_input_nd', '{input_field_before}<input id="{id}" name="{name}" type="{type}" 
	placeholder="{placeholder}" class="{input_classes}" 
	{required}{extra}>{input_field_after}' ),

( 1, 'tpl_input_field', '{input_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}' ), 

( 1, 'tpl_input_field_nd', '{label_before}<label for="{id}" 
	class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input}' ),

( 1, 'tpl_input_textarea', '{input_before}{input_multiline_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<textarea id="{id}" name="{name}" rows="{rows} cols="{cols}" 
	placeholder="{placeholder}" aria-describedby="{id}-desc"
	 class="{input_classes}" {required}{extra}>{value}</textarea>{input_field_after}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
{input_multiline_after}{input_after}' ),

( 1, 'tpl_input_select_opt', '<option value="{value}" {selected}>{text}</option>' ),

( 1, 'tpl_input_select', '{input_before}{input_select_before}{label_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
<select id="{id}" name="{name}" aria-describedby="{id}-desc"
	class="{input_classes}" {required}{extra}>
	{unselect_option}{options}</select>
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}
{input_select_after}{input_after}' ),

( 1, 'tpl_input_unselect', '<option value="">--</option>' ),

( 1, 'tpl_input_file', '{input_before}{input_upload_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<input id="{id}" name="{name}" type="file" 
	placeholder="{placeholder}" class="{input_classes}" 
	aria-describedby="{id}-desc" {required}{extra}>{input_field_after}
{desc_before}<small id="{id}-desc" class="{desc_classes}" 
	{desc_extra}>{desc}</small>{desc_after}{input_after}
{input_upload_after}{input_after}' ),

( 1, 'tpl_input_file_nd', '{input_before}{input_upload_before}
{label_before}<label for="{id}" class="{label_classes}">{label}
	{special_before}<span class="{special_classes}"
	>{special}</span>{special_after}</label>{label_after} 
{input_field_before}<input id="{id}" name="{name}" type="file" 
	placeholder="{placeholder}" aria-describedby="{id}-desc" 
	class="{input_classes}" 
	{required}{extra}>{input_field_after}{input_upload_after}{input_after}' ),

( 1, 'tpl_data_pfx', 'data-{term}="{value}"' ), 

( 1, 'tpl_breadcrumbs', '{crumbs_before}
<nav class="{crumb_classes}">
<ul class="{crumb_wrap_classes}">{crumbs_links_before}{links}{crumbs_links_before}</ul>
</nav>{crumbs_after}' ), 

( 1, 'tpl_sub_breadcrumbs', '{crumbs_sub_before}<nav class="{crumb_sub_classes}">
{crumbs_sub_ul_before}
<ul class="{crumb_sub_wrap_classes}">{links}</ul>{crumbs_sub_ul_after}
</nav>{crumbs_sub_after}' ), 

( 1, 'tpl_crumb_link', '<li class="{crumb_item_classes}">{crumb_link_before}
<a href="{url}" class="{crumb_link_classes}">{label}</a>{crumb_link_after}
</li>' ), 

( 1, 'tpl_crumb_current','<li class="{crumb_current_classes}">{crumb_current_before}
<span class="{crumb_current_item}" title="{url}">{label}</span>{crumb_current_after}
</li>' ), 

( 1, 'tpl_page_current_link', '<li class="{nav_current_classes}"><span 
class="{nav_current_s_classes}" title="{url}">{text}</span></li>' ), 

( 1, 'tpl_page_prev_link', '<li class="{nav_prev_classes}"><a 
href="{url}" class="{nav_prev_a_classes}">Previous</a></li>' ), 

( 1, 'tpl_page_noprev','<li class="{nav_noprev_classes}"><span 
class="{nav_noprev_s_classes}">Previous</span></li>' ), 

( 1, 'tpl_page_next_link','<li class="{nav_next_classes}"><a 
href="{url}" class="{nav_next_a_classes}">Next</a></li>' ), 

( 1, 'tpl_page_nonext', '<li class="{nav_nonext_classes}"><span 
class="{nav_nonext_s_classes}">Next</span></li>' ), 

( 1, 'tpl_page_first2', '<li class="{nav_first1_classes}"><a href="{url1}" class="{nav_first1_a_classes}">{text1}</a></li>
	<li class="{nav_first2_classes}"><a href="{url2}" class="{nav_first2_a_classes}">{text2}</a></li>
	<li class="{nav_first_s_classes}">...</li>' ), 

( 1, 'tpl_page_last2','<li class="{nav_last_s_classes}">...</li>
	<li class="{nav_last1_classes}"><a href="{url1}" class="{nav_last1_a_classes}">{text1}</a></li>
	<li class="{nav_last2_classes}"><a href="{url2}" class="{nav_last2_a_classes}">{text2}</a></li>' ), 

( 1, 'tpl_page_list', '<div class="{list_wrap_classes}">
	<h3 class="list_h_classes">{heading}</h3>
	<nav class="{list_classes}"><ul>{links}</ul></nav>
</div>' ), 

( 1, 'tpl_pagination', '<div class="{pagination_wrap_classes}">
<nav class="{pagination_classes}">
<ul class="{pagination_ul_classes}">{links}</ul>
</nav>
</div>' ), 

( 1, 'tpl_id_field', '<input type="hidden" name="id" value="{id}">' ), 

( 1, 'tpl_input_submit', '{input_before}{input_submit_before}<input 
	type="submit" id="{id}" name="{name}" value="{value}" 
	class="{submit_classes}" {extra}>{input_submit_after}{input_after}' ),

( 1, 'tpl_input_button', '{input_before}{input_button_before}<input 
	type="button" id="{id}" name="{name}" value="{value}" 
	class="{button_classes}" {extra}>{input_button_after}{input_after}' ), 

( 1, 'tpl_input_submit_alt', '{input_before}{input_submit_before}{input_submit_alt_before}<input 
	type="submit" id="{id}" name="{name}" value="{value}" class="{alt_classes}" 
	{extra}>{input_submit_after}{input_submit_alt_after}{input_after}' ), 

( 1, 'tpl_input_submit_warn', '{input_before}{input_warn_before}<input type="submit" name="{name}" 
	value="{value}" class="{warn_classes}" 
	{extra}>{input_warn_after}{input_after}' ), 

( 1, 'tpl_input_submit_action', '{input_before}{input_action_before}<input type="submit" name="{name}" 
	value="{value}" class="{action_classes}" 
	{extra}>{input_action_after}{input_after}' ), 

( 1, 'tpl_form_block', '{form_before}{form_block_before}
<form id="{id}" action="{action}" method="{method}" enctype="{enctype}" 
	class="{form_classes}" 
	{extra}>{form_input_before}{fields}{form_input_after}</form>
{form_block_after}{form_after}' ), 

( 1, 'tpl_form', '{form_before}{form_inline_before}
<form id="{form_classes}" method="{method}" action="{action}" 
	enctype="{enctype}" accept-charset="UTF-8" 
	{extra}>{form_input_before}{fields}{form_input_after}</form>
{form_inline_after}{form_after}' ), 

( 1, 'tpl_form_fieldset', '{input_fieldset_before}<fieldset 
	class="{fieldset_classes}">{input}</fieldset>{input_fieldset_after}' ), 

( 1, 'tpl_form_input_wrap', '{input_wrap_before}<p 
class="{input_wrap_classes}">{input}</p>{input_wrap_after}' ), 

( 1, 'tpl_form_button_wrap', '{button_wrap_before}<p 
class="{button_wrap_classes}">{buttons}</p>{button_wrap_after}' ), 

( 1, 'tpl_picture', '{picture_wrap_before}<figure 
class="{picture_wrap_classes}">{picture}
	<figcaption class="{picture_caption_classes}">{caption}</figcaption>
</figure>{picture_wrap_after}' ), 

( 1, 'tpl_picture_nd', '{picture_wrap_before}<figure 
class="{picture_wrap_classes}">{picture}</figure>{picture_wrap_after}' ), 

( 1, 'tpl_gallery', '{gallery_wrap_before}<div 
class="{gallery_wrap_classes}">{pictures}</div>{gallery_wrap_after}' );-- --



