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
	public function save() {
		
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
}



