<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Data.php
 *  @brief	Database storage handler
 */
namespace PubCabin;

class Data {
	
	/**
	 *  Database connections
	 *  @var array
	 */
	protected static $db	= [];
	
	/**
	 *  Retrieval or other errors
	 *  @var array
	 */
	protected $_err		= [];
	
	/**
	 *  PDO Statement storage
	 *  @var array
	 */
	protected $_stmcache	= [];
	
	/**
	 *  Configuration store
	 *  @var \PubCabin\Config
	 */
	protected $config;
	
	/**
	 *  Data class begin
	 *  
	 *  @param \PubCabin\Config	$config	Main configuration handler
	 */
	public function __construct( Config $config ) {
		$this->config	= $config;
	}
	
	/**
	 *  Handle cleanup
	 */
	public function __destruct() {
		\array_map( 
			function( $v ) { return null; }, 
			$this->_stmcache 
		);
		$this->_stmcache = null;
		
		$this->getDb( '', 'closeall' );
		
		// Log any pending errors
		foreach ( $this->_err as $e ) {
			errors( $e );
		}
	}
	
	/**
	 *  Get the SQL definition from DSN
	 *  
	 *  @param string	$dsn	User defined database path
	 *  @return array
	 */
	public function loadSQL( string $dsn ) : array {
		// Get the first component from the definition
		// E.G. "main" from "main.db"
		$def	= \explode( '.', $dsn )[0];
		
		$src	= 
		FileUtil::loadFile( 
			Util::lowercase( $dsn ) . '.sql' 
		);
		if ( empty( $src ) ) {
			return [];
		}
		
		// SQL Lines from definition
		return FileUtil::lines( $src, -1, false );
	}
	
	/**
	 *  Create database tables based on DSN
	 *  
	 *  @param object	$db	PDO Database object
	 *  @param string	$dsn	Database path associated with PDO object
	 */
	public function installSQL( \PDO $db, string $dsn ) {
		$parse	= [];
		
		$lines	= $this->loadSQL( $dsn );
		if ( empty( $lines ) ) {
			return;
		}
		
		// Filter SQL comments and lines starting PRAGMA
		foreach ( $lines as $l ) {
			if ( \preg_match( '/^(\s+)?(--|PRAGMA)/is', $l ) ) {
				continue;
			}
			$parse[] = $l;
		}
		
		// Separate into statement actions
		$qr	= \explode( '-- --', \implode( " \n", $parse ) );
		foreach ( $qr as $q ) {
			if ( empty( trim( $q ) ) ) {
				continue;
			}
			$db->exec( $q );
		}
	}
	
