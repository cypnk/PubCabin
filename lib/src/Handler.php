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
	protected readonly object $controller;
	
	/**
	 *  Running event output
	 *  @var array
	 */
	protected array $data		= [];
	
	/**
	 *  List of resource sub folders
	 *  @var array
	 */
	private static $resource_list = 
	[ 'public', 'install', 'update', 'assets' ];
	
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
	protected static function classStub( $obj ) : string {
		$class	= new \ReflectionClass( $obj );
		
		// Skip non-namespaced classes
		if ( !$class->inNamespace() ) {
			return '';
		}
		
		$name	= $class->getNamespaceName();
		
		// Only allow PubCabin namespaced classes
		if ( false === $name || empty( $name ) ) {
			return '';
		}
		
		$name	= \strtr( $name, [ '\\' => '/' ] );
		$ls	= \explode( '/', $name );
		return empty( $ls ) ? '' : end( $ls ) ?? '';
	}
	
	/**
	 *  Module resource path helper for visitor requests
	 *  
	 *  @param mixed	$obj	Source handler to derive name
	 *  @param string	$path	Resource file subppath
	 *  @param string	$mode	Sub directory selection mode
	 *  @return string
	 */
	public static function resourcePath( 
			$obj, 
		string	$path,
		string	$mode	= 'public'
	) : string {
		// Restrict mode
		if ( !\in_array( $mode, static::$resource_list ) ) {
			return '';
		}
		
		$name	= static::classStub( $obj );
		if ( empty( $name ) ) {
			return '';
		}
		
		return 
		\PubCabin\Util::slashPath( \PUBCABIN_MODBASE, true ) . $name . 
		\PubCabin\Util::slashPath( $mode ) . 
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
		\PubCabin\Util::slashPath( \PUBCABIN_MODSTORE, true ) . $path;
	}
}

