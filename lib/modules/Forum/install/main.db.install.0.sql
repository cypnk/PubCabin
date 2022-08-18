
INSERT INTO page_types (
	label, render, behavior
) VALUES( 'forum', '', '{
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
( 'forumtopic', '', '{
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
}' );

