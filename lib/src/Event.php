<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Event.php
 *  @brief	PubCabin notification hook
 */
namespace PubCabin;

class Event implements \SplSubject {
	
	/**
	 *  Current event name
	 *  @var string
	 */
	protected readonly string $name;
	
	/**
	 *  Main event controller
	 *  @var \PubCabin\Controller
	 */
	protected readonly object $controller;
	
	/**
	 *  Registered handlers
	 *  @var array
	 */
	protected array $handlers	= [];
	
	/**
	 *  Event parameters on execution
	 */
	protected array $params	= [];
	
	/**
	 *  Stored event data
	 *  @var array
	 */
	protected array $output	= [];
	
	
	/**
	 *  Create new event with controller and unique name
	 *  
	 *  @param \PubCabin\Controller	$ctrl	Event controller
	 *  @param string		$name	Current event's name
	 */
	public function __construct( 
		\PubCabin\Controller	$ctrl, 
		string			$name 
	) {
		$this->controller	= $ctrl;
		$this->name		= $name;
	}
	
	/**
	 *  Current event's name (read-only)
	 *  @return string
	 */
	public function name() : string {
		return $this->name;
	}
	
	/**
	 *  Add a handler to this event
	 *  
	 *  @param \SplObserver	$handler	Event handler
	 */
	public function attach( \SplObserver $handler ) {
		$name = \get_class( $handler );
		if ( \array_key_exists( $name, $this->handlers ) ) {
			return;
		}
		
		$this->handlers[$name] = $handler;
	}
	
	/**
	 *  Unregister handler from this event's notify list
	 *  
	 *  @param \SplObserver	$handler	Event handler
	 */
	public function detach( \SplObserver $handler ) {
		if ( \array_key_exists( $name, $this->handlers ) ) {
			unset( $this->handlers[$name] );
		}
	}
	
	/**
	 *  Run event and notify handlers
	 *  
	 *  @params array	$params		Optional event data
	 */
	public function notify( ?array $params = null ) {
		
		// Reset event params
		$this->params = $params ?? [];
		
		foreach ( $this->handlers as $handler ) {
			$handler->update( $this, $params );
			
			$this->output = 
			\array_merge( 
				$this->output, 
				$handler->data( $this->name ) ?? []
			);
		}
	}
	
	/**
	 *  Event parameters
	 *  
	 *  @return array
	 */
	public function data() : array {
		return $this->params;
	}
}
