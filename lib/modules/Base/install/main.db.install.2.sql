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

( 1, 'tpl_manage_page', '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{manage_page_title}</title>
<link rel="stylesheet" href="{manage}style.css">
</head>
<body>
<header>
{main_nav}
<div class="{manage_header_wrap_classes">
	{search_form}
	<h1>{manage_page_heading}</h1>
</div>
{sub_nav}
<section class="{manage_crumbs_wrap_classes}">
	{breadcrumbs}
</section>
</div>
</header>

<main class="{manage_main_classes}">
<div class="{manage_main_wrap_classes}">
	{manage_page_body}
</div>
</main>

<footer class="{manage_footer_classes}">
<div class="{manage_footer_wrap_classes}">
	{manage_page_footer}
</div>
</footer>
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
		{login_name_desc_before}<small id="loginuser-desc" data-validation="{lang:forms:login:namevalid}" class="{desc_classes}">{lang:forms:login:namedesc}</small>{login_name_desc_after}
	</p>
	<p>
		{login_pass_label_before}<label for="loginpass" class="{label_classes}">{lang:forms:login:pass}</label>{login_pass_label_after}
		{login_pass_input_before}<input id="loginpass" type="password" class="{input_classes}" aria-describedby="loginpass-desc" name="password" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{login_pass_input_after}
		{login_pass_desc_before}<small id="loginpass-desc" data-validation="{lang:forms:login:passvalid}" class="{desc_classes}">{lang:forms:login:passdesc}</small>{login_pass_desc_after}
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
		{register_name_desc_before}<small id="registername-desc" data-validation="{lang:forms:register:namevalid}" class="{desc_classes}">{lang:forms:register:namedesc}</small>{register_name_desc_before}
	</p>
	<p>
		{register_pass_label_before}<label for="registerpass" class="{label_classes}">{lang:forms:register:pass}</span></label>{register_pass_label_after}
		{register_pass_input_before}<input id="registerpass" type="password" class="{input_classes}" aria-describedby="registerpass-desc" name="password" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{register_pass_input_after}
		{register_pass_desc_before}<small id="registerpass-desc" data-validation="{lang:forms:register:passvalid}" class="{desc_classes}">{lang:forms:register:passdesc}</small>{register_pass_desc_after}
	</p>
	<p>
		{register_passr_label_before}<label for="passrepeat" class="{label_classes}">{lang:forms:register:repeat}</span></label>{register_passr_label_after}
		{register_passr_input_before}<input id="passrepeat" type="text" class="{input_classes}" aria-describedby="passrepeat-desc" name="password2" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{register_passr_input_after}
		{register_passr_desc_before}<small id="passrepeat-desc" data-validation="{lang:forms:register:passrptvalid}" class="{desc_classes}">{lang:forms:register:repeatdesc}</small>{register_passr_desc_after}
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
		{oldpass_desc_before}<small id="oldpass-desc" data-validation="{lang:forms:password:oldvalid}" class="{desc_classes}">{lang:forms:password:olddesc}</small>{oldpass_desc_after}
	</p>
	<p>
		{newpass_label_before}<label for="newpass">{lang:forms:password:new}</span></label>{newpass_label_after} 
		{newpass_input_before}<input id="newpass" type="text" class="{input_classes}" aria-describedby="newpass-desc" name="password2" maxlength="4096" pattern="([^\s][\w\s]{{pass_min},4096})" required>{newpass_input_after}
		{newpass_desc_before}<small id="newpass-desc" data-validation="{lang:forms:password:newvalid}" class="{desc_classes}">{lang:forms:password:newdesc}</small>{newpass_desc_after}
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
<nav class="{crumb_classes}"><div class="{crumb_container_classes}">{crumbs_ul_before}
<ul class="{crumb_wrap_classes}">{crumbs_links_before}{links}{crumbs_links_before}</ul>
{crumbs_ul_after}</div></nav>{crumbs_after}' ), 

( 1, 'tpl_sub_breadcrumbs', '{crumbs_sub_before}<nav class="{crumb_sub_classes}">
<div class="{crumbs_sub_container_classes}">{crumbs_sub_ul_before}
<ul class="{crumb_sub_wrap_classes}">{links}</ul>{crumbs_sub_ul_after}
</div></nav>{crumbs_sub_after}' ), 

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
 	"crumb_container_classes"	: "content",
	"crumb_sub_classes"		: "sub",
	"crumb_sub_wrap_classes"	: "",
 	"crumbs_sub_container_classes"	: "content",
	
	"crumb_item_classes"		: "",
	"crumb_link_classes"		: "",
	"crumb_current_classes"		: "",
	"crumb_current_item"		: "",
	"pagination_classes"		: "",
	"pagination_ul_classes"		: "",
	
 	"manage_header_wrap_classes"	: "",
	"manage_crumbs_wrap_classes"	: "",
	"manage_main_classes"		: "",
	"manage_main_wrap_classes"	: "",
	"manage_footer_classes"		: "",
 	
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
	"search_form_wrap_classes"	: "content",
	"search_fieldset_classes"	: "",
	"field_wrap"			: "",
	"button_wrap"			: "",
	"label_classes"			: "",
	"special_classes"		: "special",
	"input_classes"			: "",
	"desc_classes"			: "desc",
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
}' );

