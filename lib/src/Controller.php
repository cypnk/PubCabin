<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Controller.php
 *  @brief	Main event notification dispatcher
 */
namespace PubCabin;

class Controller {
	
	/**
	 *  Registered list of events
	 *  @var array
	 */
	protected $events	= [];
	
	/**
	 *  Loaded list of handlers
	 *  @var array
	 */
	protected $handlers	= [];
	
	/**
	 *  Running errors
	 *  @var array
	 */
	protected $err		= [];
	
	/**
	 *  Handler which failed to load
	 *  @var array
	 */
	protected $failed	= [];
	
	/**
	 *  Main configuration
	 *  @var \PubCabin\Config
	 */
	protected $config;
	
	
	/**
	 *  Initiator
	 *  
	 *  @param \PubCabin\Config	$config	Configuration settings
	 */
	public function __construct( \PubCabin\Config $config ) {
		$this->config	= $config;
		
		// Get preinstalled default modules
		$modules	= 
		$config->setting( 'default_modules', 'list' ) ?? [];
		
		// Register 'begin' event with default modules
		foreach ( $modules as $m ) {
			$this->register( 'begin', $m );
		}
	}
	
	/**
	 *  Handle cleanup
	 */
	public function __destruct() {
		// Dump any errors to log
		if ( !empty( $this->err ) ) {
			\messages( 'error', 
				\implode( ', ', $this->err ) );
		}
	}
	
	/**
	 *  Event naming helper, allows safe characters, lowercase
	 *  
	 *  @param string	$name	Raw event name
	 *  @return string
	 */
	public static function eventName( string $name ) : string {
		return 
		\PubCabin\Util::lowercase(
			\PubCabin\Util::bland( $name, true )
		);
	}
	
	/**
	 *  Restrict handler name to PubCabin/Modules namespace 
	 *  
	 *  @param string	$name	Plain handler name
	 */
	protected static function handlerName( string $name ) : string {
		return 
		\PubCabin\Handler::subclass( 
			[ 'Modules', $name, 'Module' ] 
		);
	}
	
	/**
	 *  Module loading helper
	 *  
	 *  @param string	$name	Name in PubCabin/Modules namespace
	 */
	protected function load( string $name ) {
		// Skip if already loaded or failed the first time
		if ( 
			\array_key_exists( $name, $this->handlers ) ||
			\in_array( $name, $this->failed )
		) {
			return;
		}
		
		// Convert to namespace
		$handler	= static::handlerName( $name );
		
		try {
			// Load dependencies first
			$deps = $handler::dependencies();
			foreach ( $deps as $d ) {
				$this->load( $d );
			}
			
			// Load handler
			$this->handlers[$name] = new $handler( $this );
		} catch( \Exception $e ) {
			$this->failed[]	= $name;
			$this->err[]	= $e->getMessage();
		}
	}
	
	/**
	 *  Create new event if it's not already registered
	 *  
	 *  @param string	$name		Singular event name
	 */
	protected function makeEvent( string $name ) {
		if ( \array_key_exists( $name, $this->events ) ) {
			return;
		}
		$this->events[$name] = 
			new Event( $this, $name );
	}
	
	/**
	 *  Attach event to loaded handler
	 *  
	 *  @param string	$name		Event name
	 *  @param string	$handler	Base handler name
	 */
	protected function attachEvent( string $name, string $handler ) {
		if ( !\array_key_exists( $name, $this->events ) ) {
			return;
		}
		if ( \array_key_exists( $name, $this->handlers ) ) {
			return;
		}
		
		$this->events[$name]->attach( 
			$this->handlers[$handler] 
		);
	}
	
	/**
	 *  Register a new handler, optionally load it, attach to event
	 *  
	 *  @param mixed	$name		Event name(s)
	 *  @param string	$handler	Handler module name	
	 */
	public function register( $name, string $handler ) {
		
		// Create event if not in list
		if ( \is_array( $name ) ) {
			foreach( $name as $n ) {
				if ( !\is_string( $n ) ) {
					continue;
				}
				$this->makeEvent( $n );
			}
		} elseif ( \is_string( $name ) ) {
			$this->makeEvent( $name );
		} else {
			return;
		}
		
		// Preload handler
		$this->load( $handler );
		
		// Attach to observers
		if ( \is_array( $name ) ) {
			foreach( $name as $n ) {
				if ( !\is_string( $n ) ) {
					continue;
				}
				$this->attachEvent( $n, $handler );
			}
		} else {
			$this->attachEvent( $name, $handler );
		}
		
	}
	
