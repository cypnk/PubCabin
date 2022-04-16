<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Sessions/Module.php
 *  @brief	Visitor session state and cookie handler
 */
namespace PubCabin\Modules\Sessions;

class Module extends \PubCabin\Handler {
	
	/**
	 *  '__Host-', '__Secure-', or empty cookie prefix
	 *  @var array
	 */
	protected static $prefix_list = [
		'none'		=> '__',
		'host'		=> '__Host-',
		'secure'	=> '__Secure-'
	];
	
	/**
	 *  Registered trigger events for this module
	 *  @var array
	 */
	protected static $events	= [
		'modulesloaded',
		'sessioncheck',
		'getcookie',
		'makecookie',
		'deletecookie',
		'cleansession',
		'shutdown'
	];
	
	/**
	 *  Currently active cookie prefix
	 *  @var string
	 */
	protected $cookie_prefix;
	
	/**
	 *  Base dependencies
	 *  
	 *  @return array
	 */
	public static function dependencies() : array {
		return [ "Base" ];
	}
	
	/**
	 *  Handle notifications
	 *  
	 *  @param \PubCabin\Event		$event	Notification handler
	 *  @param \PubCabin\Params	$params	Optional staring properties
	 */
	public function update( \SplSubject $event, ?array $params = null ) {
		$ctrl	= &$this->controller;
		$params	??= $event->data();
		
		switch ( $event->name() ) {
			case 'begin':
				// Register loaded event
				$ctrl->register( static::$events, 'Sessions' );
				break;
				
			case 'modulesloaded':
				if ( \headers_sent() ) {
					break;
				}
				
				$data	= $ctrl->output( 'begin' )['data'];
				
				// Set install dir
				$data->installDir( 
					\PubCabin\Entity::SESSION_DATA,
					static::resourcePath( 
						$this, 'install', '' 
					)
				);
				
				// Register session start and handler
				$sess	= 
				new \PubCabin\Modules\Sessions\SHandler( 
					$data,
					$ctrl
				);
				
				$this->sessionCheck();
				
				// Run session started event
				$ctrl->run( 'sessionstarted' );
				break;
				
			case 'sessioncheck':
				$this->sessionCheck();
				break;
				
			case 'getcookie':
				// Cookie event needs parameters
				if ( empty( $params['name'] ) ) {
					break;
				}
				
				$data = 
				getCookie( 
					$params['name'], 
					$params['default'] ?? ''
				);
				
				// Cookie search results
				$ctrl->run( 'cookiesearch', [
					$params['name'] => $data
				] );
				break;
			
			case 'makecookie':
				if ( empty( $params['name'] ) ) {
					break;
				}
				
				$params['data']		??= '';
				$params['options']	??= [];
				
				$this->makeCookie( 
					$params['name'], 
					$params['data'], 
					$params['options']
				);
				$ctrl->run( 'cookiemade', $params );
				
				break;
			
			case 'deletecookie':
				if ( empty( $params['name'] ) ){
					break;
				}
				$this->deleteCookie( $params['name'] );
				$ctrl->run( 
					'cookiedeleted', 
					[ $params['name'] ] 
				);
				break;
				
			case 'cleansession':
				$this->cleanSession();
				break;
				
			case 'shutdown':
				$this->shutdown();
				break;
		}
	}
	
	/**
	 *  Session owner and staleness marker
	 *  
	 *  @link https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
	 *  
	 *  @param string	$visit	Previous random visitation identifier
	 */
	protected function sessionCanary( string $visit = '' ) {
		$config	= $this->controller->getConfig();
		$bt	= $config->setting( 'session_bytes', 'int' );
		$exp	= $config->setting( 'session_exp', 'int' );
	
		$_SESSION['canary'] = 
		[
			'exp'		=> time() + $exp,
			'visit'		=> 
			empty( $visit ) ? 
				\PubCabin\Util::genId( $bt ) : $visit
		];
	}
	
	/**
	 *  Check session staleness
	 *  
	 *  @param bool		$reset	Reset session and canary if true
	 */
	public function sessionCheck( bool $reset = false ) {
		$this->session( $reset );
		if ( empty( $_SESSION['canary'] ) ) {
			$this->sessionCanary();
			return;
		}
		
		if ( time() > ( int ) $_SESSION['canary']['exp'] ) {
			$visit = $_SESSION['canary']['visit'];
			\session_regenerate_id( true );
			$this->sessionCanary( $visit );
		}
	}
	
	/**
	 *  End current session activity
	 */
	public function cleanSession() {
		if ( \session_status() === \PHP_SESSION_ACTIVE ) {
			\session_unset();
			\session_destroy();
			\session_write_close();
		}
	}
	
	/**
	 *  Session and cookie helpers
	 */
	
	/**
	 *  Prefixed cookie name helper
	 *  
	 *  @return string
	 */
	public function cookiePrefix() : string {
		if ( isset( $this->cookie_prefix ) ) {
			return $this->cookie_prefix;
		}
		
		$ctrl	= $this->controller;
		$req	= $ctrl->output( 'begin' )['request'];
		$cpath	= $ctrl->getConfig()->setting( 'cookie_path', 'string' );
		
		// Enable locking if connection is secure and path is '/'
		$this->cookie_prefix = 
		( 0 === \strcmp( $cpath, '/' ) && $req->isSecure() ) ? 
			static::$prefix_list['host'] : ( 
				$req->isSecure() ? 
					static::$prefix_list['secure'] : 
					static::$prefix_list['none'] 
			);
		
		return $this->cookie_prefix;
	}
	