	/**
	 *  Get database connection
	 *  
	 *  @param string	$dsn	Connection string
	 *  @param string	$mode	Return mode
	 *  @return mixed		PDO object if successful or else null
	 */
	public function getDb( string $dsn, string $mode = 'get' ) {
		switch( $mode ) {
			case 'close':	
				if ( isset( static::$db[$dsn] ) ) {
					static::$db[$dsn] = null;
					unset( static::$db[$dsn] );
				}
				return;
			
			case 'closeall':
				foreach( static::$db as $k => $v  ) {
					static::$db[$k] = null;
					unset( static::$db[$k] );
				}
				return;
				
			default:
				if ( empty( $dsn ) ) {
					return null;
				}
		}
		
		if ( isset( static::$db[$dsn] ) ) {
			return static::$db[$dsn];
		}
		
		// First time? SQLite database will be created
		$first_run	= !\file_exists( $dsn );
		
		$opts	= [
			\PDO::ATTR_TIMEOUT		=> 
				$this->config->setting( 
					'data_timeout', 'int' 
				),
			\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
			\PDO::ATTR_PERSISTENT		=> false,
			\PDO::ATTR_EMULATE_PREPARES	=> false,
			\PDO::ATTR_ERRMODE		=> 
				\PDO::ERRMODE_EXCEPTION
		];
		
		try {
			static::$db[$dsn]	= 
			new \PDO( 'sqlite:' . $dsn, null, null, $opts );
		} catch ( \PDOException $e ) {
			$this->_err[] = 
				'Error connecting to database ' . $dsn . 
				' Messsage: ' . $e->getMessage() ?? 'PDO Exception';
			die();
		}
		
		// Preemptive defense
		static::$db[$dsn]->exec( 'PRAGMA quick_check;' );
		static::$db[$dsn]->exec( 'PRAGMA trusted_schema = OFF;' );
		static::$db[$dsn]->exec( 'PRAGMA cell_size_check = ON;' );
		
		// Prepare defaults if first run
		if ( $first_run ) {
			static::$db[$dsn]->exec( 'PRAGMA encoding = "UTF-8";' );
			static::$db[$dsn]->exec( 'PRAGMA page_size = "16384";' );
			static::$db[$dsn]->exec( 'PRAGMA auto_vacuum = "2";' );
			static::$db[$dsn]->exec( 'PRAGMA temp_store = "2";' );
			static::$db[$dsn]->exec( 'PRAGMA secure_delete = "1";' );
			
			// Load and process SQL
			$this->installSQL( static::$db[$dsn], $dsn );
			
			// Instalation check
			static::$db[$dsn]->exec( 'PRAGMA integrity_check;' );
			static::$db[$dsn]->exec( 'PRAGMA foreign_key_check;' );
		}
		
		static::$db[$dsn]->exec( 'PRAGMA journal_mode = WAL;' );
		static::$db[$dsn]->exec( 'PRAGMA foreign_keys = ON;' );
		
		if ( $first_run ) {
			// TODO Hooks
		}
		
		return static::$db[$dsn];
	}
	
	
	/**
	 *  Helper to get the result from a successful statement execution
	 *  
	 *  @param PDO		$db	Database connection
	 *  @param array	$params	Parameters 
	 *  @param string	$rtype	Return type
	 *  @param PDOStatement	$stm	PDO prepared statement
	 *  @return mixed
	 */
	public function getDataResult( 
		\PDO		$db, 
		array		$params,
		string		$rtype, 
		\PDOStatement	$stm 
	) {
		$ok = false;
		try {
			$ok	= 
			empty( $params ) ? 
				$stm->execute() : 
				$stm->execute( $params );
				
		} catch ( \PDOException $e ) {
			
			$this->_err[] = 
				'PDO Exception in ' . 
				__CLASS__ . ' @ ' . 
				__FUNCTION__ . ' ' . 
				$e->getMessage() ?? '';
		}
		
		$opt	= Util::trimmedList( $rtype );
		switch ( $opt[0] ) {
			// Query with array return
			case 'results':
				return 
				$ok ? $stm->fetchAll() : [];
			
			// Insert with ID return
			case 'insert':
				return 
				$ok ? $db->lastInsertId() : 0;
			
			// Single column value
			case 'column':
				return 
				$ok ? $stm->fetchColumn() : '';
			
			// Total or count number
			case 'count':
				return 
				$ok ? ( int ) $stm->fetchColumn() : 0;
			
			// Single class
			case 'item':
				return $ok ? 
				$stm->fetchObject(
					empty( $opt[1] ) ? 
					 __CLASS__ : $opt[1]
				) : null;
				
			// Class array
			case 'class':
			case 'classes':
				return $ok ? 
				$stm->fetchAll( 
					\PDO::FETCH_CLASS, 
					empty( $opt[1] ) ? 
					 __CLASS__ : $opt[1] 
				) : [];
			
			// Success status
			default:
				return $ok ? true : false;
		}
	}
	
	/**
	 *  Get or create cached PDO Statements
	 *  
	 *  @param PDO		$db	Database connection
	 *  @param string	$sql	Query string or statement
	 *  @return PDOStatement
	 */
	public function statement( \PDO $db, string $sql ) : \PDOStatement {
		if ( isset( $this->_stmcache[$sql] ) ) {
			return $this->_stmcache[$sql];
		}
		
		$this->_stmcache[$sql] = $db->prepare( $sql );
		return $this->_stmcache[$sql];
	}
	
	/**
	 *  Shared data execution routine
	 *  
	 *  @param string	$sql	Database SQL
	 *  @param array	$params	Parameters 
	 *  @param string	$rtype	Return type
	 *  @param string	$dsn	Database string
	 *  @return mixed
	 */
	public function dataExec(
		string		$sql,
		array		$params,
		string		$rtype,
		string		$dsn
	) {
		$db	= $this->getDb( $dsn );
		$res	= null;
	
		try {
			$stm	= $this->statement( $db, $sql );
			$res	= $this->getDataResult( $db, $params, $rtype, $stm );
			
		} catch( \PDOException $e ) {
			
			$this->_err[] = 
				'PDO Exception in ' . 
				__CLASS__ . ' @ ' . 
				__FUNCTION__ . ' ' . 
				$e->getMessage() ?? '';
		}
		
		$stm	= null;
		return $res;
	}
	
