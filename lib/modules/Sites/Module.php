<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Sites/Module.php
 *  @brief	Main website handler
 */
namespace PubCabin\Modules\Sites;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks	= $this->getModule( 'Hooks' );
		
		// Register this module's request handler
		$hooks->event( [ 'request', [ $this, 'begin' ] );
	}
	
	
	/**
	 *  Application start
	 *  
	 *  @param string	$event	Request event name
	 *  @param array	$hook	Previous hook event data
	 *  @param array	$params	Passed event data
	 */
	public function begin( 
		string		$event, 
		array		$hook, 
		array		$params 
	) {
		$req	= $this->getRequest();
		$db	= $this->getData();
		
		// TODO Load websites and configuration
	}
}