	/**
	 *  Get collective cookie data
	 *  
	 *  @param string	$name		Cookie label name
	 *  @param mixed	$default	Default return if cookie isn't set
	 *  @return mixed
	 */
	public function getCookie( string $name, $default ) {
		$app	= 
		$this->cookiePrefix() . 
		$this->controller->getConfig()->setting( 'appname', 'string' );
		
		if ( !isset( $_COOKIE[$app] ) ) {
			return $default;
		}
		
		if ( !is_array( $_COOKIE[$app]) ) {
			return $default;
		}
		
		return $_COOKIE[$app][$name] ?? $default;
	}
	
	/**
	 *  Set application cookie
	 *  
	 *  @param int		$name		Cookie data label
	 *  @param mixed	$data		Cookie data
	 *  @param array	$options	Cookie settings and options
	 *  @return bool
	 */
	public function makeCookie( 
		string		$name, 
				$data, 
		array		$options	= [] 
	) : bool {
		$options	= $this->defaultCookieOptions( $options );
		$app		= 
		$this->cookiePrefix() . 
		$this->controller->getConfig()->setting( 'appname', 'string' );
		
		$this->controller->run( 'sessioncookieparams', $options );
		
		return 
		\setcookie( $app . "[$name]", $data, $options );
	}
	
	/**
	 *  Remove preexisting cookie
	 *  
	 *  @param string	$name		Cookie label
	 *  @return bool
	 */
	protected function deleteCookie( string $name ) : bool {
		$this->controller->run( 
			'cookiedelete', [ 'name' => $name ] 
		);
		
		return $this->makeCookie( $name, '', [ 'expires' => 1 ] );
	}
	
	/**
	 *  Samesite cookie origin setting
	 *  
	 *  @return string
	 */
	public function sameSiteCookie() : string {
		$ctrl	= $this->controller;
		if ( $ctrl->getConfig()->setting( 'cookie_restrict', 'bool' ) ) {
			return 'Strict';
		}
		
		return 
		$ctrl->output( 'begin' )['request']->isSecure() ? 
			'None' : 'Lax';
	}
	
	/**
	 *  Set the cookie options when defaults are/aren't specified
	 *  
	 *  @param array	$options	Additional cookie options
	 *  @return array
	 */
	public function defaultCookieOptions( array $options = [] ) : array {
		$ctrl	= &$this->controller;
		$config	= $ctrl->getConfig();
		$cexp	= $config->setting( 'cookie_exp', 'int' );
		$cpath	= $config->setting( 'cookie_path', 'string' );
		$req	= $ctrl->output( 'begin' )['request'];
		
		$opts	= 
		\array_merge( $options, [
			'expires'	=> 
				( int ) ( $options['expires'] ?? time() + $cexp ),
			'path'		=> $cpath,
			'samesite'	=> $this->sameSiteCookie(),
			'secure'	=> $req->isSecure() ? true : false,
			'httponly'	=> true
		] );
		
		// Domain shouldn't be used when using host prefixed cookies
		$prefix = $this->cookiePrefix();
		if ( 
			empty( $prefix ) || 
			( 0 === \strcmp( $prefix, static::$prefix_list['secure'] ) ) 
		) {
			$opts['domain']	= $req->getHost();
		}
		
		$ctrl->run( 'cookieparams', $opts );
		return $opts;
	}
	
	/**
	 *  Set session cookie parameters
	 *  
	 *  @return bool
	 */
	public function sessionCookieParams() : bool {
		$options		= $this->defaultCookieOptions();
		$config			= $this->controller->getConfig();
		
		// Override some defaults
		$options['lifetime']	=  
			$config->setting( 'cookie_exp', 'int' );
		unset( $options['expires'] );
		
		$this->controller->run( 'sessioncookieparams', $options );
		return \session_set_cookie_params( $options );
	}
	
	/**
	 *  Initiate a session if it doesn't already exist
	 *  Optionally reset and destroy session data
	 *  
	 *  @param bool		$reset		Reset session ID if true
	 */
	public function session( $reset = false ) {
		\session_cache_limiter( '' );
		if ( \session_status() === \PHP_SESSION_ACTIVE && !$reset ) {
			return;
		}
		
		$config	= $this->controller->getConfig();
		
		if ( \session_status() !== \PHP_SESSION_ACTIVE ) {
			$this->sessionCookieParams();
			\session_name( 
				$this->cookiePrefix() . 
				$config->setting( 'appname', 'string' ) 
			);
			\session_start();
			
			$this->controller->run(
				'sessioncareated', 
				[ 'id' => \session_id() ]
			);
		}
		
		if ( $reset ) {
			\session_regenerate_id( true );
			foreach ( \array_keys( $_SESSION ) as $k ) {
				unset( $_SESSION[$k] );
			}
			
			$this->controller->run( 'sessiondestroyed' );
		}
	}
	
	/**
	 *  Carry out cleanup
	 */
	protected function shutdown() {
		if ( \session_status() === \PHP_SESSION_ACTIVE ) {
			\session_write_close();
		}
	}
}



