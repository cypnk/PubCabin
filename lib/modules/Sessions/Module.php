<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Sessions/Module.php
 *  @brief	Visitor session state and cookie handler
 */
namespace PubCabin\Modules\Sessions;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Session database DSN
	 */
	const SESSION_DATA	= 'session.db';
	
	public function dependencies() : array {
		return [ 'Hooks' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks	= $this->getModule( 'Hooks' );
		
		// Register session request start
		$hooks->event( [ 'request', [ $this, 'begin' ] ] );
	}
	
	/**
	 *  Register session start and end handler
	 */
	public function begin() {
		/**
		 *  Set session handler functions
		 */
		 \session_set_save_handler(
			[ $this, 'sessionOpen' ], 
			[ $this, 'sessionClose' ], 
			[ $this, 'sessionRead' ], 
			[ $this, 'sessionWrite' ], 
			[ $this, 'sessionDestroy' ], 
			[ $this, 'sessionGC' ], 
			[ $this, 'sessionCreateID' ]
		);
		\register_shutdown_function( 'session_write_close' );
	}
	
	/**
	 *  Does nothing
	 */
	public function sessionOpen( $path, $name ) { return true; }
	public function sessionClose() { return true; }
	
	/**
	 *  Create session ID in the database and return it
	 *  
	 *  @return string
	 */
	public function sessionCreateID() {
		$bt	= 
		$this->getConfig()->setting( 'session_bytes', 'int' );
		
		$id	= \PubCabin\Util::genId( $bt );
		$sql	= 
		"INSERT OR IGNORE INTO sessions ( session_id )
			VALUES ( :id );";
		$db	= $this->getData();
		
		if ( $db->dataExec( 
			$sql, 
			[ ':id' => $id ], 
			'success', 
			self::SESSION_DATA 
		) ) {
			return $id;
		}
		
		// Something went wrong with the database
		errors( 'Error writing to session ID to database' );
		die();
	}
	
	/**
	 *  Delete session
	 *  
	 *  @return bool
	 */
	public function sessionDestroy( $id ) {
		$sql	= "DELETE FROM sessions WHERE session_id = :id;";
		$db	= $this->getData();
		if ( $db->dataExec( 
			$sql, 
			[ ':id' => $id ], 
			'success', 
			self::SESSION_DATA 
		) ) {
			return true;
		}
		return false;
	}
	
	/**
	 *  Session garbage collection
	 *  
	 *  @return bool
	 */
	public function sessionGC( $max ) {
		$sql	= 
		"DELETE FROM sessions WHERE (
			strftime( '%s', 'now' ) - 
			strftime( '%s', updated ) ) > :gc;";
		$db	= $this->getData();
		if ( $db->dataExec( 
			$sql, 
			[ ':gc' => $max ], 
			'success', 
			self::SESSION_DATA 
		) ) {
			return true;
		}
		return false;
	}
	
	/**
	 *  Read session data by ID
	 *  
	 *  @return string
	 */
	public function sessionRead( $id ) {
		$sql	= 
		"SELECT session_data FROM sessions 
			WHERE session_id = :id LIMIT 1;";
		$db	= $this->getData();
		$data	= 
		$db->dataExec( 
			$sql, [ 'id' => $id ], 'column', self::SESSION_DATA 
		);
		
		$this->getModule( 'Hooks' )->event( [ 
			'sessionread', [ 'id' => $id, 'data' => $data ]
		] );
		
		return empty( $data ) ? '' : ( string ) $data;
	}
	
	/**
	 *  Store session data
	 *  
	 *  @return bool
	 */
	public function sessionWrite( $id, $data ) {
		$sql	= 
		"REPLACE INTO sessions ( session_id, session_data )
			VALUES( :id, :data );";
		$db	= $this->getData();
		if ( $db->dataExec( 
			$sql, 
			[ ':id' => $id, ':data' => $data ], 
			'success', 
			self::SESSION_DATA 
		) ) {
			$this->getModule( 'Hooks' )->event( [ 
				'sessionwrite', 
				[ 'id' => $id, 'data' => $data ]
			] );
			return true;
		}
		return false;
	}
	
	/**
	 *  Session owner and staleness marker
	 *  
	 *  @link https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
	 *  
	 *  @param string	$visit	Previous random visitation identifier
	 */
	public function sessionCanary( string $visit = '' ) {
		$config	= $this->getConfig();
		$bt	= $config->setting( 'session_bytes', 'int' );
		$exp	= $config->setting( 'session_exp', 'int' );
	
		$_SESSION['canary'] = 
		[
			'exp'		=> time() + $exp,
			'visit'		=> 
			empty( $visit ) ? 
				\PubCabin::Util::genId( $bt ) : $visit
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
	 *  Get collective cookie data
	 *  
	 *  @param string	$name		Cookie label name
	 *  @param mixed	$default	Default return if cookie isn't set
	 *  @return mixed
	 */
	public function getCookie( string $name, $default ) {
		$prefix	= $this->getRequest()->isSecure() ? 
			'__Host-' : '';
		
		$app	= 
		$prefix . $this->getConfig()->setting( 'appname', 'string' );
		
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
	public function makeCookie( string $name, $data, array $options = [] ) : bool {
		$prefix	= $this->getRequest()->isSecure() ? 
			'__Host-' : '';
		$options	= $this->defaultCookieOptions( $options );
		$app		= 
		$prefix . $this->getConfig()->setting( 'appname', 'string' );
		
		$this->getModule( 'Hooks' )->event( [ 
			'sessioncookieparams', $options 
		] );
		
		return 
		\setcookie( $app . "[$name]", $data, $options );
	}
	
	/**
	 *  Remove preexisting cookie
	 *  
	 *  @param string	$name		Cookie label
	 *  @return bool
	 */
	function deleteCookie( string $name ) : bool {
		$this->getModule( 'Hooks' )->event( [ 
			'cookiedelete', [ 'name' => $name ] 
		] );
		
		return $this->makeCookie( $name, '', [ 'expires' => 1 ] );
	}
	
	/**
	 *  Samesite cookie origin setting
	 *  
	 *  @return string
	 */
	public function sameSiteCookie() : string {
		if ( $this->getConfig()->setting( 'cookie_restrict', 'bool' ) ) {
			return 'Strict';
		}
		
		return $this->getRequest()->isSecure() ? 'None' : 'Lax';
	}

	
	/**
	 *  Set the cookie options when defaults are/aren't specified
	 *  
	 *  @param array	$options	Additional cookie options
	 *  @return array
	 */
	public function defaultCookieOptions( array $options = [] ) : array {
		$config	= $this->getConfig();
		$cexp	= $config->setting( 'cookie_exp', 'int' );
		$cpath	= $config->setting( 'cookie_path', 'string' );
		$req	= $this->getRequest();
		$opts	= 
		\array_merge( $options, [
			'expires'	=> 
				( int ) ( $options['expires'] ?? time() + $cexp ),
			'path'		=> $cpath,
			'domain'	=> $req->getHost(),
			'samesite'	=> $this->sameSiteCookie(),
			'secure'	=> $req->isSecure() ? true : false,
			'httponly'	=> true
		] );
		
		$this->getModule( 'Hooks' )->event( [ 
			'cookieparams', $opts
		] );
		
		return $opts;
	}
	
	/**
	 *  Set session cookie parameters
	 *  
	 *  @return bool
	 */
	public function sessionCookieParams() : bool {
		$options		= $this->defaultCookieOptions();
	
		// Override some defaults
		$options['lifetime']	=  
			$this->getConfig()->setting( 'cookie_exp', 'int' );
		unset( $options['expires'] );
		
		$this->getModule( 'Hooks' )->event( [ 
			'sessioncookieparams', $opts 
		] );
		
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
		
		$hooks	= $this->getModule( 'Hooks' );
		$prefix	= $this->getRequest()->isSecure() ? 
			'__Host-' : '';
		
		if ( \session_status() !== \PHP_SESSION_ACTIVE ) {
			$this->sessionCookieParams();
			\session_name( 
				$prefix . $this->getConfig()->setting( 'appname' ), 
				'string'
			);
			\session_start();
			
			$hooks->event( [ 
				'sessioncareated', 
				[ 'id' => \session_id() ]
			] );
		}
		
		if ( $reset ) {
			\session_regenerate_id( true );
			foreach ( \array_keys( $_SESSION ) as $k ) {
				unset( $_SESSION[$k] );
			}
			
			$hooks->event( [ 'sessiondestroyed', [] ] );
		}
	}
}



