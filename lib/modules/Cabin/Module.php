<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Cabin/Module.php
 *  @brief	Core Module which initiates PubCabin activities
 */
namespace PubCabin\Modules\Cabin;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Storage folder location
	 *  @var string
	 */
	protected $store;
	
	/**
	 *  Base configuration loader
	 *  @var \PubCabin\Config
	 */
	protected $config;
	
	/**
	 *  Initial client request
	 *  @var \PubCabin\Request
	 */
	protected $request;
	
	/**
	 *  Database storage and access
	 *  @var \PubCabin\Data
	 */
	protected $data;
	
	public function dependencies() : array {
		return [];
	}
	
	public function __construct( string $_store, array $_data ) {
		parent::__construct();
		
		$this->store		= $_store;
		$this->config		= 
		new \PubCabin\Config( $_store );
		
		$this->data	= 
		new \PubCabin\Data( $_data, $this->config );
		
		$this->request	= 
		new \PubCabin\Request( $this->config );
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getStore() {
		return $this->store;
	}
	
	public function getData() {
		return $this->data;
	}
}

