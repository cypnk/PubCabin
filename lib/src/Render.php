<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Render.php
 *  @brief	HTML Template processing and user output
 */

namespace PubCabin

class Render {
	
	/**
	 *  Standard stylesheet link
	 */
	const TPL_STYLE_TAG	= '<link rel="stylesheet" href="{url}">';
	
	/**
	 *  JavaScript source tag
	 */
	const TPL_SCRIPT_TAG	= '<script src="{url}" nonce="{nonce}"></script>';
	
	/**
	 *  HTML meta tag
	 */
	const TPL_META_TAG	= '<meta name="{name}" content="{content}">';
	
	/**
	 *  Core settings and configuration 
	 *  @var PubCabin\Config
	 */
	protected $config;
	
	/**
	 *  Setting storage
	 *  @var array
	 */
	protected $store	= [];
	
	/**
	 *  Template storage
	 *  @var array
	 */
	protected $templates	= [];
	
	/**
	 *  Previously rendered content
	 *  @var array
	 */
	protected $cache	= [];
	
	/**
	 *  Found storage regions
	 *  @var array
	 */
	protected $regions	= [];
	
	public function __construct( \PubCabin\Config $_config ) {
		$this->config	= $_config;
	}
	
	/**
	 *  Flatten a multi-dimensional array into a path map
	 *  
	 *  @link https://stackoverflow.com/a/2703121
	 *  
	 *  @param array	$items		Raw item map (parsed JSON)
	 *  @param string	$delim		Phrase separator in E.G. {lang:}
	 *  @return array
	 */ 
	public static function flatten(
		array		$items, 
		string		$delim	= ':'
	) : array {
		$it	= new \RecursiveIteratorIterator( 
				new \RecursiveArrayIterator( $items )
			);
		
		$out	= [];
		foreach ( $it as $leaf ) {
			$path = '';
			foreach ( \range( 0, $it->getDepth() ) as $depth ) {
				$path .= 
				\sprintf( 
					"$delim%s", 
					$it->getSubIterator( $depth )->key() 
				);
			}
			$out[$path] = $leaf;
		}
		
		return $out;
	}
	
	/**
	 *  Term replacement helper
	 *  Flattens multidimensional array into {$prefix:group:label...} format
	 *  and replaces matching placeholders in content
	 *  
	 *  @param string	$prefix		Replacement prefix E.G. 'lang'
	 *  @param array	$data		Multidimensional array
	 *  @param string	$content	Placeholders to replace
	 *  @return string
	 */ 
	public static function prefixReplace(
		string		$prefix, 
		array		$data, 
		string		$content
	) : string {
		// Find placeholders with given prefix
		\preg_match_all( 
			'/\{' . $prefix . '(\:[\:a-z_]{1,100}+)\}/i', 
			$content, $m 
		);
		// Convert data to :group:label... format
		$terms	= static::flatten( $data );
		
		// Replacements list
		$rpl	= [];
		
		$c	= \count( $m );
		
		// Set {prefix:group:label... } replacements or empty string
		for( $i = 0; $i < $c; $i++ ) {
			if ( !isset( $m[1] ) ) {
				continue;
			}
			
			if ( !isset( $m[1][$i] ) ) {
				continue;
			}
			$rpl['{' . $prefix . $m[1][$i] . '}']	= 
				$terms[$m[1][$i]] ?? '';
		}
		
		return \strtr( $content, $rpl );
	}
	
	/**
	 *  Scan template for language placeholders
	 *  
	 *  @param string	$tpl	Loaded template data
	 *  @param string	$lang	Language translations
	 *  @return string
	 */
	public static function parseLang( string $tpl, array $lang ) : string {
		$tpl		= 
		static::prefixReplace( 'lang', $lang, $tpl );
		
		// Change variable placeholders
		return \preg_replace( '/\s*__(\w+)__\s*/', ' {\1} ', $tpl );
	}
		