	/**
	 *  Get configuration
	 *  
	 *  @return \PubCabin\Config
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 *  Base names of currently loaded handlers
	 *  
	 *  @return array
	 */
	public function loadedModules() : string {
		return \array_keys( $this->handlers );
	}
	
	/**
	 *  Event data parameters from last notification
	 *  
	 *  @param string	$name	Event label
	 *  @return array
	 */
	public function params( string $name ) : array {
		$name	= static::eventName( $name );
		if ( \array_key_exists( $name, $this->events ) ) {
			return $this->events[$name]->data();
		}
		
		return [];
	}
	
	/**
	 *  Combined handler output for given event
	 *  
	 *  @param string	$name	Event label
	 *  @return array
	 */
	public function output( string $name, array $params = [] ) : array {
		$name	= static::eventName( $name );
		$out	= [];
		$res	= [];
		foreach ( $this->handlers as $k => $handler ) {
			// Find any output
			$res = $handler->data( $name );
			if ( empty( $res ) ) {
				continue;
			}
			$out = \array_merge_recursive( $out, $res );
		}
		
		return $out;
	}
	
	/**
	 *  String return helper
	 *  
	 *  @param string	$name	Event label
	 */
	public function stringResult( string $name ) : string {
		return 
		\array_shift( $this->output( $name ) ?? [] ) ?? '';
	}
	
	/**
	 *  Get HTML from notification result, if sent
	 *  
	 *  @param string	$event		Notification event name
	 *  @param string	$default	Fallback html content
	 *  @return string
	 */
	public function htmlResult( string $name, string $default = '' ) : string {
		return $this->output( $event )['html'] ?? $default;
	}
	
	/**
	 *  Get HTML render template from notification result, if sent
	 *  
	 *  @param string	$event		Hook event name
	 *  @param string	$default	Fallback template
	 *  @param array	$input		Component to apply template to
	 *  @param bool		$full		Render full regions
	 *  @return string
	 */
	public function templateRender(
		string		$event, 
		string		$default,
		array		$input,
		bool		$full		= false
	) {
		// Get render from begin event
		$render = $this->output( 'begin' )['render'] ?? null;
		
		if ( \is_null( $render ) ) {
			return '';
		}
		
		return 
		$render->render(
			$this->output( $event )['template'] ?? 
				$this->stringResult( $event, $default ),
			$input,
			$full
		);
	}
	
	/**
	 *  Wrap component region in 'before' and 'after' event hooks and their output
	 *  
	 *  @param string	$before		Before template parsing event
	 *  @param string	$after		After template parsing event
	 *  @param string	$tpl		Base component template
	 *  @param array	$input		Raw component data
	 *  @param bool		$full		Render full regions
	 *  @return string
	 */
	public function wrap( 
		string		$before, 
		string		$after, 
		string		$tpl		= '', 
		array		$input		= [],
		bool		$full		= false
	) {
		// Send "before" event notification
		$this->run( $before, [
			'data'		=> $input,
			'template'	=> $tpl,
			'full'		=> $full
		] );
		
		// Prepend any HTML output and render the new ( or old ) template
		$html	= 
		$this->htmlResult( $before ) . 
			$this->templateRender( $before, $tpl, $input, $full );
		
		$this->run( $after, [ 
			'data'		=> $input,	// Raw component data
			'before'	=> $before,	// Event called before
			'html'		=> $html,	// Current HTML
			'full'		=> $full,	// Full region render
			'template'	=> $tpl		// New or previously replaced
		] );
		
		// Send any replaced HTML or already rendered HTML
		return $this->htmlResult( $after, $html );
	}
	
	/**
	 *  Post 'begin' event housekeeping
	 */
	private function postBegin() {
		static $begin	= false;
		// Don't run again
		if ( $begin ) {
			return;
		}
		
		$begin = true;
		// Run default modules loaded event
		$this->run( 'modulesloaded', [
			'modules'	=> 
			$this->config->setting( 'default_modules', 'list' ) ?? []
		] );
	}
	
	/**
	 *  Notify handlers of a given event
	 *  
	 *  @param string	$name		Running event name
	 *  @param array	$params		Optional event parameter
	 */
	public function run( string $name, ?array $params = null ) {
		$name		= static::eventName( $name );
		// If event was not registered, do nothing
		if ( !\array_key_exists( $name, $this->events ) ) {
			return;
		}
		
		// Run event
		$this->events[$name]->notify( $params );
		
		// Begin event is special
		if ( 0 == \strcmp( $name, 'begin' ) ) {
			$this->postBegin();
		}
	}
}
