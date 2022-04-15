<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Sessions/SHandler.php
 *  @brief	Session handler
 */
namespace PubCabin\Modules\Sessions;

class SHandler {
	
	protected $config;
	
	protected $data;
	
	protected $controller;
	
	public function __construct( 
		\PubCabin\Data		$data,
		\PubCabin\Controller	$ctrl
	) {
		$this->data		= $data;
		$this->controller	= $ctrl;
		$this->config		= $ctrl->getConfig();
		
		// Can't change handler if content was sent
		if ( \headers_sent() ) {
			return;
		}
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
	}
	
	/**
	 *  Set close
	 */
	public function __destruct() {
		// Ensure cleanup if session is still active
		if ( \session_status() === \PHP_SESSION_ACTIVE ) {
			\session_write_close();
		}
	}
	
	/**
	 *  Database string helper
	 */
	protected function dsn() {
		static $dsn;
		
		if ( isset( $dsn ) ) {
			return $dsn;
		}
		$dsn = 
		\PubCabin\Util::slashPath( \PUBCABIN_DATA ) . 
			\PubCabin\Entity::SESSION_DATA;
		
		return $dsn;
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
		static $sql	= 
		"INSERT OR IGNORE INTO sessions ( session_id )
			VALUES ( :id );";
		
		$bt	= $this->config->setting( 'session_bytes', 'int' );
		$id	= \PubCabin\Util::genId( $bt );
		if ( $this->data->dataExec( 
			$sql, 
			[ ':id' => $id ], 
			'success', 
			$this->dsn()
		) ) {
			return $id;
		}
		
		// Something went wrong with the database
		\messages( 'error', 'Error writing to session ID to database' );
		die();
	}
	
	
	/**
	 *  Delete session
	 *  
	 *  @return bool
	 */
	public function sessionDestroy( $id ) {
		$sql	= "DELETE FROM sessions WHERE session_id = :id;";
		if ( $this->data->dataExec( 
			$sql, 
			[ ':id' => $id ], 
			'success', 
			$this->dsn() 
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
		if ( $this->data->dataExec( 
			$sql, 
			[ ':gc' => $max ], 
			'success', 
			$this->dsn() 
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
		static $sql	= 
		"SELECT session_data FROM sessions 
			WHERE session_id = :id LIMIT 1;";
		$out	= 
		$this->data->dataExec( 
			$sql, [ 'id' => $id ], 'column', $this->dsn() 
		);
		
		$this->controller->run( 
			'sessionread', 
			[ 'id' => $id, 'data' => $out ]
		);
		
		return empty( $out ) ? '' : ( string ) $out;
	}
	
	/**
	 *  Store session data
	 *  
	 *  @return bool
	 */
	public function sessionWrite( $id, $data ) {
		static $sql	= 
		"REPLACE INTO sessions ( session_id, session_data )
			VALUES( :id, :data );";
		
		if ( $this->data->dataExec( 
			$sql, 
			[ ':id' => $id, ':data' => $data ], 
			'success', 
			$this->dsn() 
		) ) {
			$this->controller->run( 
				'sessionwrite', 
				[ 'id' => $id, 'data' => $data ]
			);
			return true;
		}
		
		return false;
	}
}
