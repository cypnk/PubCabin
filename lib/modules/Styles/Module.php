<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Styles/Module.php
 *  @brief	Template and rendering handler
 */
namespace PubCabin\Modules\Styles;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Generated regular expression
	 *  @var string
	 */
	private static $regex;
	
	/**
	 *  Placeholder parsed templates list
	 *  @var array
	 */
	private static $parsed	= [];
	
	/**
	 *  General term match pattern in {domain:etc...} format
	 *  Test: https://regex101.com/r/mFHkWO/3
	 */
	const RENDER_RX_MATCH	= <<<RX
/(:?\{)
	(
		([\w]+)
		(:?\(([\w=\"\'\:,]+):?\))?
	)
	{repeat}
(:?\})/igx
RX;
	
	/**
	 *  Repeated matching sub pattern. E.G. {domain:sub:etc...}
	 */
	const RENDER_RX_REPEAT	= <<<RX
(\:?
	([\w]+)
	(:?\(([\w=\"\'\:,]+):?\))?
)?
RX;
	
	/**
	 *  Maximum number of sub matches (in addition to primary)
	 */
	const RENDER_MAX_DEPTH	= 6;
	
	/**
	 *  Item start
	 */
	const RENDER_IDX_ITEM	= 3;
	
	/**
	 *  Parameter start
	 */
	const RENDER_IDX_PARAM	= 5;
	
	/**
	 *  Skip n items for next item/parameter
	 */
	const RENDER_IDX_SKIP	= 4;

	
	public function dependencies() : array {
		return [ 'Hooks' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks = $this->getModule( 'Hooks' );
		
		// Register template render event handler
		$hooks->event( [ 
			'templaterender', 
			[ $this, 'renderTemplate' ] 
		] );
	}
	
	/**
	 *  Placeholder match pattern builder
	 *  
	 *  @example 
	 *  Items 3, 7, 11, 15, 19, 23
	 *  Item params 5, 9, 13, 17, 21, 25
	 */
	protected function getRenderRegex() {
		if ( isset( static::$regex ) ) {
			return static::$regex;
		}
		
		$mxd	= 
		$this->getConfig()->setting( 'render_max_dpth', 'int' ) ?? 
			self::RENDER_MAX_DEPTH;
		
		$m	= \str_repeat( self::RENDER_RX_REPEAT, $mxd );
		static::$regex	= \strtr( self::RENDER_RX_MATCH, [ '{repeat}' => $m ] );
		
		return static::$regex;
	}
	
	/**
	 *  Process placeholder parameter clusters
	 *  
	 *  @param string	$tpl		Raw render template
	 *  
	 *  @example {Lang:label} {Workspace:Collection(id=:id)}
	 */
	public function parseRender( string $tpl ) : array {
		$key		= \hash( 'sha1', $tpl );
		
		if ( isset( static::$parsed[$key] ) ) {
			return static::$parsed[$key];
		}
		
		$config	= $this->getConfig();
		$groups	= [];
		
		\preg_match_all( $this->getRenderRegex(), $tpl, $matches );
		
		if ( empty( $matches ) ) {
			static::$parsed[$key] = $groups;
			return $groups;
		}
		
		$rii = 
		$config->setting( 'render_idx_item', 'int' ) ?? 
			self::RENDER_IDX_ITEM;
		
		$ris = 
		$config->setting( 'render_idx_skip', 'int' ) ?? 
			self::RENDER_IDX_SKIP;
		
		$rip = 
		$config->setting( 'render_idx_param', 'int' ) ?? 
			self::RENDER_IDX_PARAM;
		
		$mrc = \array_chunk( $matches, $rii + $ris );
		foreach ( $mrc as $m ) {
			$groups[$m[0]] = $m[$rip];
		}
		
		static::$parsed[$key] = $groups;
		return $groups;
	}
	
	/**
	 *  Discover functions inside templates with placeholders and send them hook
	 *  
	 *  @param string	$tpl		Raw render template
	 *  @param array	$input		Placeholder replacement values
	 *  @param array	$place		Original placeholders in the template
	 *  @return array
	 */
	public function processTemplate( string $tpl, array $input, array $place ) {
		$groups = $this->parseRender( $tpl );
		$hooks	= $this->getModule( 'Hooks' );
		
		foreach ( $groups as $k => $v ) {
			$hook->event( [ 
				'templatefunction', 
				[
					'template'	=> $tpl, 
					'input'		=> $input,
					'placeholders'	=> $place,
					'function'	=> $k,
					'parameters'	=> $v 
				] 
			] );
		}
	}
	
	/**
	 *  Template render event handler
	 */
	public function renderTemplate( string $event, array $hook, array $params ) {
		$this->processTemplate( 
			$hook['template']	?? '',
			$hook['input' ]		?? [],
			$hook['placeholders']	?? []
		);
	}

}

