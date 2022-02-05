<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/User.php
 *  @brief	Core membership object
 */

namespace PubCabin\Core;

class User extends \PubCabin\Entity {
	
	/**
	 *  Login authentication modes
	 */
	public const AUTH_STATUS_SUCCESS	= 0;
	public const AUTH_STATUS_FAILED		= 1;
	public const AUTH_STATUS_NOUSER		= 2;
	public const AUTH_STATUS_BANNED		= 3;
	
	/**
	 *  Access login name
	 *  @var string
	 */
	public $username;
	
	/**
	 *  Access credentials
	 *  @var string
	 */
	public $password;
	
	/**
	 *  Friendly name
	 *  @var string
	 */
	public $display;
	
	/**
	 *  Brief profile description
	 *  @var string
	 */
	public $bio;
	
	/**
	 *  Authentication token
	 *  @var string
	 */
	public $hash;
	
	/**
	 *  Cookie search string
	 *  @var string
	 */
	public $lookup;
	
	
	
	
	/**
	 *  Auth parameters
	 */
	
	/**
	 *  External authentication provider identifier
	 *  @var int
	 */
	public $provider_id;
	
	/**
	 *  Authentication info
	 */
	public $info		= '';
	
	/**
	 *  Contact for reminders, resets
	 *  @var string
	 */
	public $email;
	
	/**
	 *  Temporary token
	 *  @var string
	 */
	public $mobile_pin;
	
	/**
	 *  Authentication providers
	 *  @var array
	 */
	protected $_auth	= [];
	
	/**
	 *  User access roles list
	 *  @var array
	 */
	protected $_roles	= [];
	
	
	
	
	
	/**
	 *  Authentication activity
	 */
	
	/**
	 *  Last known IP address (IPv4 or IPv6)
	 *  @var string
	 */
	public $last_ip;
	
	/**
	 *  Last activity timestamp
	 *  @var string
	 */
	public $last_active;
	
	/**
	 *  Last successful login timestamp
	 *  @var string
	 */
	public $last_login;
	
	/**
	 *  Last credential change timestamp
	 *  @var string
	 */
	public $last_pass_change;
	
	/**
	 *  Last access lockdown timestamp 
	 *  E.G. for too many login attempts
	 *  @var string
	 */
	public $last_lockout;
	
	/**
	 *  Privileges can be assigned if true
	 *  @var bool
	 */
	protected $_is_approved;
	
	/**
	 *  Authenticated access denied if true
	 *  @var bool
	 */
	protected $_is_locked;
	
	/**
	 *  Number of failed login attempts
	 *  @var int
	 */
	public $failed_attempts		= 0;
	
	/**
	 *  Starting timestamp of current batch of failed login attempts
	 *  @var string
	 */
	public $failed_last_start;
	
	/**
	 *  Last failed login attempt timestamp
	 *  @var string
	 */
	public $failed_last_attempt;
	
	// TODO
	public function save( \PubCabin\Data $data ) : bool {
		if ( isset( $this->id ) ) {
			
		} else {
			
		}
		
		return true;
	}
	
