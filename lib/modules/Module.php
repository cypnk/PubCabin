<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Module.php
 *  @brief	Base module inherited by all others
 */
namespace PubCabin\Modules;

abstract class Module {
	
	/**
	 *  Main database name
	 */
	const MAIN_DATA			= 'main.db';
	
	/**
	 *  Base module's asset folder
	 */
	const ASSET_DIR			= '/assets';
	
	/**
	 *  Base module's template folder
	 */
	const TEMPLATE_DIR		= '/templates';
	
	/**
	 *  Base module's publicly accessible content E.G css, js etc...
	 */
	const PUBLIC_DIR		= '/public';
	
	/**
	 *  Already loaded modules
	 *  @var array
	 */
	protected static $loaded	= [];
	
	/**
	 *  Base configuration loader
	 *  @var \PubCabin\Config
	 */
	protected static $config;
	
	/**
	 *  Database storage and access
	 *  @var \PubCabin\Data
	 */
	protected static $data;
	
	/**
	 *  Initial client request
	 *  @var \PubCabin\Request
	 */
	protected static $request;
	
	abstract public function dependencies() : array;
	
	public function __construct() {
		$this->loadModules();
	}
	
	/**
	 *  Load module dependencies
	 */
	protected function loadModules() {
		$deps = $this->dependencies();
		if ( empty( $deps ) ) {
			return;
		}
		
		foreach ( $deps as $k ) {
			if ( \array_key_exists( 
				$k, static::$loaded 
			) ) {
				continue;
			}
			
			$cls = 
			'\\PubCabin\\Modules\\' . $k . '\\Module';
			static::$loaded[$k] = new $cls();
		}
	}
	
	/**
	 *  Get list of currently loaded modules
	 *  
	 *  @return array
	 */
	protected function getLoadedModules() : array {
		return \array_keys( static::$loaded );
	}
	
	/**
	 *  Get already loaded dependency
	 *  
	 *  @param string	$module		Module short class name
	 *  @return mixed
	 */
	protected function getModule( string $module ) {
		return static::$loaded[$module] ?? null;
	}
	
	/**
	 *  Get or load current configuration
	 *  
	 *  @return \PubCabin\Config
	 */
	protected function getConfig() {
		if ( !isset( static::$config ) ) {
			static::$config		= 
			new \PubCabin\Config();
		}
		return static::$config;
	}
	
	/**
	 *  Get or load current visitor request
	 *  
	 *  @return \PubCabin\Request
	 */
	public function getRequest() {
		if ( !isset( static::$request ) ) {
			$config = $this->getConfig();
			if ( empty( $config ) ) {
				return null;
			}
			
			static::$request	= 
			new \PubCabin\Request( $config );
		}
		return static::$request;
	}
	
	/**
	 *  Get or load database access class
	 *  
	 *  @return \PubCabin\Data
	 */
	protected function getData() {
		if ( !isset( static::$data ) ) {
			$config	= $this->getConfig();
			if ( empty( $config ) ) {
				return null;
			}
			
			static::$data		= 
			new \PubCabin\Data( $config );
		}
		
		return static::$data;
	}
	
	/**
	 *  Calling module's base file location
	 *  
	 *  @param string	$mode	Optional asset or template folder
	 */
	protected function moduleBase( string $mode = '' ) : string {
		$cls	= new \ReflectionClass( __CLASS__ );
		$dir	= 
		\PUBCABIN_MODBASE . 
		\substr( 
			$cls->getNamespaceName(), 0, 
			\strlen( 'PubCabin\\Modules\\' )
		);
		
		// Specific subfolder?
		switch ( \strtolower( $mode ) ) {
			case 'tpl':
			case 'template':
			case 'templates':
				return $dir . self::TEMPLATE_DIR;
			
			case 'asset':
			case 'assets':
				return $dir . self::ASSET_DIR;
			
			case 'htdocs':
			case 'public':
				return $dir . self::PUBLIC_DIR;
		}
		
		return $dir;
	}
}



