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
	
	/**
	 *  Reset authenticated user data types for processing
	 *  
	 *  @param array	$user		Stored user in database/session
	 *  @return array
	 */
	protected static function formatAuthUser( array $user ) : array {
		return [
			'id'		=> ( int ) ( $user['id'] ?? 0 ), 
			'status'	=> ( int ) ( $user['status'] ?? 0 ), 
			'name'		=> $user['name'] ?? '', 
			'hash'		=> $user['hash'] ?? '', 
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
		$path	= $config->setting( 'cookiepath', 'string', '/' );
		
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
		$cexp	= $config->setting( 'cookie_exp', 'int' );
		
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
		$results	= $this->findUserByUsername( $username );
		
		// No user found?
		if ( empty( $results ) ) {
			$status = self::AUTH_STATUS_NOUSER;
			return [];
		}
		$user	= $results[0];
		
		// Verify credentials
		if ( \PubCabin\Core\User::verifyPassword( $password, $user->password ) ) {
			
			// Refresh password if needed
			if ( \PubCabin\Core\User::passNeedsRehash( $user->password ) ) {
				$this->savePassword( ( int ) $user->id, $password );
			}
			
			$status = self::AUTH_STATUS_SUCCESS;
			return $results;
		}
		
		// Login failiure
		$status = self::AUTH_STATUS_FAILED;
		return [];
	}
	
	/**
	 *  Get login details by username
	 *  
	 *  @param string		$username	User's login name as entered
	 *  @return array
	 */
	protected function findUserByUsername( string $username ) : array {
		$sql		= 
		"SELECT * FROM login_pass WHERE username = :user LIMIT 1;";
		$db		= $this->memberDb();
		$stm		= $db->prepare( $sql );
		$results	= 
		$this->getData()->getDataResult( 
			$db,
			[ ':user' => $username ], 
			'class,\\PubCabin\\Core\\User', 
			$stm
		);
		
		if ( empty( $results ) ) {
			return [];
		}
		return $results;
	}
	
	/**
	 *  Get profile details by id
	 *  
	 *  @param int			$id	User's id
	 *  @return array
	 */
	public function findUserById( int $id ) : array {
		$sql		= "SELECT * FROM users WHERE id = :id LIMIT 1;";
		$db		= $this->memberDb();
		$stm		= $db->prepare( $sql );
		$results	= 
		$this->getData()->getDataResult( 
			$db,
			[ ':id' => $id ], 
			'class,\\PubCabin\\Core\\User', 
			$stm
		);
		if ( empty( $results ) ) {
			return [];
		}
		return $results;
	}
	
	/**
	 *  Reset cookie lookup token and return new lookup
	 *  
	 *  @param int			$id	Logged in user's ID
	 *  @return string
	 */
	public function resetLookup( int $id ) : string {
		$db	= $this->memberDb();
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
		$sql	= 
		"SELECT * FROM login_view WHERE lookup = :lookup LIMIT 1;";
		
		$db	= $this->memberDb();
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
			$this->resetLookup( ( int ) $user['id'] );
			
		} elseif ( $expired ) {
			return [];
		}
		
		return $user;
	}
	
	/**
	 *  Set a new password for the user
	 *  
	 * 
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param int			$id	User's unique identifier
	 *  @param string		$param	Raw password as entered
	 *  @return bool
	 */
	protected function savePassword( 
		int		$id,
		string		$password 
	) : bool {
		$sql	= 
		"UPDATE users SET password = :password 
			WHERE id = :id";
		
		return 
		$this->getData()->setUpdate( $sql, [ 
			':password'	=> \PubCabin\Core\User::hashPassword( $password ), 
			':id'		=> $id 
		], static::dataName() );
	}
}


