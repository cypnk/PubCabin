<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Memebership/Module.php
 *  @brief	User authentication and profile management
 */
namespace PubCabin\Modules\Membership;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Login authentication modes
	 */
	public const AUTH_STATUS_SUCCESS	= 0;
	public const AUTH_STATUS_FAILED		= 1;
	public const AUTH_STATUS_NOUSER		= 2;
	public const AUTH_STATUS_BANNED		= 3;
	
	/**
	 *  Currently authenticated user data
	 *  @var array
	 */
	protected $user;
	
	/**
	 *  Cookie set range path
	 *  @var string
	 */
	private $cookie_path;
	
	/**
	 *  Cookie expiration limit
	 *  @var int
	 */
	private $cookie_exp;
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Sites', 'Menues', 'Forms' ];
	}
	
	protected function getSession {
		return $this->getModule( 'Sessions' );
	}
	
	/**
	 *  Reset authenticated user data types for processing
	 *  
	 *  @param array	$user		Stored user in database/session
	 *  @return array
	 */
	protected static function formatAuthUser( array $user ) : array {
		$user['is_approved']	??= false;
		$user['is_locked']	??= false;
		
		return [
			'id'		=> ( int ) ( $user['id'] ?? 0 ), 
			'status'	=> ( int ) ( $user['status'] ?? 0 ), 
			'name'		=> $user['name'] ?? '', 
			'hash'		=> $user['hash'] ?? '',
			'is_approved'	=> 
				$user['is_approved'] ? true : false,
			'is_locked'	=> 
				$user['is_locked'] ? true : false, 
			'auth'		=> $user['auth'] ?? ''
		];
	}
	
	/**
	 *  Current cookie path base URL helper
	 *  
	 *  @return string
	 */
	public function cookiePath() : string {
		if ( isset( $this->cookie_path ) ) {
			return $this->cookie_path;
		}
		
		$config	= $this->getConfig();
		$path	= 
		$config->setting( 'cookiepath', 'string' ) ?? '/';
		
		$this->cookie_path	= 
		\PubCabin\Util::slashPath( 
			empty( $path ) ? 	
				'/' : \PubCabin\Util::cleanUrl( $path )
		);
		
		return $this->cookie_path;
	}
	
	/**
	 *  Get currently configured cookie duration
	 *   
	 *  @return int
	 */
	public function cookieExp() : int {
		if ( isset( $this->cookie_exp ) ) {
			return $this->cookie_exp;
		}
		
		$config	= $this->getConfig();
		$cexp	= 
		$config->setting( 'cookie_exp', 'int' ) ?? 604800;
		
		$this->cookie_exp	= 
		\PubCabin\Util::intRange( $cexp, 3600, 2147483647 );
		
		return $this->cookie_exp;
	}
	
	/**
	 *  Current member database name
	 *  
	 *  @return string
	 */
	protected static function dataName() : string {
		return \PubCabin\Modules\Module::MAIN_DATA;
	}
	
	/**
	 *  Current member database helper
	 */
	protected function memberDb() {
		return 
		$this->getData()->getDb( static::dataName() );
	}
	
	/**
	 *  Login user credentials
	 *  
	 *  @param string	$username	Login name to search
	 *  @param string	$password	User provided password
	 *  @param int		$status		Authentication success etc...
	 *  @return array
	 */
	public function authByCredentials(
		string		$username,
		string		$password,
		int		&$status
	) : array {
		\PubCabin\Core\User::findUserByUsername(
			$this->getData(),
			$username,
			$password,
			$status
		);
	}
	
	/**
	 *  Get login details by username
	 *  
	 *  @param string		$username	User's login name as entered
	 *  @return array
	 */
	protected function findUserByUsername( string $username ) : array {
		return 
		\PubCabin\Core\User::findUserByUsername(
			$this->getData(), 
			$username
		);
	}
	
	/**
	 *  Get profile details by id
	 *  
	 *  @param int			$id	User's id
	 *  @return array
	 */
	public function findUserById( int $id ) : array {
		return 
		\PubCabin\Core\User::findUserById( 
			$this->getData(), 
			$id 
		);
	}
	
	/**
	 *  Reset cookie lookup token and return new lookup
	 *  
	 *  @param int			$id	Logged in user's ID
	 *  @return string
	 */
	public function resetLookup( int $id ) : string {
		return 
		\PubCabin\Core\User::resetLookup(
			$this->getData(), 
			$id
		);
	}
	
	/**
	 *  Find user authorization by cookie lookup
	 *  
	 *  @param string		$lookup	Raw cookie lookup term
	 *  @param int			$cexp	Cookie expiration
	 *  @param bool			$reset	Reset lookup if expired
	 *  @return array
	 */
	public function findCookie( 
		string		$lookup, 
		int		$cexp, 
		bool		$reset		= false
	) : array {
		return 
		\PubCabin\Core\User::findCookie(
			$this->getData(), 
			$lookup,
			$cexp,
			$reset
		);
	}
	
	/**
	 *  Apply user auth session and save the current browser info
	 *  
	 *  @param array	$user		User info stored in database
	 *  @param bool		$cookie		Set auth cookie if true
	 */
	public function setAuth( array $user, bool $cookie ) {
		$session = $this->getSession();
		$session->sessionCheck();
		
		$auth			= 
		\hash( 'tiger160,4', 
			$this->getRequest()->getUA() . $user['hash'] 
		);
		
		// Set user session data
		$_SESSION['user']	= [
			'id'		=> $user['id'],
			'status'	=> $user['status'],
			'name'		=> $user['name'],
			'is_approved'	=> $user['is_approved'],
			'is_locked'	=> $user['is_locked'],
			'auth'		=> $auth
		];
		
		if ( $cookie ) {
			// Set cookie lookup code
			$session->makeCookie( 'user', $user['lookup'] );
		}
	}
	
	/**
	 *  End user session
	 */
	public function endAuth() {
		$session = $this->getSession();
		$session->sessionCheck( true );
		
		// Delete existing auth
		$this->authUser( true );
		
		// Delete lookup cookie
		$session->deleteCookie( 'user' );
	}
	
	/**
	 *  Check user authentication session
	 *  
	 *  @param bool		$delete		Forget existing auth if true
	 *  @return array
	 */
	public function authUser( bool $delete = false ) : array {
		$session = $this->getSession();
		$session->sessionCheck();
	
		if ( $delete ) {
			unset( $this->user );
			return [];
		}
		
		if ( isset( $this->user ) ) {
			return $this->user;
		}
		if ( 
			empty( $_SESSION['user'] ) || 
			!\is_array(  $_SESSION['user'] ) 
		) { 
			// Session was empty? Check cookie lookup
			$cookie	= $session->getCookie( 'user', '' );
			if ( empty( $cookie ) ) {
				return [];
			}
			// Sane defaults
			if ( \PubCabin\Util::strsize( $cookie ) > 255 ) {
				return [];
			}
			
			$user	= 
			$session->findCookie( 
				\PubCabin\Util::pacify( $cookie ) 
			);
			
			// No cookie found?
			if ( empty( $user ) ) {
				return [];
			}
			
			// Reset data types
			$user	= static::formatAuthUser( $user );
			
			// User found, apply authorization
			$this->setAuth( $user, true );
			
			// Update last activity
			$this->updateUserActivity( 
				$user['id'], 'active' 
			);
			$this->user = $user;
			return $_SESSION['user'];
			
		} else {
			// Fetched results must be a 6-item array
			$user	= $_SESSION['user'];
			if ( \count( $user ) !== 6 ) { 
				$_SESSION['user']	= '';
				return []; 
			}
		}
		
		$user = static::formatAuthUser( $user );
		
		// Check if current browser changed since auth token creation
		$auth			= 
		\hash( 'tiger160,4', 
			$this->getRequest()->getUA() . $user['hash'] 
		);
		
		if ( 0 != \strcmp( ( string ) $user['auth'], $auth ) ) { 
			return []; 
		}
		$this->updateUserActivity( $user['id'], 'active' );
		
		$this->user = $user;
		return $this->user;
	}
	
	/**
	 *  Update the last activity of the current user
	 *  
	 *  @param int		$id	User unique identifier
	 *  @param string	$mode	Activity type
	 *  @return bool
	 */
	public function updateUserActivity(
		string	$mode	= '' 
	) : bool {
		$id	= ( int ) ( $this->user['id'] ?? 0 );
		if ( empty( $id ) ) {
			return false;
		}
		
		\PubCabin\Core\User::updateUserActivity( 
			$this->getData(),
			$this->getConfig(),
			$this->getRequest(),
			$id,
			$mode
		);
	}
}


