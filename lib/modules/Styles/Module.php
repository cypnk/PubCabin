<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Styles/Module.php
 *  @brief	Template and rendering handler
 */
namespace PubCabin\Modules\Styles;

class Module extends \PubCabin\Modules\Module {
	
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
	 *  Discover functions inside templates with placeholders and send them hook
	 *  
	 *  @param string	$tpl		Raw render template
	 *  @param array	$input		Placeholder replacement values
	 *  @param array	$place		Original placeholders in the template
	 *  @return array
	 */
	public function processTemplate( string $tpl, array $input, array $place ) {
		$parser = $this->getParser();
		$hooks	= $this->getModule( 'Hooks' );
		
		$groups = $parser->parse( $tpl );
		
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

