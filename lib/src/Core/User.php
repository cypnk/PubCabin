<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/User.php
 *  @brief	Core membership object
 */

namespace PubCabin\Core;

class User extends \PubCabin\Entity {
	
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
			$params[':password']	= static::hashPasword( $this->password ?? '' );
			
			$this->id = 
			$data->setInsert( $sql, $params, static::MAIN_DATA );
			
			$ok	= empty( $this->id ) ? false : true;
			
			// Create basic auth info
			if ( $ok ) {
				$this->createAuth();
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
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param string	$password	Raw entered password 
	 *  @return bool
	 */
	public function passwordAuth( 
		\PubCabin\Data $data, 
		string $password 
	) : bool {
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
		static::verifyPassword( 
			$password, 
			$res['password'] ?? '' 
		);
	}
	
	/**
	 *  Set a new password for the user
	 *  
	 *  @param string	$param		Raw password as entered
	 *  @return bool
	 */
	public function savePassword( 
		\PubCabin\Data $data, 
		string $password 
	) : bool {
		if ( !isset( $this->id ) ) {
			return false;	
		}
		
		$sql	= 
		"UPDATE users SET password = :password 
			WHERE id = :id";
		
		return 
		$data->setUpdate( $sql, [ 
			':password'	=> static::hashPassword( $password ), 
			':id'		=> ( int ) $this->id 
		], static::MAIN_DATA );
	}
	
	/**
	 *  Authentication creation helper
	 */
	private function createAuth() {
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
	 *  Hash password to storage safe format
	 *  
	 *  @param string	$password	Raw password as entered
	 *  @return string
	 */
	public static function hashPassword( string $password ) : string {
		return 
		\base64_encode(
			\password_hash(
				\base64_encode(
					\hash( 'sha384', $password, true )
				),
				\PASSWORD_DEFAULT
			)
		);
	}
	
	/**
	 *  Check hashed password
	 *  
	 *  @param string	$password	Password exactly as entered
	 *  @param string	$stored		Hashed password in database
	 */
	public static function verifyPassword( 
		string		$password, 
		string		$stored 
	) : bool {
		if ( empty( $stored ) ) {
			return false;
		}
		
		$stored = \base64_decode( $stored, true );
		if ( false === $stored ) {
			return false;
		}
		
		return 
		\password_verify(
			\base64_encode( 
				\hash( 'sha384', $password, true )
			),
			$stored
		);
	}
	
	/**
	 *  Check if user password needs rehashing
	 *  
	 *  @param string	$stored		Already hashed, stored password
	 *  @return bool
	 */
	public static function passNeedsRehash( 
		string		$stored 
	) : bool {
		$stored = \base64_decode( $stored, true );
		if ( false === $stored ) {
			return false;
		}
		
		return 
		\password_needs_rehash( $stored, \PASSWORD_DEFAULT );
	}
	
}



