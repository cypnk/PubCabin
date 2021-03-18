<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Cabin/Module.php
 *  @brief	Core Module which initiates PubCabin activities
 */
namespace PubCabin\Modules\Cabin;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Initial client request
	 *  @var \PubCabin\Request
	 */
	protected $request;
	
	public function dependencies() : array {
		return [ 'Hooks' ];
	}
	
	public function __construct( string $_store, array $_data ) {
		parent::__construct( $store );
		
		if ( !isset( static::$data ) ) {
			static::$data		= 
			new \PubCabin\Data( 
				$_data, 
				static::$config 
			);
		}
		
		$this->request	= 
		new \PubCabin\Request( $this->config );
	}
	
	public function getRequest() {
		return $this->request;
	}
}