	/**
	 *  Apply region preset content to placeolders in the given template
	 *  
	 *  @param string	$tpl	Page template
	 *  @return string
	 */
	public function renderRegions( string $tpl ) : string {
		
		// Stylesheets, JavaScript, and Meta tags
		$tpl	= 
		$this->regionTags( 
			$tpl, '{stylesheets}', self::TPL_STYLE_TAG, 'styles' 
		);
		
		$tpl	= 
		$this->regionTags( 
			$tpl, '{body_js}', self::TPL_SCRIPT_TAG, 'scripts' 
		);
		
		$tpl	= 
		$this->regionTags( 
			$tpl, '{meta_tags}', self::TPL_META_TAG, 'meta' 
		);
		
		$sa	= $this->config( 'shared_assets' );
		return \strtr( $tpl, [ '{shared_assets}' => $sa ] );
	}
	
	
	/**
	 *  Store and send rendering templates
	 *  
	 *  @param string	$lable	Template name to send back
	 *  @param array	$reg	New templates to initiaize registry or override existing templates
	 *  @return string
	 */
	public function template( string $label, array $reg = [] ) : string {
		// New templates? Append to current store
		if ( !empty( $reg ) ) {
			$this->templates = 
				\array_merge( $this->templates, $reg );
		}
		
		return $this->templates[$label] ?? '';
	}
	
	/**
	 *  Load and change each placeholder into a key
	 *  
	 *  @return array
	 */
	public function loadClasses() : array {
		$cls	= 
		$this->config->settings( 'default_classes', 'json' );
		$cv	= [];
		
		// Add new or appened classes while removing duplicates
		foreach( $cls as $k => $v ) {
			$cv['{' . $k . '}'] = 
			\implode( ' ', Util::uniqueTerms( Util::bland( $v ) ) );
		}
		return $cv;
	}
	
	/**
	 *  Helper to preload area in storage
	 *  
	 *  @param string	$area		Storage segment area
	 */
	protected function loadSettingStore( $area ) {
		switch( $area ) {
			case 'classes':
				$this->store['classes']	= 
					$this->loadClasses();
				break;
					
			case 'styles':
				$s	= 
				$this->config->setting( 
					'default_stylesheets', 'json' 
				);
				
				$lim	= 
				$this->config->setting( 
					'style_limit', 'int' 
				);
				
				$this->store['styles']	= 
					\is_array( $s ) ? $s : 
						FileUtil::linePresets( 
							'stylesheets', 
							$s, $lim 
						);
				break;
				
			case 'scripts':
				$s	= 
				$this->config->setting( 
					'default_scripts' 
				);
				
				$lim	= 
				$this->config->setting( 
					'script_limit', 'int' 
				);
				
				$this->store['scripts']	= 
					\is_array( $s ) ? $s : 
						FileUtil::linePresets( 
							'scripts', 
							$s, $lim 
						);
				break;
			
			case 'meta':
				// Load custom meta tags
				$meta	= 
				$this->config->setting( 'default_meta' );
				
				$this->store['meta']		= 
					\is_string( $meta ) ? 
						Util::decode( $meta ) : 
						[ 'meta' => $meta ];
				break;
			
			default:
				$this->store[$area]	= [];
		}
	}
	
	/**
	 *  Get or override render store pairs
	 *  
	 *  @param string	$area	Template store placeholder area
	 *  @param array	$modify	New placeholder replacements
	 *  @return array
	 */ 
	public function rsettings( 
		string		$area, 
		array		$modify		= [] 
	) : array {
		
		if ( !isset( $this->store[$area] ) ) {
			$this->loadSettingStore( $area );
		}
		
		if ( empty( $modify ) ) {
			return $this->store[$area];
		}
		
		$this->store[$area] = 
		\array_unique( \array_merge( $this->store[$area], $modify ) );
		
		return $this->store[$area];
	}
	
