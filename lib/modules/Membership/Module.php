<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Memebership/Module.php
 *  @brief	User authentication and profile management
 */
namespace PubCabin\Modules\Membership;

class Module extends \PubCabin\Modules\Module {
	
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
	protected function dataName() : string {
		return \PubCabin\Modules\Module::MAIN_DATA;
	}
	
	/**
	 *  Current member database helper
	 */
	protected function memberDb() {
		return 
		$this->getData()->getDb( $this->dataName() );
	}
}


