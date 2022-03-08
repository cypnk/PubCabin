<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Wiki/Module.php
 *  @brief	Simple user wiki with interlinked pages
 */
namespace PubCabin\Modules\Wiki;

class Module extends \PubCabin\Modules\Module {
	
	const WIKI_LINK_RX	= 
	'/\[\[(?P<term>[^|\]]*)\|(?P<link>[^\]^\n^\r]+)\]\]/';
	
	public function dependencies() : array {
		return [ 
			'Styles', 
			'Forms', 
			'Sites', 
			'Manager', 
			'Membership',
			'Comments'
		];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks = $this->getModule( 'Hooks' );
		
		// Register url request
		$hooks->register( [ 'requesturl', [ 
			$this, 'filterRequest' 
		] );
	}
	
	/**
	 *  Parse and extract linked pages
	 *  
	 *  @param string	$text	Article body
	 *  @param array	$links	Extracted wiki pages
	 *  @return string
	 */
	protected function pageParse( 
		string	$text, 
		array	&$links 
	) : string {
		$text	= 
		\strip_tags( \nl2br( $text, false ), 'br' );
		
		$links	= [];
		
		\preg_match_all( self::WIKI_LINK_RX, $text, $m );
		if ( !empty( $m ) ) {
			$links = 
			 \array_filter( 
				$m, 
				function( $k ) {
					return \is_string( $k );
				}, \ARRAY_FILTER_USE_KEY 
			);
			
			// Resort links
			$c = count( $m['term'] );
			for ( $i = 0; $i < $c; $i++ ) {
				$links[] = 
				[ $m['term'][$i] => $m['link'][$i] ];
			}
		}
		
		// TODO: Replace matches with HTML links
		return $text;
	}
	
	/**
	 *  Request filter event
	 *  
	 *  @param string	$event	Request event name
	 *  @param array	$hook	Previous hook event data
	 *  @param array	$params	Passed event data
	 */
	public function filterRequest( 
		string		$event, 
		array		$hook, 
		array		$params 
	) {
		$filter	= [
			'since'	=> [
				'filter'	=> \FILTER_VALIDATE_INT,
				'options'	=> [
					'min_range'	=> 1,
					'max_range'	=> time(),
					'default'	=> 1
				]
			],
			'phrase'=> [
				'filter'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'options'	=> [ 'default' => '' ]
			],
			'find'	=> [
				'filter'	=> \FILTER_CALLBACK,
				'options'	=> '\PubCabin\Util::unifySpaces'
			],
			'token'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'nonce'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'meta'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS
		];
		
		return 
		\array_merge( $hook, \filter_var_array( $params, $filter ) );
	}
}

