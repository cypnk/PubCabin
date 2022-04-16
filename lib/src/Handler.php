<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Handler.php
 *  @brief	Common event notification handler
 */
namespace PubCabin;

class Handler implements \SplObserver {
	
	/**
	 *  Main event controller
	 *  @var \PubCabin\Controller
	 */
	protected $controller;
	
	/**
	 *  Running event output
	 *  @var array
	 */
	protected $data		= [];
	
	/**
	 *  Initialize handler with current controller
	 *  
	 *  @param \PubCabin\Controller	$ctrl	Event controller
	 */
	public function __construct( \PubCabin\Controller $ctrl ) {
		$this->controller	= $ctrl;
	}
	
	/**
	 *  Dependencies by handler/module name, without namespace
	 *  
	 *  @return array
	 */
	public static function dependencies() : array {
		return [];
	}
	
	public function update( 
		\SplSubject	$event, 
		?array		$params	= null 
	) { }
	
	/**
	 *  Handler result data
	 *  
	 *  @param string	$name	Event name to retrieve output
	 */
	public function data( string $name ) : array { 
		return $this->data[$name] ?? []; 
	}
	
	/**
	 *  Sub handler namespace helper
	 *  
	 *  @param array
	 */
	public static function subclass( array $paths ) : string {
		if ( empty( $paths ) ) {
			return '';
		}
		
		// Convet to safe string
		$paths	= 
		\array_map( function( $name ) {
			$name	= 
			\strtr(
				\PubCabin\Util::bland( $name, true ), 
				[ '\\' => '', '.' => '', '-' => '', 
					' ' => '_' ] 
			);
			
			return 
			\PubCabin\Util::smartTrim( $name );
		}, $paths );
		
		// Handlers are all under PubCabin namespace
		return '\\PubCabin\\' . \implode( '\\', $paths );
	}
	
	/**
	 *  Base module class name
	 *  
	 *  @param mixed	$obj	Source handler
	 *  @return string
	 */
	protected static function classSub( $obj ) : string {
		$class	= new \ReflectionClass( $obj );
		
		// Skip non-namespaced classes
		if ( !$class->inNamespace() ) {
			return '';
		}
		
		$name	= $class->getNamespaceName();
		$name	= \strstr( $name, 'PubCabin\\' );
		
		// Only allow PubCabin namespaced classes
		if ( false === $name ) {
			return '';
		}
		
		return 
		\trim( \strtr( $name, [ '\\' => '/' ] ), '/');
	}
	
	/**
	 *  Module resource path helper for visitor requests
	 *  
	 *  @param mixed	$obj	Source handler to derive name
	 *  @param string	$path	Resource file subppath
	 *  @return string
	 */
	public static function resourcePath( $obj, string $path ) : string {
		$name	= static::classStub( $obj );
		if ( empty( $name ) ) {
			return '';
		}
		
		return 
		\PubCabin\Util::slashPath( $name ) . '/public' . 
		\PubCabin\Util::slashPath( $path );
	}
	
	/**
	 *  Visitor sent or generated file storage destination helper
	 *  
	 *  @param mixed	$obj	Destination handler to derive name
	 *  @param string	$path	Storage subppath
	 *  @return string
	 */
	public static function uploadPath( $obj, string $path ) : string {
		$name	= static::classStub( $obj );
		if ( empty( $name ) ) {
			return '';
		}
		
		return 
		\PUBCABIN_MODSTORE . \PubCabin\Util::slashPath( $path );
	}
}

