
INSERT INTO page_types (
	label, render, behavior
) VALUES ( 'blog', '', '{
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
( 'post', '', '{
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
}' );-- --

