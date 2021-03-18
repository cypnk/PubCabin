<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Module.php
 *  @brief	Base module inherited by all others
 */
namespace PubCabin\Modules;

abstract class Module {
	
	protected static $loaded	= [];
	
	/**
	 *  Base configuration loader
	 *  @var \PubCabin\Config
	 */
	protected static $config;
	
	/**
	 *  Storage folder location
	 *  @var string
	 */
	protected static $store;
	
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
	
	public function __construct( string $_store ) {
		$this->loadModules();
		
		if ( !isset( static::$store ) ) {
			static::$store = $_store;
		}
	}
	
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
	
	protected function getModule( string $module ) {
		return static::$loaded[$module] ?? null;
	}
	
	protected function getStore() {
		return static::$store ?? null;
	}
	
	protected function getConfig() {
		if ( !isset( static::$config ) ) {
			$store = $this->getStore();
			if ( empty( $store ) ) {
				return null;
			}
			static::$config		= 
			new \PubCabin\Config( $store );
		}
		return static::$config;
	}
	
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
	
	protected function getData() {
		if ( !isset( static::$data ) ) {
			$config	= $this->getConfig();
			$_data	= 
			\PubCabin\Util::trimmedList( \PUBCABIN_BASES );
			
			static::$data		= 
			new \PubCabin\Data( $_data, $config );
		}
		
		return static::$data;
	}
}


