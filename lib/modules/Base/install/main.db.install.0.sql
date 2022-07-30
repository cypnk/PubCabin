-- Default database content

-- Language and translations

-- Default language
INSERT INTO languages (
	id, iso_code, label, display, is_default
) VALUES ( 'en', 'English', 'English', 1 ); 

-- Others
INSERT INTO languages (
	iso_code, label, display
) VALUES 
( 'ar', 'عربى', 'Arabic' ),
( 'be', 'Беларуская мова', 'Belarusian' ),
( 'bn', 'বাংলা', 'Bengali' ),
( 'bo', 'ལྷ་སའི་སྐད་', 'Tibetan' ),
( 'ca', 'Català', 'Catalan' ),
( 'cs', 'Čeština', 'Czech' ),
( 'da', 'Dansk', 'Danish' ),
( 'de', 'Deutsch', 'German' ),
( 'el', 'Ελληνικά', 'Greek' ),
( 'es', 'Español', 'Spanish' ), 
( 'et', 'Eesti', 'Estonian' ),
( 'fa', 'فارسی', 'Farsi' ),
( 'fi', 'Suomi', 'Finnish' ),
( 'fr', 'Français', 'French' ),
( 'gu', 'ગુજરાતી', 'Gujarati' ),
( 'he', 'עברית‬', 'Hebrew' ),
( 'hi', 'हिंदी', 'Hindi' ),
( 'hr', 'Hrvatski', 'Croatian' ),
( 'hu', 'Magyar', 'Hungarian' ),
( 'hy', 'Հայերեն', 'Armenian' ),
( 'it', 'Italiano', 'Italian' ),
( 'jp', '日本語', 'Japanese' ),
( 'ka', 'ქართული ენა', 'Georgian' ),
( 'kn', 'ಕನ್ನಡ', 'Kannada' ),
( 'ko', '조선말', 'Korean' ),
( 'lt', 'Lietuvių kalba', 'Lithuanian' ),
( 'lo', 'ພາສາລາວ', 'Lao' ),
( 'ml', 'Melayu', 'Malay' ),
( 'nl', 'Nederlands', 'Dutch' ),
( 'pa', 'ਪੰਜਾਬੀ', 'Punjabi' ),
( 'pt', 'Português', 'Portuguese' ),
( 'pl', 'Język polski', 'Polish' ),
( 'ru', 'русский', 'Russian' ),
( 'sl', 'Slovenska', 'Slovenian' ),
( 'sk', 'Slovenčina', 'Slovak' ),
( 'si', 'සිංහල', 'Sinhalese' ),
( 'sv', 'Svenska', 'Swedish' ),
( 'ta', 'தமிழ்', 'Tamil' ),
( 'te', 'తెలుగు', 'Telugu' ),
( 'th', 'ภาษาไทย', 'Thai' ),
( 'tr', 'Türkçe', 'Turkish' ),
( 'uk', 'Українська', 'Ukranian' ),
( 'ur', 'اُردُو‬', 'Urdu' ),
( 'vi', 'Tiếng Việt', 'Vietnamese' ),
( 'zh', '汉语', 'Chinese' );-- --

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
			"namevalid"	: "Valid username is required", 
			"pass"		: "Password <span>(required)<\/span>",
			"passdesc"	: "Minimum {pass_min} characters.",
			"passvalid"	: "Valid password is required", 
			"rem"		: "Remember me",
			"submit"	: "Login"
		},
		"register"	: {
			"page"		: "Register",
			"name"		: "Name <span>(required)<\/span>",
			"namedesc"	: "Between {name_min} and {name_max} characters. Letters, numbers, and spaces supported.",
			"namevalid"	: "Valid username is required", 
			"pass"		: "Password <span>(required)<\/span>",
			"passdesc"	: "Minimum {pass_min} characters.",
			"passvalid"	: "Valid password is required", 
			"repeat"	: "Repeat password <span>(required)<\/span>",
			"repeatdesc"	: "Must match password entered above",
			"passrptvalid"	: "Passwords must match", 
			"rem"		: "Remember me",
			"terms"		: "Agree to the <a href=\"{terms}\" target=\"_blank\">site terms</a>",
			"submit"	: "Register"
		},
		"password"	: {
			"page"		: "Change password",
			"old"		: "Old Password <span>(required)<\/span>",
			"olddesc"	: "Must match current password.",
			"oldvalid"	: "Valid password is required", 
			"new"		: "New password <span>(required)<\/span>",
			"newdesc"	: "Minimum {pass_min} characters. Must be different from old password.",
			"newvalid"	: "Valid password is required", 
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
		"blogpost"	: "Journal",
		"blogpostdesc"	: "Blog post entry",
		"blogpage"	: "Journal page",
		"blogpagedesc"	: "Static blog page",
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
}' );