	/**
	 *  Get all the CSS classes of the given render segment
	 *  
	 *  @param string	$name	CSS applicable area
	 *  @return array
	 */
	public function getClasses( string $name ) : array {
		$cls	= $this->rsettings( 'classes' );
		$n	= '{' . \Util::bland( $name ) . '}';
		$va	= [];
		foreach( $cls as $k => $v ) {
			if ( 0 != \strcmp( $n , $k ) ) {
				continue;
			}
			$va	= \Util::uniqueTerms( $v );
			break;
		}
		
		return $va;
	}
	
	/**
	 *  Overwrite the CSS class(es) of a render segment
	 *  
	 *  @param string	$name	CSS applying segment name
	 *  @param string	$value	CSS new CSS parameters
	 */
	public function setClass( string $name, string $value ) {
		$this->rsettings( 
			'classes', 
			[ '{' . \Util::bland( $name ) . '}' => 
				\Util::bland( $value ) ] 
		);
	}
	
	/**
	 *  Add a CSS class to render segment
	 *  
	 *  @param string	$name	CSS applying segment name
	 *  @param string	$value	New CSS classes
	 */
	public function addClass( string $name, string $value ) {
		$vls	= 
		\preg_split( 
			'/\s+/', $value, -1, \PREG_SPLIT_NO_EMPTY 
		);
		
		$cls	= \array_merge( $this->getClasses( $name ), $vls );
		
		$this->setClass( 
			$name, 
			\implode( ' ', \array_unique( $cls ) ) 
		);
	}
	
	/**
	 *  Remove a CSS class from the segment's class list
	 *  
	 *  @param string	$name	CSS segment name
	 *  @param string	$value	Removing class(es)
	 */
	public function removeClass( string $name, string $value ) {
		$vls	= 
		\preg_split( 
			'/\s+/', $value, -1, \PREG_SPLIT_NO_EMPTY 
		);
		
		$cls	= 
		\array_diff( $this->getClasses( $name ), $vls );
		
		$this->setClass( 
			$name, 
			\implode( ' ', \array_unique( $cls ) ) 
		);
	}
	
	/**
	 *  URL and associated nonce extraction helper
	 *  
	 *  @param string	$path	URL|nonce formatted string
	 *  @return array
	 */
	public function splitUrlNonce( string $path ) : array {
		if ( false === \strpos( $path, '|' ) ) {
			return [ 'url' => \trim( $path ), 'nonce' => '' ];
		}
	
		$u	= \strstr( $r, '|', true );
		$n	= \strstr( $r, '|' );
		return [ 
			'url'	=> ( false === $n ) ? '' : \trim( $u ), 
			'nonce'	=> ( false === $n ) ? '' : \trim( $n, '| ' )
		];
	}
	
	/**
	 *  Special tag rendering helper (scripts, links etc...)
	 *  
	 *  @param string	$tpl	Rendering template
	 *  @param string	$label	Region placeholder
	 *  @param string	$tag	Tag replacement template
	 *  @param string	$region	Region setting name
	 *  @return string
	 */
	public function regionTags(
		string		$tpl,
		string		$label,
		string		$tag, 
		string		$region 
	) : string {
		$rg	= $this->rsettings( $region );
		$rgo	= '';
		
		switch( $region ) {
			// Render meta tags
			case 'meta':
				$i = $this->config( 'meta_limit', 'int' );
				
				foreach ( $rg['meta'] ?? [] as $k => $v ) {
					if ( $i < 0 ) {
						break;
					}
					$rgo .= \strtr( $tag, $v );
					$i--;
				}
				break;
				
			// Everything else just has a URL
			default:
				foreach( $rg as $r ) {
					$rgo .= 
					\strtr( $tag, $this->splitUrlNonce( $r ) );
				}
		}
		
		return \strtr( $tpl, [ $label => $rgo ] );
	}
	
