<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Config.php
 *  @brief	Global configuration and settings
 */
namespace PubCabin;

class Config {
	
	/**
	 *  Storage folder
	 *  @var string
	 */
	private $store;
	
	/**
	 *  Database index primary key (set once)
	 *  @var int
	 */
	private $cabin;
	
	/**
	 *  Configuration presets
	 *  @var array
	 */
	private static $defaults	= [
		'app_name'		=> 'PubCabin',
		'app_start'		=> '2017-03-14T04:30:55Z',
		'skip_local'		=> 1,
		'cache'			=> STORE . 'cache/',
		'cache_ttl'		=> 3200,
		'file_path'		=> PATH . 'htdocs/',
		'error'			=> STORE . 'error.log',
		'notice'		=> STORE . 'notice.log',
		
		'default_basepath'	=> <<<JSON
{
	"basepath"		: "\/",
	"is_active"		: 1,
	"is_maintenance"	: 0,
	"settings"		: []
}
JSON
,
		'language'		=> 'en',
		'locale'		=> 'us',
		'timezone'		=> 'America/New_York',
		'token_bytes'		=> 8,
		'nonce_hash'		=> 'tiger160,4',
		'ext_whitelist'		=> <<<JSON
{
	"text"		: "css, js, txt, html",
	"images"	: "ico, jpg, jpeg, gif, bmp, png, tif, tiff, svg", 
	"fonts"		: "ttf, otf, woff, woff2",
	"audio"		: "ogg, oga, mpa, mp3, m4a, wav, wma, flac",
	"video"		: "avi, mp4, mkv, mov, ogg, ogv"
}
JSON
,
		'max_search_words'	=> 10,
		'style_limit'		=> 20,
		'script_limit'		=> 10,
		'meta_limit'		=> 15,
		'folder_limit'		=> 15,
		'shared_assets'		=> '/',
		'frame_whitelist'	=> '',
		'default_stylesheets'	=> '',
		'default_scripts'	=> '',
		'default_meta'		=> <<<JSON
{
	"meta" : [
		{ "name" : "generator", "content" : 
			"Bare; https:\/\/github.com\/cypnk\/PubCabin" }
	]
}
JSON
,
		'tag_white'		=> <<<JSON
{
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
}
JSON
		'form_white'		=> <<<JSON
{
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
}
JSON
,
		'default_jcsp'		=> <<<JSON
{
	"default-src"		: "'none'",
	"img-src"		: "*",
	"base-uri"		: "'self'",
	"style-src"		: "'self'",
	"script-src"		: "'self'",
	"font-src"		: "'self'",
	"form-action"		: "'self'",
	"frame-ancestors"	: "'self'",
	"frame-src"		: "*",
	"media-src"		: "'self'",
	"connect-src"		: "'self'",
	"worker-src"		: "'self'",
	"child-src"		: "'self'",
	"require-trusted-types-for" : "'script'"
}
JSON
	];
	
	public function __construct( string $store ) {
		$this->store = $store;
	}
	
	// TODO
	public function getConfig() : array {
		return [];
	}
	
	/**
	 *  Get configuration setting or default value
	 *  
	 *  @param string	$name		Configuration setting name
	 *  @param mixed	$default	If not set, fallback value
	 *  @param string	$type		String, integer, or boolean
	 *  @return mixed
	 */
	public function setting( 
		string		$name, 
		string		$type		= 'string' 
	) {
		if ( !isset( static::$defaults[$name] ) ) {
			return null;
		}
		
		$config  = $this->getConfig();
		
		switch( $type ) {
			case 'int':
			case 'integer':
				return 
				( int ) ( 
					$config[$name] ?? 
					static::$defaults[$name]
				);
				
			case 'bool':
			case 'boolean':
				return 
				( bool ) ( 
					$config[$name] ?? 
					static::$defaults[$name]
				);
			
			default:
				return 
				$config[$name] ?? 
				static::$defaults[$name];
		}
	}
}