	public function __set( $name, $value ) {
		
		switch ( $name ) {
			// Sub structure identification
			case 'user_id':
				if ( !isset( $this->id ) ) {
					$this->id = ( int ) $value;
				}
				break;
			
			// Alias of username
			case 'name':
				if ( !isset( $this->username ) ) {
					$this->username = 
						( string ) $value;
				}
				break;
			
			case 'auth_providers':
				if ( \is_array( $value ) ) {
					$this->_auth = $value;
				}
				break;
			
			case 'roles':
				if ( \is_array( $value ) ) {
					$this->_roles = $value;
				}
				break;
			
			case 'is_approved':
				$this->_is_approved = ( bool ) $value;
				break;
			
			case 'is_locked':
				$this->_is_locked = ( bool ) $value;
				break;
				
			default:
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		
		switch ( $name ) {
			case 'user_id':
				return $this->id ?? null;
				
			case 'name':
				return $this->username ?? null;
			
			case 'auth_providers':
				return $this->_auth;
			
			case 'roles':
				return $this->_roles;
			
			case 'is_approved':
				return $this->_is_approved ?? false;
			
			case 'is_locked':
				return $this->_is_locked ?? false;
				
			default:
				return parent::__get( $name );
		}
	}
	
	/**
	 *  Create or update user
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @return bool			True on success
	 */
	public function save( \PubCabin\Data $data ) : bool {
		$params	= [
			':display'	=> $this->display ?? '',
			':bio'		=> $this->bio ?? '',
			':settings'	=> \PubCabin\Util::encode( $this->settings ),
			':status'	=> ( int ) ( $this->status ?? 0 )
		];
		
		// New user
		if ( empty( $this->id ) ) {
			// Provider must be set if not using password to login
			if ( empty( $this->password ) && empty( $this->provider_id ) ) {
				return false;	
			}
			
			$sql	= 
			"INSERT INTO users ( display, bio, settings, status, username, password ) 
				VALUES( :display, :bio, :settings, :status, :username, :password )";
			$params[':username']	= $this->username;
			$params[':password']	= empty( $this->password ) ? 
				'' : \PubCabin\Crypto::hashPasword( $this->password );
			
			$this->id = 
			$data->setInsert( $sql, $params, static::MAIN_DATA );
			
			$ok	= empty( $this->id ) ? false : true;
			
			// Create basic auth info
			if ( $ok ) {
				$this->createAuth( $data );
			}
			return $ok;
		} 
		
		// Editing existing user
		$params[':id'] => $this->id;
		$sql	= 
		"UPDATE users SET display = :display, bio = :bio, 
			settings = :settings, status = :status 
			WHERE id = :id;";
		
		return $data->setUpdate( $sql, $params, static::MAIN_DATA );
	}
	
	/**
	 *  Authenticate loaded user with given password
	 *  
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param string		$password	Raw entered password 
	 *  @return int
	 */
	public function passwordAuth( 
		\PubCabin\Data $data, 
		string $password 
	) : int {
		if ( empty( $this->id ) ) {
			return false;
		}
		
		$res	= 
		$data->getSingle( 
			$this->id, 
			"SELECT password FROM users WHERE id = :id", 
			static::MAIN_DATA 
		);
		
		return  
		\PubCabin\Crypto::verifyPassword( 
			$password, 
			$res['password'] ?? '' 
		) ? self::AUTH_STATUS_SUCCESS : self::AUTH_STATUS_FAILED;
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
		\PubCabin\Data	$data, 
		string		$username,
		string		$password,
		int		&$status
	) : array {
		$user = static::findUserByUsername( $data, $username );
		
		// No user found?
		if ( empty( $user ) ) {
			$status = self::AUTH_STATUS_NOUSER;
			return [];
		}
		
		// Verify credentials
		if ( \PubCabin\Crypto::verifyPassword( 
			$password, $user['password'] 
		) ) {
			
			// Refresh password if needed
			if ( \PubCabin\Crypto::passNeedsRehash( 
				$user['password'] 
			) ) {
				$this->savePassword( 
					$data, 
					( int ) $user['id'], 
					$password 
				);
			}
			
			$status = self::AUTH_STATUS_SUCCESS;
			return $user;
		}
		
		// Login failiure
		$status = self::AUTH_STATUS_FAILED;
		return [];
	}
	
	/**
	 *  Set a new password for the user
	 *  
	 * 
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param string		$param	Raw password as entered
	 *  @return bool
	 */
	public function savePassword( 
		\PubCabin\Data	$data, 
		string		$password 
	) : bool {
		if ( !isset( $this->id ) ) {
			return false;	
		}
		
		$sql	= 
		"UPDATE users SET password = :password 
			WHERE id = :id";
		
		return 
		$data->setUpdate( $sql, [ 
			':password'	=> 
				\PubCabin\Crypto::hashPassword( $password ), 
			':id'		=> ( int ) $this->id 
		], static::MAIN_DATA );
	}
	
	/**
	 *  Authentication creation helper
	 * 
	 *  @param \PubCabin\Data	$data	Storage handler
	 */
	private function createAuth( \PubCabin\Data $data ) {
		$params = [
			':user_id'	=> $this->id,
			':email'	=> $this->email ?? '',
			':info'		=> $this->info ?? '',
			':is_approved'	=> $this->is_approved ? 1 : 0,
			':is_locked'	=> $this->is_locked ? 1 : 0
		];
		
		// Local password login
		if ( empty( $this->provider_id ) ) {
			$sql	= 
			"INSERT INTO user_auth ( user_id, email, info, is_approved, is_locked ) 
				VALUES( :user_id, :email, :info, :is_approved, :is_locked )";
			
		// Third party login
		} else {
			$sql	= 
			"INSERT INTO user_auth ( user_id, email, info, 
				is_approved, is_locked, provider_id ) 
			VALUES( :user_id, :email, :info, 
				:is_approved, :is_locked, :provider_id )";
			$params[':provider_id'] = ( int ) $this->provider_id;
		}

		$data->setInsert( $sql, $params, static::MAIN_DATA );	
	}
	
