<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Hooks/Module.php
 *  @brief	Delayed or scheduled execution routines
 */
namespace PubCabin\Modules\Hooks;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Registered call methods
	 *  @var array
	 */
	protected static $handlers	= [];
	
	/**
	 *  Post-trigger output store
	 *  @var array
	 */
	protected static $output	= [];
	
	/**
	 *  Post duty last call methods
	 *  @var array
	 */
	private static $_shutdown	= [];
	
	public function dependencies() : array {
		return [];
	}
	
	/**
	 *  End functions during destruct
	 */
	public function __destruct() {
		// Run shutdown hooks
		$this->register( [ 'shutdown', [] ] );
		
		// Run destruct callbacks
		foreach( static::$_shutdown as $k => $v ) {
			if ( \is_array( $v ) ) {
				$k( ...$v );
			} elseif ( $v !== null ) {
				$k( $v );
			} else {
			$k();
			}
		}
	}
	
	/**
	 *  Hooks and extensions
	 *  Append a hook handler in [ 'event', 'handler' ] format
	 *  Call the hook event in [ 'event', args... ] format
	 *  
	 *  @param array	$params		[ 'event', 'handler' ]
	 */
	public function register( array $params ) {
		
		// Nothing to add?
		if ( empty( $params ) ) { return; }
		
		// First parameter is the event name
		$name			= 
		\strtolower( \array_shift( $params ) );
		
		// Filter event
		$name			= 
		\PubCabin\Util::lowercase(
			\strtr( 
				\PubCabin\Util::unifySpaces( $name ), 
				[ ' ' => '', '.' => '' ] 
			)
		);
		
		// Prepare event to receive handlers
		if ( !isset( $this->handlers[$name] ) ) {
			$this->handlers[$name]	= [];
		}
		
		// Adding a handler to the given event?
		// Need an event name and a handler
		if ( 
			\is_string( $params[0] )	&& 
			\is_callable( $params[0] )
		) {
			$this->handlers[$name][]	= $params[0];
			
		// Handler being called with parameters, if any
		} else {
			// Asking for hook-named output?
			if ( 
				\is_string( $params[0] ) && 
				empty( $params[0] ) 
			) {
				return $this->output[$name] ?? [];
			}
			
			// Execute handlers in order and store in output
			foreach( $this->handlers[$name] as $handler ) {
				$this->output[$name] = 
				$handler( 
					$name, 
					$this->output[$name] ?? [], 
					...$params 
				) ?? [];
			}
		}
	}
	
	
	/**
	 *  Hook result rendering helpers
	 */
	
	/**
	 *  Check for non-empty string result from hook
	 *  
	 *  @param string	$event		Hook event name
	 *  @param string	$default	Fallback content
	 *  @return array
	 */
	public function stringResult( 
		string		$event, 
		string		$default	= '' 
	) : string {
		$sent	= $this->register( [ $event, '' ] );
		return 
		( !empty( $sent ) && \is_string( $sent ) ) ? 
			$sent : $default;
	}
	
	/**
	 *  Check for non-empty array result from hook
	 *  
	 *  @param string	$event		Hook event name
	 *  @param array	$default	Fallback content
	 *  @return array
	 */
	public function arrayResult( 
		string		$event, 
		array		$default	= [] 
	) : array {
		$sent	= $this->register( [ $event, '' ] );
		return 
		( !empty( $sent ) && \is_array( $sent ) ) ? 
			$sent : $default;
	}
	
	/**
	 *  Get HTML from hook result, if sent
	 *  
	 *  @param string	$event		Hook event name
	 *  @param string	$default	Fallback html content
	 *  @return string
	 */
	public function html(
		string		$event, 
		string		$default	= '' 
	) : string {
		return 
		$this->arrayResult( $event )['html'] ?? $default;
	}
	
	/**
	 *  Collection of functions to execute during class destruction
	 */
	public function shutdown() {
		$args			= \func_get_args();
		if ( empty( $args ) ) {
			return;
		}
		
		if ( \is_callable( $args[0] ) ) {
			static::$_shutdown[$args[0]] = 
				$args[1] ?? null;
		}
	}
}