	/**
	 *  Append values to placeholder terms used in templates
	 *  
	 *  @param array	$region		Placeholder > value pair
	 */
	function setRegion( array $region = [] ) {
		static $presets = [];
		
		if ( empty( $region ) ) {
			return $presets;
		}
		
		foreach ( $region as $k => $v ) {
			$presets[$k] = ( $presets[$k] ?? '' ) . $v;
		}
	}
	
	
	/**
	 *  Format template with classes, assets, and language parameters
	 *  
	 *  @param string	$tpl	Rendering template
	 *  @param array	$input	Placeholder replacements
	 *  @param string	$lang	Language translations
	 *  @param bool		$full	Complete render including regions if true
	 *  @return string
	 */
	public function parse(
		string	$tpl,
		array	$input	= [],
		array	$lang	= [],
		bool	$full		= false 
	) : string {
		// Rendered template key
		$key		= 
		\hash( 'sha1', ( string ) $full . $tpl );
		
		// Check cache
		if ( !isset( $this->cache[$key] ) ) {
			// Full render?
			$tpl		= $full ? 
			static::parseLang( $this->renderRegions( $tpl ), $lang ) : 
			static::parseLang( $tpl, $lang );
		
			// Apply component classes
			$this->cache[$key]	= 
			\strtr( $tpl, $this->rsettings( 'classes' ) );
				
			// Find render regions
			$this->regions[$key]	= 
			Parser::findTplRegions( $this->cache[$key] );
		}
		
		$out		= [];
		
		// Set content in regions or place empty string
		foreach( $this->regions[$key] as $k => $v ) {
			// Set render content or clear it
			$out['{' . $v .'}'] =  $input[$v] ?? '';
		}
		
		// Parse appended
		$tpl		= 
		static::parseLang( \strtr( $this->cache[$key], $out, $lang ) );
		
		// Finally set classes again
		return \strtr( $tpl, $this->rsettings( 'classes' ) );
	}
	
	/**
	 *  Format template with classes, assets, and language parameters
	 *  
	 *  @param string	$tpl	Rendering template
	 *  @param array	$input	Placeholder replacements
	 *  @param bool		$full	Complete render including regions if true
	 *  @return string
	 */
	public function render(
		string	$tpl,
		array	$input	= [],
		bool	$full		= false 
	) : string {
		static $cache	= [];
		static $regions	= [];
		$key		= hash( 'sha1', ( string ) $full . $tpl );
		
		// Check cache
		if ( !isset( $cache[$key] ) ) {
			// Full render?
			$tpl		= $full ? 
			$this->parseLang( 
				$this->renderRegions( $tpl ) 
			) : 
			$this->parseLang( $tpl );
		
			// Apply component classes
			$cache[$key]	= 
			\strtr( $tpl, $this->rsettings( 'classes' ) );
			
			// Find render regions
			$regions[$key]	= 
			$this->findTplRegions( $cache[$key] );
		}
		
		$out		= [];
		
		// Set content in regions or place empty string
		foreach( $regions[$key] as $k => $v ) {
			// Set render content or clear it
			$out['{' . $v .'}'] =  $input[$v] ?? '';
		}
		
		// Parse appended
		$tpl		= 
		$this->parseLang( \strtr( $cache[$key], $out ) );
		
		// Finally set classes again
		return \strtr( $tpl, $this->rsettings( 'classes' ) );
	}
	
	/**
	 *  Pagination link template generator helper
	 *  
	 *  @param int	$c			Loop counter
	 *  @param int	$page			Current page index
	 *  @param string	$prefix		Page path prefix E.G. 'page'
	 *  @return string
	 */
	protected function pageLink( 
		int	$c, 
		int	$page, 
		string	$prefix 
	) {
		if ( $c == $page ) {
			return
			$this->render( 
				$this->template( 'tpl_page_current_link' ), 
				[ 'url'	=> $prefix . $c, 'text'	=> $c ]
			); 
		}
		
		return
		$this->render( 
			$this->template( 'tpl_page_link' ), 
			[ 'url'	=> $prefix . $c, 'text' => $c ]
		);
	}
	