	/**
	 *  Update or insert multiple database rows at once with single SQL
	 *  
	 *  @param string	$sql	Database SQL update query
	 *  @param array	$params	Collection of query parameters
	 *  @param string	$rtype	Return type
	 *  @param string	$dsn	Database string
	 *  @return array		Result status
	 */
	public function dataBatchExec (
		string		$sql,
		array		$params,
		string		$rtype,
		string		$dsn		= \DATA
	) : array {
		$db	= $this->getDb( $dsn );
		$res	= [];
		
		try {
			if ( !$db->beginTransaction() ) {
				return false;
			}
			
			$stm	= $this->statement( $db, $sql );
			foreach ( $params as $p ) {
				$res[]	= 
				$this->getDataResult( 
					$db, $params, $rtype, $stm 
				);
			}
			$db->commit();
			
		} catch( \PDOException $e ) {
			
			$this->_err[] = 
				'PDO Exception in ' . 
				__CLASS__ . ' @ ' . 
				__FUNCTION__ . ' ' . 
				$e->getMessage() ?? '';
		}
		$stm = null;
		return \is_array( $res ) ? $res : [];
	}
	
	/**
	 *  Helper to turn a range of input values into an IN() parameter
	 *  
	 *  @example Parameters for [value1, value2] become "IN (:paramIn_0, :paramIn_1)"
	 *  
	 *  @param array	$values		Raw parameter values
	 *  @param array	$params		PDO Named parameters sent back
	 *  @param string	$prefix		SQL Prepended fragment prefix
	 *  @param string	$prefix		SQL Appended fragment suffix
	 *  @return string
	 */
	public function getInParam(
		array		$values, 
		array		&$params, 
		string		$prefix		= 'IN (', 
		string		$suffix		= ')'
	) : string {
		$sql	= '';
		$p	= '';
		$i	= 0;
			
		foreach ( $values as $v ) {
			$p		= ':paramIn_' . $i;
			$sql		.= $p .',';
			$params[$p]	= $v;
			
			$i++;
		}
		
		// Remove last comma and close parenthesis
		return $prefix . \rtrim( $sql, ',' ) . $suffix;
	}
	
	/**
	 *  Get parameter result from database
	 *  
	 *  @param string	$sql	Database SQL query
	 *  @param array	$params	Query parameters
	 *  @param string	$dsn	Database string
	 *  @return array		Query results
	 */
	public function getResults(
		string		$sql, 
		array		$params		= [],
		string		$dsn		= \DATA
	) : array {
		$res = 
		$this->dataExec( $sql, $params, 'results', $dsn );
		return 
		empty( $res ) ? [] : ( \is_array( $res ) ? $res : [] );
	}
	
	/**
	 *  Create database update
	 *  
	 *  @param string	$sql	Database SQL update query
	 *  @param array	$params	Query parameters (required)
	 *  @param string	$dsn	Database string
	 *  @return bool		Update status
	 */
	public function setUpdate(
		string		$sql,
		array		$params,
		string		$dsn		= \DATA
	) : bool {
		$res = $this->dataExec( $sql, $params, 'success', $dsn );
		return empty( $res ) ? false : true;
	}
	
	/**
	 *  Insert record into database and return last ID
	 *  
	 *  @param string	$sql	Database SQL insert
	 *  @param array	$params	Insert parameters (required)
	 *  @param string	$dsn	Database string
	 *  @return int			Last insert ID
	 */
	public function setInsert(
		string		$sql,
		array		$params,
		string		$dsn		= \DATA
	) : int {
		$res = $this->dataExec( $sql, $params, 'insert', $dsn );
		
		return 
		empty( $res ) ? 
			0 : ( \is_numeric( $res ) ? ( int ) $res : 0 );
	}
	
	/**
	 *  Get a single item row by ID
	 *  
	 *  @return array
	 */
	public function getSingle(
		int		$id,
		string		$sql,
		string		$dsn		= \DATA
	) : array {
		$data	= $this->getResults( $sql, [ ':id' => $id ], $dsn );
		if ( empty( $data ) ) {
			return $data[0];
		}
		return [];
	}
}


