<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Module.php
 *  @brief	Base module inherited by all others
 */
namespace PubCabin\Modules;

abstract class Module {
	
	protected static $loaded	= [];
	
	abstract public function dependencies() : array;
	
	public function __construct() {
		$this->loadModules();
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
			
			static::$loaded[$k] = new "$k\Module";
		}
	}
	
	protected function getModule( string $module ) {
		return static::$loaded[$module] ?? null;
	}
}

