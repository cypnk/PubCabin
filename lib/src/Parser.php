<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Parser.php
 *  @brief	Text component placeholder parser
 */
namespace PubCabin;

class Parser {
	
	/**
	 *  Region match pattern
	 *  @example {region}
	 */
	const RX_REGION	= '/(?<=\{)([a-z_]+)(?=\})/i';
	
	/**
	 *  General term match pattern
	 *  @example https://regex101.com/r/mFHkWO/3
	 */
	const RX_MATCH =
	"/(:?\{)
		(
			([\w]+)
			(:?\(([\w=\"\'\:,]+):?\))?
		)
		{repeat}
	(:?\})/igx";
	
	/**
	 *  Repeated matching sub pattern
	 */
	const RX_REPEAT = 
	"(\:?
		([\w]+)
		(:?\(([\w=\"\'\:,]+):?\))?
	)?";
	
	/**
	 *  Maximum number of sub matches (in addition to primary)
	 */
	const MAX_DEPTH = 5;
	
	/**
	 *  Item start
	 */
	const IDX_ITEM	= 3;
	
	/**
	 *  Parameter start
	 */
	const IDX_PARAM	= 5;
	
	/**
	 *  Skip n items for next item/parameter
	 */
	const IDX_SKIP	= 4;
	
	/**
	 *  Generated regular expression
	 *  @var string
	 */
	private $regex;
	
	/**
	 *  Placeholder parsed templates list
	 *  @var array
	 */
	private $parsed	= [];
	
	/**
	 *  Find template {regions} set in the HTML
	 *  Template regions must consist of letters, underscores, and 
	 *  without spaces
	 *  
	 *  @param string	$tpl	Raw HTML template without content
	 *  @return array
	 */
	public static function findTplRegions( string $tpl ) : array {
		if ( \preg_match_all( self::RX_REGION, $tpl, $m ) ) {
			return $m[0];
		}
		return [];
	}
	
	/**
	 *  Process placeholder parameter clusters
	 *  
	 *  @example
	 *  {Lang:label}
	 *  {Workspace:Collection(id=:id)}
	 * 
	 *  @param string		$tpl	Raw render template
	 *  @param \PubCabin\Config	$config	Main configuration
	 */
	public function parse( 
		string		$tpl, 
		\PubCabin\Config	$config
	) : array {
		$key	= \hash( 'sha1', $tpl );
		
		if ( isset( $this->parsed[$key] ) ) {
			return $this->parsed[$key];
		}
		
		$groups	= [];
		
		if ( !\preg_match_all( 
			$this->getRenderRegex( $config ), 
			$tpl, 
			$matches 
		) ) {
			$this->parsed[$key] = $groups;
			return $groups;
		}
		
		// Limit to presets
		$rii	= 
		$config->setting( 'parser_idx_item', 'int' ) ?? 
			self::IDX_ITEM;
		
		$ris	= 
		$config->setting( 'parser_idx_skip', 'int' ) ?? 
			self::IDX_SKIP;
		
		$rip	= 
		$config->setting( 'parser_idx_param', 'int' ) ?? 
			self::IDX_PARAM;
		
		// Append segments to major clusters up to preset limits
		$mrc	= \array_chunk( $matches, $rii + $ris );
		foreach ( $mrc as $m ) {
			$groups[$m[0]] = $m[$rip];
		}
		
		$this->$parsed[$key] = $groups;
		return $groups;
	}
	
	/**
	 *  Placeholder match pattern builder
	 *  
	 *  @example 
	 *  Items 3, 7, 11, 15, 19, 23
	 *  Item params 5, 9, 13, 17, 21, 25
	 */
	public function getRenderRegex( \PubCabin\Config $config ) : string {
		if ( isset( $this->regex ) ) {
			return $this->regex;
		}
		
		$mxd		= 
		$config->setting( 'parser_max_depth', 'int' ) ?? 
			self::MAX_DEPTH;
		
		$m		= \str_repeat( self::RX_REPEAT, $mxd );
		$this->regex	= 
		\strtr( self::RX_MATCH, [ '{repeat}' => $m ] );
		
		return $this->regex;
	}
}


