<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Config.php
 *  @brief	Global configuration and settings
 */
namespace PubCabin;

class Config {
	
	/**
	 *  Configuration placeholder replacements
	 *  @var array
	 */
	private static $replacements	= [
		'{path}'	=> \PUBCABIN_PATH,
		'{store}'	=> \PUBCABIN_DATA,
		'{files}'	=> \PUBCABIN_FILES,
		'{cache}'	=> \PUBCABIN_CACHE,
		'{backup}'	=> \PUBCABIN_BACKUP,
		'{modfiles}'	=> \PUBCABIN_MODSTORE,
		'{error}'	=> \PUBCABIN_ERRORS
	];
	
	/**
	 *  Configuration presets
	 *  @var array
	 */
	private static $defaults	= [
		'app_name'		=> 'PubCabin',
		'app_start'		=> '2017-03-14T04:30:55Z',
		'skip_local'		=> 1,
		'cache'			=> '{cache}',
		'cache_ttl'		=> 3200,
		'uploads'		=> '{files}',
		'file_path'		=> '{path}htdocs/',
		'mod_file_path'		=> '{modstore}',
		'error'			=> '{error}',
		'notice'		=> '{store}notice.log',
		
		'default_basepath'	=> <<<JSON
{
	"basepath"		: "\/",
	"is_active"		: 1,
	"is_maintenance"	: 0,
	"settings"		: []
}
JSON
,
		'site_depth'		=> 25,
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
,
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
		'default_secpolicy'	=> <<<JSON
{
	"content-security-policy": {
		"default-src"			: "'none'",
		"img-src"			: "*",
		"base-uri"			: "'self'",
		"style-src"			: "'self'",
		"script-src"			: "'self'",
		"font-src"			: "'self'",
		"form-action"			: "'self'",
		"frame-ancestors"		: "'self'",
		"frame-src"			: "*",
		"media-src"			: "'self'",
		"connect-src"			: "'self'",
		"worker-src"			: "'self'",
		"child-src"			: "'self'",
		"require-trusted-types-for"	: "'script'"
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
	}
}
JSON
,
		'route_mark'		=> <<<JSON
{
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
JSON
,
		'session_exp'		=> 300,
		'session_bytes'		=> 12,
		'session_limit_count'	=> 5,
		'session_limit_medium'	=> 3,
		'session_limit_heavy'	=> 1,
		'cookie_exp'		=> 86400,
		'cookie_path'		=> '/',
		'cookie_restrict'	=> 1,
		'form_delay'		=> 30,
		'form_expire'		=> 7200
	];
	
	/**
	 *  Overriden configuration during runtime
	 *  @var array
	 */
	private static $options		= [];
	
	public function __construct() {
		
		foreach ( static::$defaults as $k => $v ) {
			static::$defaults[$k] = 
				$this->placeholders( $v );
		}
		
		// Default options loaded first
		static::$options = static::$defaults;
	}
	
	/**
	 *  Replace relative paths and other placeholder values
	 *  
	 *  @param mixed	$settings	Raw configuration
	 *  @return mixed
	 */
	public function placeholders( $settings ) {
		// Replace if string
		if ( \is_string( $settings ) ) {
			return 
			\strtr( $settings, static::$replacements );
		
		// Keep going if an array
		} elseif ( \is_array( $settings ) ) {
			return $this->placeholders( $settings );
		}
		
		// Everything else as-is
		return $settings;
	}
	
	/**
	 *  Overriden configuration, if set
	 * 
	 *  @return array
	 */
	public function getConfig() : array {
		return static::$options;
	}
	
	/**
	 *  Override default configuration with new runtime defaults
	 *  E.G. From database
	 * 
	 *  @param array	$options	New configuration
	 */
	public function overrideDefaults( array $options ) {
		static::$options = 
		\array_merge( static::$defaults, $options );
		
		foreach ( static::$options as $k => $v ) {
			static::$options[$k] = 
				$this->placeholders( $v );
		}
	}
	
	/**
	 *  Get configuration setting or default value
	 *  
	 *  @param string	$name		Configuration setting name
	 *  @param mixed	$default	If not set, fallback value
	 *  @param string	$type		String, integer, or boolean
	 *  @param string	$filter		Optional parse function
	 *  @return mixed
	 */
	public function setting( 
		string		$name, 
		string		$type		= 'string',
		string		$filter		= ''
	) {
		if ( 
			!isset( static::$options[$name] ) || 
			!isset( static::$defaults[$name] )
		) { 
			return null;
		}
		
		switch( $type ) {
			case 'int':
			case 'integer':
				return 
				( int ) ( 
					static::$options[$name] ?? 
					static::$defaults[$name]
				);
				
			case 'bool':
			case 'boolean':
				return 
				( bool ) ( 
					static::$options[$name] ?? 
					static::$defaults[$name]
				);
			
			case 'json':
				$json	= 
				static::$options[$name] ?? 
				static::$defaults[$name];
				
				return 
				\is_array( $json ) ? 
					$json : 
					Util::decode( ( string ) $json );
					
			case 'lines':
				$lines	= 
				static::$options[$name] ?? 
				static::$defaults[$name];
				
				return 
				\is_array( $lines ) ? 
					$lines : 
					FileUtil::lineSettings( 
						( string ) $lines, 
						$filter
					);
			
			// Core configuration setting fallback
			default:
				return 
				static::$options[$name] ?? 
				static::$defaults[$name];
		}
	}
	
	/**
	 *  Helper to determine if given hash algo exists or returns default
	 *  
	 *  @param string	$token		Configuration setting name
	 *  @param string	$default	Defined default value
	 *  @param bool		$hmac		Check hash_hmac_algos() if true
	 *  @return string
	 */
	public function hashAlgo(
		string	$token, 
		string	$default, 
		bool	$hmac		= false 
	) : string {
		static $algos	= [];
		$t		= $token . ( string ) $hmac;
		if ( isset( $algos[$t] ) ) {
			return $algos[$t];
		}
		
		$ht		= $this->setting( $token ) ?? $default;
		
		$algos[$t]	= 
			\in_array( $ht, 
				( $hmac ? \hash_hmac_algos() : \hash_algos() ) 
			) ? $ht : $default;
			
		return $algos[$t];	
	}
}

