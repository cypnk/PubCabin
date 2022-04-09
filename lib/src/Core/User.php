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
	protected $_info		= [];
	
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
	 *  Currently active public keys
	 *  @var array
	 */
	protected $_pub_keys	= [];
	
	
	
	
	
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
	
	/**
	 *  Authentication expiration date
	 *  @var string
	 */
	protected $_auth_expires;
	
	
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
				
			case 'public_keys':
				if ( \is_array( $value ) ) {
					$this->_pub_keys = $value;
				}
				break;
			
			case 'is_approved':
				$this->_is_approved = ( bool ) $value;
				break;
			
			case 'is_locked':
				$this->_is_locked = ( bool ) $value;
				break;
			
			case 'auth_expires':
				$this->_auth_expires = 
					\PubCabin\Util::utc( ( string ) $value );
				break;
			
			case 'info':
				$this->_info = 
					\is_array( $value ) ? $value : 
					\PubCabin\Util::encode( $value );
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
			
			case 'public_keys':
				return $this->_pub_keys;
			
			case 'is_approved':
				return $this->_is_approved ?? false;
			
			case 'is_locked':
				return $this->_is_locked ?? false;
			
			case 'auth_expires':
				return $this->_auth_expires ?? null;
			
			case 'info'
				return $this->_info ?? [];
				
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
	 *  @param int			$user		User unique identifier
	 *  @param string		$password	Raw entered password 
	 *  @return int
	 */
	public static function passwordAuth( 
		\PubCabin\Data $data, 
		int	$id
		string	$password 
	) : int {
		$res	= 
		$data->getSingle( 
			$id, 
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
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param string		$username	Login name to search
	 *  @param string		$password	User provided password
	 *  @param int			$status		Authentication success etc...
	 *  @return array
	 */
	public static function authByCredentials(
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
				static::savePassword( 
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
	 *  @param int			$id	User unique identifier
	 *  @param string		$param	Raw password as entered
	 *  @return bool
	 */
	public static function savePassword( 
		\PubCabin\Data	$data,
		int		$id, 
		string		$password 
	) : bool {
		$sql	= 
		"UPDATE users SET password = :password 
			WHERE id = :id";
		
		return 
		$data->setUpdate( $sql, [ 
			':password'	=> 
			\PubCabin\Crypto::hashPassword( $password ), 
			':id'		=> $id
		], static::MAIN_DATA );
	}
	
	/**
	 *  Authentication creation helper
	 * 
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param array		$info	Custom auth data
	 *  @return bool
	 */
	public function createAuth( 
		\PubCabin\Data	$data, 
		array		$info	= [] 
	) : bool {
		$params = [
			':user_id'	=> $this->id,
			':email'	=> 
				empty( $info ) ? 
					( $this->email ?? null ) : null,
			':info'		=> 
				empty( $info ) ? 
					\PubCabin\Util::encode( $this->info ) : 
					\PubCabin\Util::encode( $info ),
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
				is_approved, is_locked, provider_id, expires ) 
			VALUES( :user_id, :email, :info, 
				:is_approved, :is_locked, :provider_id, :expires )";
			$params[':provider_id'] = ( int ) $this->provider_id;
			
			// Third party logins may have an expiration
			$params[':expires'] = $this->auth_expires;
		}
		$id = $data->setInsert( $sql, $params, static::MAIN_DATA );
		
		return empty( $id ) ? false : true;
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
		$data->statement( $db, 
			"UPDATE logout_view SET lookup = '' 
				WHERE user_id = :id;" 
		);
		
		if ( $stm->execute( [ ':id' => $id ] ) ) {
			$stm->closeCursor();
			
			// SQLite should have generated a new random lookup
			$rst = 
			$data->prepare( $db, 
				"SELECT lookup FROM logins WHERE 
					user_id = :id;"
			);
			
			if ( $rst->execute( [ ':id' => $id ] ) ) {
				$col = $rst->fetchColumn();
				$rst->closeCursor();
				return $col;
			}
		}
		
		return '';
	}
	
	/**
	 *  Create public/secret keypair and return generated secret key
	 *  
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param int			$id		Logged in user's ID
	 *  @param string		$label		Public key short description
	 *  @param string		$expires	Key expiration date
	 *  @return string
	 */
	public static function createKeypair( 
		\PubCabin\Data	$data, 
		int		$id, 
		?string		$label,
		?string		$expires
	) : string {
		$keys = \PubCabin\Crypto::keypair();
		if ( empty( $keys ) ) {
			return '';
		}
		
		$sql	= 
		"INSERT INTO public_keys( user_id, public_key, label, expires ) 
			VALUES( :user_id, :public_key, :label, :expires );";
		$params = [
			':user_id'	=> $id,
			':public_key'	=> $keys['public'],
			':label'	=> empty( $label ) ? 
				null : \PubCabin\Util::title( $label ),
			':expires'	=> empty( $expires ) ? 
				null : \PubCabin\Util::utc( $expires )
		];
		
		return
		$data->setInsert( $sql, $params, static::MAIN_DATA ) ? 
			$keys['secret'] : '';
	}
	
	/**
	 *  Return given user's active (unexpired) public keys
	 * 
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param int			$id		Designated user ID
	 */
	public static function getPubKeys( 
		\PubCabin\Data	$data, 
		int		$id 
	) : array {
		$sql		= 
		"SELECT public_key FROM public_keys WHERE id = :id 
			AND (
				strftime( '%s', expires ) > 
				strftime( '%s', 'now' ) 
			);";
		$results	= 
		$data->getResults( 
			$sql, [ ':id' => $id ], static::MAIN_DATA
		);
		
		if ( empty( $results ) ) {
			return [];
		}
		$keys		= [];
		foreach ( $results as $res ) {
			$keys[] = $res['public_key'] ?? '';
		}
		return $keys;
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
		$sql	= 
		"SELECT * FROM login_view 
			WHERE lookup = :lookup LIMIT 1;";
		$db	= $data->getDb( static::MAIN_DATA );
		$stm	= $data->statement( $db, $sql );
		
		// First find lookup
		if ( $stm->execute( [ ':lookup' => $lookup ] ) ) {
			$results = $stm->fetchAll();
			$stm->closeCursor();
		}
		
		// No logins found
		if ( empty( $results ) ) {
			return [];
		}
		
		// One login found
		$user	= $results[0];
		$uptime	= \strtotime( $user['updated'] );
		if ( false === $uptime ) {
			$uptime = 0;
		}
		$expired = ( time() - $uptime ) > $cexp;
		
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
		'SELECT * FROM users WHERE id = :id LIMIT 1;';
		
		$db		= $data->getDb( static::MAIN_DATA );
		$stm		= $data->statement( $db, $sql );
		$result		= 
		$data->getDataResult( 
			$db,
			[ ':id' => $id ], 
			'class,\\PubCabin\\Core\\User', 
			$stm
		);
		$stm->closeCursor();
		return empty( $result ) ? [] : $result;
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
		'SELECT * FROM login_view WHERE name = :user LIMIT 1;';
		
		$db		= $data->getDb( static::MAIN_DATA );
		$stm		= $data->statement( $db, $sql );
		$result		= 
		$data->getDataResult( 
			$db,
			[ ':user' => $username ], 
			'class,\\PubCabin\\Core\\User', 
			$stm
		);
		$stm->closeCursor();
		return empty( $result ) ? [] : $result;
	}
	
	/**
	 *  Check if username or it's clean equivalent exists
	 *  
	 *  @param \PubCabin\Data	$data		Storage handler
	 *  @param string		$username	User's login name
	 *  @return bool
	 */
	public static function usernameExists(  
		\PubCabin\Data	$data, 
		string		$username 
	) : bool {
		$sql		= 
		"SELECT COUNT( id ) FROM users WHERE 
			username = :user OR user_clean = :clean;";
		
		$db		= $data->getDb( static::MAIN_DATA );
		$stm		= $data->statement( $db, $sql );
		$result		= 
		$data->getDataResult( 
			$db,
			[ 
				':user'		=> $username, 
				':clean'	=> 
				\PubCabin\Util::labelName( 
					$username, 
					\PubCabin\Util:strsize( $username )
				)
			], 
			'column',
			$stm
		);
		$stm->closeCursor();
		return empty( $result ) ? false : true;
	}
	
	/**
	 *  Update the last activity IP of the given user
	 *  Most of these actions use triggers in the database
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param \PubCabin\Config	$config	Current configuration
	 *  @param \PubCabin\Request	$req	Current vistor request
	 *  @param int			$id	User unique identifier
	 *  @param string		$mode	Activity type
	 *  @return bool
	 */
	public static function updateUserActivity(
		\PubCabin\Data		$data, 
		\PubCabin\Config	$config, 
		\PubCabin\Request	$req, 
		int			$id,
		?string			$mode = null
	) : bool {
		$now	= \PubCabin\Util::utc();
		$mode 	??= '';
		
		switch ( $mode ) {
			case 'active':
				$sql	= 
				"UPDATE auth_activity SET 
					last_ip		= :ip, 
					last_ua		= :ua, 
					last_session_id = :sess
					WHERE user_id = :id;";
				
				$params = [
					':ip'	=> $req->getIP(), 
					':ua'	=> $req->getUA(),
					':sess'	=> \session_id(), 
					':id'	=> $id
				];
				break;
				
			case 'login':
				$sql	= 
				"UPDATE auth_activity SET 
					last_ip		= :ip, 
					last_ua		= :ua, 
					last_login	= :login, 
					last_session_id = :sess
					WHERE user_id = :id;";
				
				$params = [
					':ip'	=> $req->getIP(), 
					':ua'	=> $req->getUA(),
					':login'=> $now,
					':sess'	=> \session_id(), 
					':id'	=> $id
				];
				break;
				
			case 'passchange':
				// Change table itself instead of the view
				$sql	= 
				"UPDATE user_auth SET 
					last_ip			= :ip, 
					last_ua			= :ua, 
					last_active		= :active,
					last_pass_change	= :change, 
					last_session_id		= :sess 
					WHERE user_id = :id;";
				
				$params = [
					':ip'		=> $req->getIP(), 
					':ua'		=> $req->getUA(),
					':active'	=> $now,
					':change'	=> $now,
					':sess'		=> \session_id(),
					':id'		=> $id
				];
				break;
				
			case 'failedlogin':
				$sql	= 
				"UPDATE auth_activity SET 
					last_ip			= :ip, 
					last_ua			= :ua, 
					last_session_id		= :sess, 
					failed_last_attempt	= :fdate
					WHERE user_id = :id;";
					
				$params = [
					':ip'		=> $req->getIP(), 
					':ua'		=> $req->getUA(),
					':sess'		=> \session_id(),
					':fdate'	=> $now,
					':id'		=> $id
				];
				break;
				
			case 'lock':
				$sql	= 
				"UPDATE auth_activity SET 
					is_locked = 1 WHERE id = :id;";
				$params	= [ ':id' => $id ];
				break;
				
			case 'unlock':
				$sql	= 
				"UPDATE user_auth SET 
					is_locked = 0 WHERE id = :id;";
				$params	= [ ':id' => $id ];
				break;
				
			case 'approve':
				$sql	= 
				"UPDATE user_auth SET 
					is_approved = 1 WHERE id = :id;";
				$params	= [ ':id' => $id ];
				break;
				
			case 'unapprove':
				$sql	= 
				"UPDATE user_auth SET 
					is_approved = 0 WHERE id = :id;";
				$params	= [ ':id' => $id ];
				break;
				
			default:
				// First run? Create or replace auth basics
				
				// Auto approve new auth?
				$ap = 
				$config->setting( 
					'auto_approve_reg', 
					'bool' 
				) ?? true;
				
				return 
				$data->setInsert( 
					"REPLACE INTO user_auth ( 
						user_id, last_ip, last_ua, 
						last_session_id, is_approved
					) VALUES( :id, :ip, :ua, :sess, :ap );", 
					[
						':id'	=> $id, 
						':ip'	=> $req->getIP(), 
						':ua'	=> $req->getUA(),
						':sess'	=> \session_id(),
						':ap'	=> $ap ? 1 : 0
					], 
					static::MAIN_DATA
				) ? true : false;
		}
		
		return 
		$data->setUpdate( $sql, $params, static::MAIN_DATA );
	}
}



