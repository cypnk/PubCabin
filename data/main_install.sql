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
( 26, 'uk', 'украї́нська мо́ва', 'Ukranian', 0 ),
( 27, 'ur', 'اُردُو‬', 'Urdu', 0 ),
( 28, 'vi', 'Tiếng Việt', 'Vietnamese', 0 ),
( 29, 'zh', '中文', 'Chinese', 0 );-- --

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
		},
		"captcha"	: {
	  		"title"		: "Captcha",
	  		"titledesc"	: "Copy the text in the image shown",
	  		"msg"		: "This field is required to continue",
	  		"msgdesc"	: "<a href=\"{accessibility\"}>Accessibility options<\/a>.",
			"alt"		: "captcha"
		}, 
		"anonpost"	: {
			"page"		: "Create message",
			"title"		: "Message title",
			"titledesc"	: "Between {title_min} and {title_max} characters. Letters, numbers, and spaces supported.",
			"name"		: "Name <span>(optional)<\/span>",
			"namedesc"	: "Between {name_min} and {name_max} characters. Letters, numbers, and spaces supported. Name#secret format supported.",
			"msg"		: "Message <span>(required)<\/span>",
			"msgdesc"	: "Simple HTML and a subset of <a href=\"{formatting}\">Markdown<\/a> supported.",
			"submit"	: "Post"
		},
		"userpost"	: {
			"page"		: "Create message",
			"title"		: "Message title",
			"titledesc"	: "Between {title_min} and {title_max} characters. Letters, numbers, and spaces supported.",
			"name"		: "Posting as <a href=\"{userlink}\">{username}<\/a>",
			"msg"		: "Message <span>(required)<\/span>",
			"msgdesc"	: "Simple HTML and a subset of <a href=\"{formatting}\">Markdown<\/a> supported.",
			"submit"	: "Post"
		},
		"editpost"	: {
			"page"		: "Edit message",
			"title"		: "Message title",
			"titledesc"	: "Between {title_min} and {title_max} characters. Letters, numbers, and spaces supported.",
			"msg"		: "Editing Message <span>(required)<\/span>",
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
				"frame_whitelist" : "Whitelist of embeddable URLs",
				"cache_ttl" : "Default cache duration",
				"site_depth" : "Sub website base directory limit",
				"folder_limit" : "Sub directory path depth",
				"max_search_words" : "Maximum number of words in search phrases", 
				"app_start" : "Content start date in UTC format",
				"app_name" : "Main application name",
				"skip_local" : "Prevent local IPs from connecting (Warning: This may lock you out if hosting locally)"
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
	}, 
	"mod"		: {
		"usercontent" : {
			"select"	: "Select",
			"IP"		: "IP {ip}"
		}
	},
	"ptypes": {
		"page"		: "Page",
	  	"pagedesc"	: "Full page content such as Home, About or other main section",
	  	"blog"		: "Blog",
	  	"blogdesc"	: "A date-archived journal",
	  	"forum"		: "Forum",
	  	"forumdesc"	: "Shared subject for community discussion topics",
	  	"forumtopic"	: "Forum Topic",
	  	"forumtopicdesc": "Indexed discussion thread relevant to the forum subject"
	},
	"roles" : {
		"admin": {
			"label"	: "Admin",
			"desc"	: "Global administrator"
		},
		"siteadmin": {
			"label"	: "Site Admin",
			"desc"	: "Website administrator"
		},
		"sitemod": {
			"label"	: "Site Moderator",
			"desc"	: "Website user content moderator"
		}
	}
}' );-- --

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
	"name_min": 1,
	"name_max": 180,
	"pass_min": 7,
	"display_min": 3,
	"display_max": 180,
	"enable_register": 1,
	"auto_approve_reg": 1,
	"title_min": 3,
	"title_max": 255,
	"cache_ttl": 3200,
	"site_depth": 25,
	"max_search_words": 10,
	"token_bytes": 8,
	"nonce_hash": "tiger160,4",
	"style_limit": 20,
	"script_limit": 10,
	"meta_limit": 15,
	"folder_limit": 15,
	"shared_assets": "\/",
	"default_stylesheets": [],
	"default_scripts": [],
	"default_meta": {
		"meta" : [
			{ "name" : "generator", "content" : 
				"Bare; https:\/\/github.com\/cypnk\/PubCabin" }
			]
	},
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
		"text"		: "css, js, txt, html, vtt",
		"images"	: "ico, jpg, jpeg, gif, bmp, png, tif, tiff, svg, webp", 
		"fonts"		: "ttf, otf, woff, woff2",
		"audio"		: "ogg, oga, mpa, mp3, m4a, wav, wma, flac",
		"video"		: "avi, mp4, mkv, mov, ogg, ogv",
		"documents"	: "doc, docx, ppt, pptx, pdf, epub",
		"archives"	: "zip, rar, gz, tar"
	}, 
	"route_mark" : {
		"*"	: "(?<all>.+)",
		":id"	: "(?<id>[1-9][0-9]*)",
		":ids"	: "(?<ids>[1-9][0-9,]*)",
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
		":user"	: "(?<user>[\\pL\\pN\\s_\\-]{1,80})",
		":slug"	: "(?<slug>[\\pL\\-\\d]{1,100})",
		":tree"	: "(?<tree>[\\pL\\/\\-\\d]{1,255})",
		":file"	: "(?<file>[\\pL_\\-\\d\\.\\s]{1,120})",
		":find"	: "(?<find>[\\pL\\pN\\s\\-_,\\.\\:\\+]{2,255})",
		":redir": "(?<redir>[a-z_\\:\\/\\-\\d\\.\\s]{1,120})"
	},
	"session_exp": 300,
	"session_bytes": 12,
	"session_limit_count": 5,
	"session_limit_medium": 3,
	"session_limit_heavy": 1,
	"cookie_exp": 86400,
	"cookie_path": "\/",
	"cookie_restrict": 1,
	"form_delay": 30,
	"form_expire": 7200,
	"login_delay": 5,
	"login_attempts": 3
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
}' ),
( 2, 'blog', '', '{
	"parents":	["*", "page"],
	"children":	["post"],
 	"label:		"{lang:ptypes:blog}",
 	"description":	"{lang:ptypes:blogdesc}",
 	"allow_children": 0,
 	"allow_comments": 0,
 	"privileges"	: {
		"create_roles"	: [],
		"edit_roles"	: [],
		"delete_roles"	: [],
		"upload_roles"	: [],
		"mod_roles"	: [],
		"comment_roles"	: []
	},
	"settings"	: {}
}' ),
( 3, 'post', '', '{
	"parents":	["blog"],
	"children":	[],
 	"label":	"{lang:ptypes:post}",
 	"description":	"{lang:ptypes:postdesc}",
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
}' ),
( 4, 'forum', '', '{
	"parents":	["*"],
	"children":	["forum", "forumtopic"],
 	"label":	"{lang:ptypes:forum}",
 	"description":	"{lang:ptypes:forumdesc}",
 	"allow_children": 1,
 	"allow_comments": 0,
 	"privileges"	: {
		"create_roles"	: [],
		"edit_roles"	: [],
		"delete_roles"	: [],
		"upload_roles"	: [],
		"mod_roles"	: [],
		"comment_roles"	: []
	},
	"settings"	: {}
}' ),
( 5, 'forumtopic', '', '{
	"parents":	["forum"],
	"children":	[],
 	"label":	"{lang:ptypes:forumtopic}",
 	"description":	"{lang:ptypes:forumtopicdesc}",
 	"allow_children": 0,
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

( 1, 'tpl_login_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{lang:forms:login:page}</title>
{after_title}
{stylesheets}
{meta_tags}
<meta name="robots" content="noindex, nofollow">
{login_page_head}
</head>
<body class="{body_classes} {login_page_body_classes}">{login_page_body}
<main class="{body_main_classes} {login_page_main_classes}">{login_form_before}
<form action="{action}" method="post" class="{form_classes} {login_form_classes}" id="login_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{login_name_label_before}<label for="loginuser" class="{label_classes}">{lang:forms:login:name}</label>{login_name_label_after}
		{login_name_input_before}<input id="loginuser" type="text" class="{input_classes}" aria-describedby="loginuser-desc" name="username" maxlength="{name_max}" pattern="([^\s][\w\s]{{name_min},{name_max}})" required>{login_name_input_after}
		{login_name_desc_before}<small id="loginuser-desc" class="{desc_classes}">{lang:forms:login:namedesc}</small>{login_name_desc_after}
	</p>
	<p>
		{login_pass_label_before}<label for="loginpass" class="{label_classes}">{lang:forms:login:pass}</label>{login_pass_label_after}
		{login_pass_input_before}<input id="loginpass" type="password" class="{input_classes}" aria-describedby="loginpass-desc" name="password" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{login_pass_input_after}
		{login_pass_desc_before}<small id="loginpass-desc" class="{desc_classes}">{lang:forms:login:passdesc}</small>{login_pass_desc_after}
	</p>
	<p>{login_rem_label_before}<label class="ib">{login_rem_input_before}<input type="checkbox" name="rem" value="1">{login_rem_input_after} {lang:forms:login:rem}</label>{login_rem_label_after}</p>
	<p><input type="submit" class="{submit_classes}" value="{lang:forms:login:submit}"></p>
</form>{login_form_after}
</main>
</body>
</html>' ), 

( 1, 'tpl_register_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{lang:forms:register:page}</title>
{after_title}
{stylesheets}
{meta_tags}
<meta name="robots" content="noindex, nofollow">
{register_page_head}
</head>
<body class="{body_classes} {register_page_body_classes}">{register_page_body}
<main class="{body_main_classes} {register_page_main_classes}">{register_form_before}
<form action="{action}" method="post" class="{form_classes} {register_form_classes}" id="register_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{register_name_label_before}<label for="registername" class="{label_classes}">{lang:forms:register:name}</span></label>{register_name_label_after}
		{register_name_input_before}<input id="registername" type="text" class="{input_classes}" aria-describedby="registername-desc" name="username" maxlength="{name_max}" pattern="([^\s][\w\s]{{name_min},{name_max}})" required>{register_name_input_after}
		{register_name_desc_before}<small id="registername-desc" class="{desc_classes}">{lang:forms:register:namedesc}</small>{register_name_desc_before}
	</p>
	<p>
		{register_pass_label_before}<label for="registerpass" class="{label_classes}">{lang:forms:register:pass}</span></label>{register_pass_label_after}
		{register_pass_input_before}<input id="registerpass" type="password" class="{input_classes}" aria-describedby="registerpass-desc" name="password" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{register_pass_input_after}
		{register_pass_desc_before}<small id="registerpass-desc" class="{desc_classes}">{lang:forms:register:passdesc}</small>{register_pass_desc_after}
	</p>
	<p>
		{register_passr_label_before}<label for="passrepeat" class="{label_classes}">{lang:forms:register:repeat}</span></label>{register_passr_label_after}
		{register_passr_input_before}<input id="passrepeat" type="text" class="{input_classes}" aria-describedby="passrepeat-desc" name="password2" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{register_passr_input_after}
		{register_passr_desc_before}<small id="passrepeat-desc" class="{desc_classes}">{lang:forms:register:repeatdesc}</small>{register_passr_desc_after}
	</p>
	<p>
		{register_terms_label_before}<label class="ib right">{register_terms_input_before}<input type="checkbox" name="terms" value="1" required>{register_terms_input_after} {lang:forms:register:terms}</label>{register_terms_label_after} 
		{register_rem_label_before}<label class="ib right">{register_rem_input_before}<input type="checkbox" name="rem" value="1">{register_rem_input_after} {lang:forms:register:rem}</label>{register_rem_label_after} 
		<input type="submit" class="{submit_classes}" value="{lang:forms:register:submit}"></p>
</form>{register_form_after}
</main>
</body>
</html>' ), 

( 1, 'tpl_password_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{lang:forms:password:page}</title>
{after_title}
{stylesheets}
{meta_tags}
<meta name="robots" content="noindex, nofollow">
{password_page_head}
</head>
<body class="{body_classes} {password_page_body_classes}>{password_page_body}
<main class="{body_main_classes} {password_page_main_classes}">{password_form_before}
<form action="{action}" method="post" class="{form_classes} {password_form_classes}" id="password_form">
	<input type="hidden" name="id" value="{id}">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<input type="hidden" name="meta" value="{meta}">
	<p>
		{oldpass_label_before}<label for="oldpass" class="{label_classes}">{lang:forms:password:old}</span></label>{oldpass_label_after} 
		{oldpass_input_before}<input id="oldpass" type="password" class="{input_classes}" aria-describedby="oldpass-desc" name="password" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{oldpass_input_after}
		{oldpass_desc_before}<small id="oldpass-desc" class="{desc_classes}">{lang:forms:password:olddesc}</small>{oldpass_desc_after}
	</p>
	<p>
		{newpass_label_before}<label for="newpass">{lang:forms:password:new}</span></label>{newpass_label_after} 
		{newpass_input_before}<input id="newpass" type="text" class="{input_classes}" aria-describedby="newpass-desc" name="password2" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{newpass_input_after}
		{newpass_desc_before}<small id="newpass-desc" class="{desc_classes}">{lang:forms:password:newdesc}</small>{newpass_desc_after}
	</p>
	<p><input type="submit" class="{submit_classes}" value="{lang:forms:password:submit}"></p>
</form>{password_form_after}
</main>
</body>
</html>' ), 

( 1, 'tpl_profile_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{lang:forms:profile:page}</title>
{after_title}
{stylesheets}
{meta_tags}
<meta name="robots" content="noindex, nofollow">
{profile_page_head}
</head>
<body class="{body_classes} {profile_page_body_classes}>{profile_page_body}
<main class="{body_main_classes} {profile_page_main_classes}">{profile_form_before}
<form action="{action}" method="post" class="{form_classes} {profile_form_classes}" id="profile_form">
	<input type="hidden" name="id" value="{id}">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<input type="hidden" name="meta" value="{meta}">
	<p>
		{profile_name_label_before}<label for="loginuser" class="{label_classes}">{lang:forms:profile:name}</label>{profile_name_label_after} 
		{profile_name_input_before}<input id="loginuser" type="text" value="{username}" class="{input_classes}" disabled>{profile_name_input_after}
	</p>
	<p>
		{display_label_before}<label for="display" class="{label_classes}">{lang:forms:profile:display}</span></label>{display_label_after} 
		{display_input_before}<input id="display" type="text" class="{input_classes}" aria-describedby="display-desc" name="display" maxlength="{display_max}" pattern="([^\s][A-z0-9À-ž\s]+){{display_min},{display_max}}" value="{display}">{display_input_after}
		{display_desc_before}<small id="display-desc" class="{desc_classes}">{lang:forms:profile:displaydesc}</small>{display_desc_after}
	</p>
	<p>
		{bio_label_before}<label for="bio" class="{label_classes}">{lang:forms:profile:bio}</span></label>{bio_label_after} 
		{bio_input_before}<textarea id="bio" name="bio" rows="3" cols="60" class="{input_classes}" aria-describedby="bio-desc">{bio}</textarea>{bio_input_after} 
		{bio_desc_before}<small id="bio-desc" class="{desc_classes}">{lang:forms:profile:biodesc}</small>{bio_desc_after}
	</p>
	<p><input type="submit" class="{submit_classes}" value="{lang:forms:profile:submit}"></p>
</form>{profile_form_after}
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

( 1, 'tpl_post_item', '{before_post_item}<li 
	class="{post_index_item_classes}">{before_post_item_full}<time 
	class="{post_datetime_classes}" datetime="{date_utc}">{date_stamp}</time>
	<a class="{post_index_item_link_classes}" href="{permalink}"
	>{title}</a>{after_post_item_full}</li>{after_post_item}'
),
 
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
		<div class="{post_body_content_classes}">{body}</div>
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

( 1, 'tpl_page_link','<li class="{nav_classes}"><a 
href="{url}" class="{nav_a_classes}">{text}</a></li>' ), 

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
	<h3 class="{list_h_classes}">{heading}</h3>
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
class="{gallery_wrap_classes}">{pictures}</div>{gallery_wrap_after}' ),

( 1, 'tpl_user_comment', '<article class="{comment_classes}">
	<header>
		<time datetime="{date_utc}">{date_nice}</time>
		<address><a href="{author_link}">{author}</a></address>
	</header>
	<section>{body}</section>
</article>' ), 

( 1, 'tpl_anon_comment', '<article class="{comment_classes}">
	<header>
		<time datetime="{date_utc}">{date_nice}</time>
		<address>{author}</address>
	</header>
	<section>{body}</section>
</article>' ),

( 1, 'tpl_moduser_comment', '<article class="{comment_classes}">
	<header>
		<time datetime="{date_utc}">{date_nice}</time>
		<address><a href="{author_link}">{author}</a></address>
	</header>
	<section>{body}</section>
	<footer>
		<label class="func">
			<input type="checkbox" name="select[]" value="{id}"> 
			{lang:mod:usercontent:select}
		</label>
		{lang:mod:usercontent:ip}
	</footer>
</article>' ), 

( 1, 'tpl_modanon_comment', '<article class="{comment_classes}">
	<header>
		<time datetime="{date_utc}">{date_nice}</time>
		<address>{author}</address>
	</header>
	<section>{body}</section>
	<footer>
		<label class="func">
			<input type="checkbox" name="select[]" value="{id}"> 
			{lang:mod:usercontent:select}
		</label>
		{lang:mod:usercontent:ip}
	</footer>
</article>' ), 

( 1, 'tpl_anonpost_form', '<form action="{action}" method="post" class="{form_classes}" id="post_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{anonpost_name_label_before}<label for="authorname" class="{label_classes}">{lang:forms:anonpost:name}</label>{anonpost_name_label_after}
		{anonpost_name_input_before}<input id="authorname" type="text" class="{input_classes}" aria-describedby="authorname-desc" name="author" maxlength="{name_max}" pattern="([^\s][\w\s]{{name_min},{name_max}})">{anonpost_name_input_before}
		{anonpost_name_desc_before}<small id="authorname-desc" class="{desc_classes}">{lang:forms:anonpost:namedesc}</small>{anonpost_name_desc_before}
	</p>
	<p>
		{anonpost_title_label_before}<label for="postname" class="{label_classes}">{lang:forms:anonpost:title}</label>{anonpost_title_label_after}
		{anonpost_title_input_before}<input id="postname" type="text" class="{input_classes}" aria-describedby="postname-desc" name="title" maxlength="{title_max}" pattern="([^\s][\w\s]{{title_min},{title_max}})">{anonpost_title_input_after}
		{anonpost_title_desc_before}<small id="postname-desc" class="{desc_classes}">{lang:forms:anonpost:titledesc}</small>{anonpost_title_desc_after}
	</p>
	<p>
		{anonpost_message_label_before}<label for="message" class="{label_classes}">{lang:forms:anonpost:msg}</label>{anonpost_message_label_after}
		{anonpost_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>{anonpost_message_input_before}
		{anonpost_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:anonpost:msgdesc}</small>{anonpost_message_desc_after}
	</p>
	<p><label class="ib right"><input type="checkbox" name="terms" value="1" required> Agree to the <a href="{terms}" target="_blank">site terms</a></label> 
		<input type="submit" value="{lang:forms:anonpost:submit}"></p>
</form>' ),

( 1, 'tpl_userpost_form', '<form action="{action}" method="post" class="{form_classes}" id="post_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{userpost_title_label_before}<label for="postname" class="{label_classes}">{lang:forms:userpost:title}</label>{userpost_title_label_after}
		{userpost_title_input_before}<input id="postname" type="text" class="{input_classes}" aria-describedby="postname-desc" name="title" maxlength="{title_max}" pattern="([^\s][\w\s]{{title_min},{title_max}})">{userpost_title_input_after}
		{userpost_title_desc_before}<small id="postname-desc" class="{desc_classes}">{lang:forms:userpost:titledesc}</small>{userpost_title_desc_after}
	</p>
	<p>
		{userpost_message_label_before}<label for="message" class="{label_classes}">{lang:forms:userpost:msg}</label>{userpost_message_label_after}
		{userpost_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>{userpost_message_input_before}
		{userpost_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:userpost:msgdesc}</small>{userpost_message_desc_after}
	</p>
	<p><input type="submit" value="{lang:forms:userpost:submit}"></p>
</form>' ), 

( 1, 'tpl_editpost_form', '<form action="{action}" method="post" class="{form_classes}" id="edit_form">
	<input type="hidden" name="id" value="{id}">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{editpost_title_label_before}<label for="postname" class="{label_classes}">{lang:forms:editpost:title}</label>{editpost_title_label_after}
		{editpost_title_input_before}<input id="postname" type="text" class="{input_classes}" aria-describedby="postname-desc" name="title" maxlength="{title_max}" pattern="([^\s][\w\s]{{title_min},{title_max}})" value="{title}">
		{editpost_title_desc_before}<small id="postname-desc" class="{desc_classes}">{lang:forms:editpost:titledesc}</small>{userpost_title_desc_after}
	</p>
	<p>
		{editpost_message_label_before}<label for="message" class="{label_classes}">{lang:forms:editpost:msg}</label>{editpost_message_label_after}
		{editpost_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>
		{editpost_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:editpost:msgdesc}</small>{editpost_message_desc_after}
	</p>
	<p><input type="submit" value="{lang:forms:editpost:submit}"></p>
</form>' ), 

( 1, 'tpl_anoncomment_form', '<form action="{action}" method="post" class="{form_classes}" id="anon_post_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{anoncomment_message_label_before}<label for="message" class="{label_classes}">{lang:forms:anonpost:msg}</label>{anoncomment_message_label_after}
		{anoncomment_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>{anoncomment_message_input_after}
		{anoncomment_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:anonpost:msgdesc}</small>{anoncomment_message_desc_after}
	</p>
	<p>
		{anoncomment_name_label_before}<label for="postauthor" class="{label_classes}">{lang:forms:anonpost:name}</label>{anoncomment_name_label_after}
		{anoncomment_name_input_before}<input id="postauthor" type="text" class="{input_classes}" aria-describedby="postauthor-desc" name="author" maxlength="{name_max}" pattern="([^\s][\w\s]{{name_min},{name_max}})">{anoncomment_name_input_after}
		{anoncomment_name_desc_before}<small id="postauthor-desc" class="{desc_classes}">{lang:forms:anonpost:namedesc}</small>{anoncomment_name_desc_after}
	</p>
	<p><label class="ib right"><input type="checkbox" name="terms" value="1" required> Agree to the <a href="{terms}" target="_blank">site terms</a></label> 
		<input type="submit" value="{lang:forms:anonpost:submit}"></p>
</form>' ), 

( 1, 'tpl_usercomment_form', '<form action="{action}" method="post" class="{form_classes}" id="comment_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>{lang:forms:userpost:name}</p>
	<p>
		{usercomment_message_label_before}<label for="message" class="{label_classes}">{lang:forms:userpost:msg}</label>{usercomment_message_label_after}
		{usercomment_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>{usercomment_message_input_after}
		{usercomment_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:userpost:msgdesc}</small>{usercomment_message_desc_after}
	</p>
	<p><input type="submit" value="{lang:forms:userpost:submit}"></p>
</form>' ), 

( 1, 'tpl_editcomment_form', '<form action="{action}" method="post" class="{form_classes}" id="edit_comment_form">
	<input type="hidden" name="id" value="{id}">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		{editcomment_message_label_before}<label for="message" class="{label_classes}">{lang:forms:editpost:msg}</label>{editcomment_message_label_after}
		{editcomment_message_input_before}<textarea id="message" name="message" rows="3" cols="60" class="{input_classes}" aria-describedby="message-desc" required>{message}</textarea>{editcomment_message_input_after}
		{editcomment_message_desc_before}<small id="message-desc" class="{desc_classes}">{lang:forms:editpost:msgdesc}</small>{editcomment_message_desc_after}
	</p>
	<p><input type="submit" value="{lang:forms:editpost:submit}"></p>
</form>' ), 

( 1, 'tpl_modaction', '<p><label for="{prefix}-action">{lang:sections:moderation:drop:action}</label>
	<select id="{prefix}-action" name="action">
		<option value="">--</option>
		
		<option value="pub">{lang:sections:moderation:drop:pub}</option>
		<option value="delete">{lang:sections:moderation:drop:del}</option>
		
		<option value="hold">{lang:sections:moderation:drop:hold}</option>
		<option value="delete">{lang:sections:moderation:drop:del}</option>
		
		<option value="holdsusp">{lang:sections:moderation:drop:holdsusp}</option>
		<option value="delsusp">{lang:sections:moderation:drop:delsusp}</option>
		
		<option value="holdsuspip">{lang:sections:moderation:drop:holdsuspip}</option>
		<option value="delsuspip">{lang:sections:moderation:drop:delsuspip}</option>
		
		<option value="holdsuspuip">{lang:sections:moderation:drop:holdsuspuip}</option>
		<option value="delsuspuip">{lang:sections:moderation:drop:delsuspuip}</option>
		
		<option value="holdblock"{lang:sections:moderation:drop:holdblock}</option>
		<option value="delblock">{lang:sections:moderation:drop:delblock}</option>
		
		<option value="holdblockip">{lang:sections:moderation:drop:holdblockip}</option>
		<option value="delblockip">{lang:sections:moderation:drop:delblockip}</option>
		
		<option value="holdblockuip">{lang:sections:moderation:drop:holdblockuip}</option>
		<option value="delblockuip">{lang:sections:moderation:drop:delblockuip}</option>
	</select>
</p>' ),

( 1, 'tpl_modoptdur', '<option value="{value}">{value}{lang:sections:moderation:drop:dur}</option>' ), 
( 1, 'tpl_modopt', '<option value="{value}">{value}</option>' ), 

( 1, 'tpl_moddelsel', '<form action="{action}" method="post">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<select multiple size="6" class="selector" name="delete">
		{list_items}
	</select>
	<p><input type="submit" value="{lang:sections:moderation:delselect}"></p>
</form>' ), 

( 1, 'tpl_moddursel', '<p>
	<label for="{prefix}-duration" class="{label_classes}">{lang:sections:moderation:duration}</label>
	<input id="{prefix}-duration" type="text" class="{input_classes}" aria-describedby="{prefix}-duration-desc" maxlength="80" pattern="([^\s][\w\s]{3,80})">
	<small id="{prefix}-duration-desc" class="{desc_classes}">{lang:sections:moderation:durdesc}</small>
</p>' ), 

( 1, 'tpl_defaultmodip_form', '<form action="{action}" method="post" class="{form_classes}" id="modip_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		<label for="ip" class="{label_classes}">{lang:sections:moderation:filters:iplbl}</label>
		<input id="ip" type="text" class="{input_classes}" aria-describedby="ip-desc" pattern="([^\s][\w\s,\.\:/]{3,255})" required>
		<small id="ip-desc" class="{desc_classes}">{lang:sections:moderation:filters:ipdesc}</small>
	</p>
	<p>
		<label for="host" class="{label_classes}">{lang:sections:moderation:filters:hostlbl}</label>
		<input id="host" type="text" class="{input_classes}" aria-describedby="host-desc" pattern="([^\s][\w\s,\.\:/\-]{3,255})" required>
		<small id="host-desc" class="{desc_classes}">{lang:sections:moderation:filters:hostdesc}</small>
	</p>
	
	{duration_select}
	{filter_action}
	
	<p><input type="submit" value="{lang:sections:moderation:add}"></p>
</form>' ), 

( 1, 'tpl_modip', '<div class="{section_classes}">
	<input id="panel-iprange" type="checkbox" name="panels">
	<label for="panel-iprange" role="tab">{lang:sections:moderation:filters:ip}</label>
	<div class="{section_content_classes}">
	
	{mod_form}
	{delselect_form}
	
	</div>
</div>' ), 

( 1, 'tpl_modword_form', '<form action="{action}" method="post" class="{form_classes}" id="modword_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		<label for="word" class="{label_classes}">{lang:sections:moderation:filters:wordlbl}</label>
		<input id="word" type="text" class="{input_classes}" aria-describedby="word-desc" maxlength="255" pattern="([^\s][\w\s,]{3,255})" required>
		<small id="word-desc" class="{desc_classes}">{lang:sections:moderation:filters:worddesc}</small>
	</p>
	
	{duration_select}
	{filter_action}
	
	<p><input type="submit" value="{lang:sections:moderation:add}"></p>
</form>' ),

( 1, 'tpl_modwords', '<div class="{section_classes}">
	<input id="panel-words" type="checkbox" name="panels">
	<label for="panel-words" role="tab">{lang:sections:moderation:filters:word}</label>
	<div class="{section_content_classes}">
	
	{mod_form}
	{delselect_form}
	
	</div>
</div>' ), 

( 1, 'tpl_moduserform', '<form action="{action}" method="post" class="{form_classes}" id="moduser_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		<label for="user" class="{label_classes}">{lang:sections:moderation:filters:userlbl}</label>
		<input id="user" type="text" class="{input_classes}" aria-describedby="user-desc" maxlength="255" pattern="([^\s][\w\s,]{3,255})" required>
		<small id="user-desc" class="{desc_classes}">{lang:sections:moderation:filters:userdesc}</small>
	</p>
	
	{duration_select}
	{filter_action}
</form>' ), 

( 1, 'tpl_moduser', '<div class="{section_classes}">
	<input id="panel-usernames" type="checkbox" name="panels">
	<label for="panel-usernames" role="tab">{lang:sections:moderation:filters:user}</label>
	<div class="{section_content_classes}">
	
	{mod_form}
	{delselect_form}
	
	</div>
</div>' ),

( 1, 'tpl_modurl_form', '<form action="{action}" method="post" class="{form_classes}" id="modurl_form">
	<input type="hidden" name="token" value="{token}">
	<input type="hidden" name="nonce" value="{nonce}">
	<p>
		<label for="url" class="{label_classes}">{lang:sections:moderation:filters:urllbl}</label>
		<input id="url" type="text" class="{input_classes}" aria-describedby="url-desc" maxlength="255" pattern="([^\s][\w\s]{3,255})" required>
		<small id="url-desc" class="{desc_classes}">{lang:sections:moderation:filters:urldesc}</small>
	</p>
	
	<p><label for="url-action" class="{label_classes}">{lang:sections:moderation:drop:action}</label>
		<select id="url-action" name="action" class="{input_classes}">
			<option value="">--</option>
			<option value="noanon">{lang:sections:moderation:drop:noanon}</option>
			<option value="close">{lang:sections:moderation:drop:close}</option>
			<option value="hide">{lang:sections:moderation:drop:hide}</option>
		</select>
	</p>
	<p><input type="submit" value="{lang:sections:moderation:add}"></p>
</form>' ),

( 1, 'tpl_modurl', '<div class="{section_classes}">
	<input id="panel-urls" type="checkbox" name="panels">
	<label for="panel-urls" role="tab">{lang:sections:moderation:filters:url}</label>
	<div class="{section_content_classes}">
	
	{mod_form}
	{delselect_form}
	
	</div>
</div>' );-- --


-- Default CSS classes and placeholders
INSERT INTO style_definitions( style_id, content ) 
VALUES 
( 1, '{
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
	
	"code_wrap_classes"		: "",
	"code_classes"			: "",
	
	"section_classes"		: "",
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
	
	"login_form_classes"		: "",
	"register_form_classes"		: "",
	"password_form_classes"		: "",
	"profile_form_classes"		: "",
	
	"login_page_body_classes"	: "",
	"register_page_body_classes"	: "",
	"password_page_body_classes"	: "",
	"profile_page_body_classes"	: "",
	
	"login_page_main_classes"	: "",
	"register_page_main_classes"	: "",
	"password_page_main_classes"	: "",
	"profile_page_main_classes"	: "",
	
	
	"submit_classes"		: "",
	"alt_classes"			: "",
	"warn_classes"			: "",
	"action_classes"		: ""
}' );-- --