	/**
	 *  Pagination navigation template generator
	 *  
	 *  @param int		$total		Total number of pages
	 *  @param int		$page		Current page index
	 *  @param int		$limit		Maximum number of pages to show
	 *  @param string	$prefix		Page path prefix E.G. 'page'
	 *  @return string
	 */
	public function paginate(
		int	$total,
		int	$page, 
		int	$limit,
		string	$prefix	= 'page'
	) : string {
		$last	= \ceil( $total / $limit );
		if ( $last < 1 ) {
			return '';
		}
		$out	= '';
		$buf	= 7;
		$pad	= 2;
		$adj	= 1;
		$prev	= $page - 1;
		$next	= $page + 1;
		$lm	= $last - 1;
		$pm	= $pad - 1;
		$pp	= $pad + 1;
		$c	= 0;
		
		if ( $page > 1 ) {
			$out .= 
			$this->render( 
				$this->template( 'tpl_page_prev_link' ) , 
				[ 'url' => $prefix . $prev ] 
			);
		} else {
			$out .= 
			$this->render( $this->template( 'tpl_page_noprev' ) );
		}
		
		if ( $last >= ( $buf + ( $adj * $pad ) ) ) {
			if ( $page < 1 + ( $adj * $pp ) ) {
				for ( $c = 1; $c < ( $pad * 2 ) + ( $adj * $pad ); $c++ ) {
					$out .= 
					$this->pageLink( $c, $page, $prefix );
				}
				
				$out .=
				$this->render( 
					$this->template( 'tpl_page_last2' ), 
					[ 
						'url1'	=> $prefix . $lm, 
						'url2'	=> $prefix . $last,
						'text1'	=> $lm,
						'text2'	=> $last
					]
				); 
			} elseif ( 
				$last - ( $adj * $pad ) > $page && 
				$page > ( $adj * $pm ) 
			) {
				$out .=
				$this->render( 
					$this->template( 'tpl_page_first2' ), 
					[ 
						'url1'	=> $prefix . 1, 
						'url2'	=> $prefix . 2,
						'text1'	=> 1,
						'text2'	=> 2
					]
				);
				
				for ( $c = $page - $adj; $c <= $page + $adj; $c++ ) {
					$out .= 
					$this->pageLink( $c, $page, $prefix );
				}
				
				$out .=
				$this->render( 
					$this->template( 'tpl_page_last2' ), 
					[ 
						'url1'	=> $prefix . $lm, 
						'url2'	=> $prefix . $last,
						'text1'	=> $lm,
						'text2'	=> $last
					]
				); 
			} else {
				$out .=
				$this->render( 
					$this->template( 'tpl_page_first2' ), 
					[ 
						'url1'	=> $prefix . 1, 
						'url2'	=> $prefix . 2,
						'text1'	=> 1,
						'text2'	=> 2
					]
				);
				
				for ( 
					$c = $last - ( $pm + ( $adj * $pp ) ); 
					$c <= $last; $c++ 
				) {
					$out .= 
					$this->pageLink( $c, $page, $prefix );
				}
			}
		} else {
			for ( $c = 1; $c <= $last; $c++ ) {
				$out .= 
				$this->pageLink( $c, $page, $prefix );
			}
		}
		
		if ( $page < $c - 1 ) {
			$out .=
			$this->render( 
				$this->template( 'tpl_page_next_link' ), 
				[ 'url'	=> $prefix . $next ]
			);
		} else {
			$out .= 
			$this->render( $this->template( 'tpl_page_nonext' ) );
		}
		
		return 
		$this->render( 
			$this->templates( 'tpl_pagination' ), 
			[ 'links' => $out ] 
		);
	}
}