	/**
	 *  Reset cookie lookup token and return new lookup
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param int			$id	Logged in user's ID
	 *  @return string
	 */
	public static function resetLookup( 
		\PubCabin\Data	$data, 
		int		$id 
	) : string {
		$db	= $data->getDb( static::MAIN_DATA );
		$stm	= 
		$db->prepare( 
			"UPDATE logout_view SET lookup = '' 
				WHERE user_id = :id;" 
		);
		
		if ( $stm->execute( [ ':id' => $id ] ) ) {
			// SQLite should have generated a new random lookup
			$rst = 
			$db->prepare( 
				"SELECT lookup FROM logins WHERE 
					user_id = :id;"
			);
			
			if ( $rst->execute( [ ':id' => $id ] ) ) {
				return $stm->fetchColumn();
			}
		}
		
		return '';
	}
	
	/**
	 *  Find user authorization by cookie lookup
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param string		$lookup	Raw cookie lookup term
	 *  @param int			$cexp	Cookie expiration
	 *  @param bool			$reset	Reset lookup if expired
	 *  @return array
	 */
	public static function findCookie( 
		\PubCabin\Data	$data, 
		string		$lookup, 
		int		$cexp, 
		bool		$reset		= false
	) : array {
		$sql	= "SELECT * FROM login_view
			WHERE lookup = :lookup LIMIT 1;";	
		$db	= $data->getDb( static::MAIN_DATA );
		$stm	= $db->prepare( $sql );
		
		// First find lookup
		if ( $stm->execute( [ ':lookup' => $lookup ] ) ) {
			$results = $stm->fetchAll();
		}
		
		// No logins found
		if ( empty( $results ) ) {
			return [];
		}
		
		// One login found
		$user	= $results[0];
		$xpired = 
		( time() - ( ( int ) $user['updated'] ) ) > $cexp;
		
		// Check for cookie expiration
		if ( $reset && $expired ) {
			$user['lookup']	= 
			static::resetLookup( ( int ) $user['id'] );
			
		} elseif ( $expired ) {
			return [];
		}
		
		return $user;
	}
	
	/**
	 *  Get profile details by id
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param int			$id	User's id
	 *  @return array
	 */
	public static function findUserById(
		\PubCabin\Data	$data, 
		int		$id 
	) : array {
		$sql		= 
		"SELECT * FROM users WHERE id = :id LIMIT 1;";
		$results	= 
		$data->getResults( 
			$sql, [ ':id' => $id ], static::MAIN_DATA
		);
		if ( empty( $results ) ) {
			return [];
		}
		return $results[0];
	}
	
	/**
	 *  Get login details by username
	 *  
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param string		$username	User's login name as entered
	 *  @return array
	 */
	public static function findUserByUsername( 
		\PubCabin\Data	$data, 
		string		$username 
	) : array {
		$sql		= 
		"SELECT * FROM login_pass WHERE username = :user LIMIT 1;";
		$results	= 
		$data->getResults( 
			$sql, [ ':user' => $username ], static::MAIN_DATA 
		);
		
		if ( empty( $results ) ) {
			return [];
		}
		return $results[0];
	}
}



