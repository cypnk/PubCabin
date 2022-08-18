<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Data.php
 *  @brief	Database storage handler
 */
namespace PubCabin;

class Data {
	
	const SUB_SQL_MAX	= 20;
	
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
	 *  Main event controller
	 *  @var \PubCabin\Controller
	 */
	protected $controller;
	
	/**
	 *  SQL Installation file sources
	 *  @var array
	 */
	protected $install_dir	= [];
	
	
	/**
	 *  Data class begin
	 *  
	 *  @param \PubCabin\Controller	$ctrl	Event controller
	 */
	public function __construct( \PubCabin\Controller $ctrl ) {
		$this->controller	= $ctrl;
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
			\messages( 'error', $e );
		}
	}
	
	/**
	 *  Set SQL installation file source
	 *  
	 *  @param string	$def	Bare database name
	 *  @param string	$root	Source file destination
	 *  @return bool
	 */
	public function installDir( string $def, string $root ) : bool {
		if ( empty( $def ) ) {
			\messages( 'error', 'Empty SQL install file' );
			return false;
		}
		
		$chk = 
		\PubCabin\FileUtil::filterDir( $root . $def, $root );
		if ( empty( $chk ) ) {
			\messages( 
				'error', 
				'Setting install SQL file failed: ' . 
				$def 
			);
			return false;
		}
		
		$this->install_dir[$def] = $root;
		return true;
	}
	
	/**
	 *  Load SQL installation file contents
	 *  
	 *  @param string	$def	Definition source file
	 *  @param string	$root	Custom sub install SQL file location
	 *  @return string
	 */
	public function sqlFile( string $def, string $root = '' ) : string {
		$err	= [];
		$def	= \ltrim( $def, '/\\' );
		
		// Empty source implies common data dir
		$this->install_dir[$def] ??= '';
		
		$r = empty( $root );
		
		$file	= $r ? 
		\PubCabin\FileUtil::loadFile( 
			$def . '.sql', $this->install_dir[$def], $err 
		) : '';
		
		// Load default installation
		if ( empty( $file ) && !$r ) {
			\messages( 
				'error', 
				'Loading SQL install file failed: ' . 
					$def . ' From: ' . 
					$this->install_dir[$def]
			);
			$this->_err = 
			\array_merge( $this->_err, $err );
			
			return '';
		}
		
		$t = '';
		$f = '';
		
		// Load and append any sub install files
		if ( $r ) {
			for ( $i = 0; $i < static::SUB_SQL_MAX; $i++ ) {
				$f = $def . '.install.' . $i .'.sql';
				
				if ( !\file_exists( 
					\PubCabin\Util::slashPath( $this->install_dir[$def] ) . $f 
				) ) {
					break;
				}
				
				$t =
				\PubCabin\FileUtil::loadFile( 
					$f, $this->install_dir[$def], $err 
				);
				if ( empty( $t ) ) {
					break;	
				}
			
				$file .= "\n" . $t;
		} else {
			for ( $i = 0; $i < static::SUB_SQL_MAX; $i++ ) {
				$f = $def . '.install.' . $i .'.sql';
				if ( !\file_exists( \PubCabin\Util::slashPath( $root ) . $f ) ) {
					break;
				}
				
				$t = \PubCabin\FileUtil::loadFile( $f, $root, $err );
				if ( empty( $t ) ) {
					break;	
				}
			
				$file .= "\n" . $t;
			}
		}
		
		return $file;
	}
	
	/**
	 *  Get the SQL definition from DSN
	 *  
	 *  @param string	$dsn	User defined database path
	 *  @return array
	 */
	public function loadSQL( string $dsn ) : array {
		// Get the name component from the full database path
		$def	= 
		\str_contains( $dsn, '/' ) ?
			\end( \explode( '/', $dsn ) ) : 
			\substr( $dsn, \strlen( \PUBCABIN_DATA ) - 1 );
		
		if ( false === $def ) {
			return [];
		}
		
		$src	= $this->sqlFile( $def );
		if ( empty( $src ) ) {
			\messages( 
				'error', 
				'Loading SQL install file failed: ' . $dsn
			);
			return [];
		}
		
		// SQL Lines from definition
		return \PubCabin\FileUtil::lines( $src, -1, false );
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
		
		// Configuration from current controller
		$config		= $this->controller->getConfig();
		
		// First time? SQLite database will be created
		$first_run	= !\file_exists( $dsn );
		$timeout	= 
			$config->setting( 'data_timeout', 'int' );
		
		$opts		= [
			\PDO::ATTR_TIMEOUT		=> $timeout,
			\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
			\PDO::ATTR_PERSISTENT		=> false,
			\PDO::ATTR_EMULATE_PREPARES	=> false,
			\PDO::ATTR_AUTOCOMMIT		=> false,
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
			$this->controller->run( 
				'dbcreated', [ 'dsn' => $dsn ] 
			);
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
		
		$opt	= \PubCabin\Util::trimmedList( $rtype );
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
			$stm->closeCursor();
			
		} catch( \PDOException $e ) {
			
			$this->_err[] = 
				'PDO Exception in ' . 
				__CLASS__ . ' @ ' . 
				__FUNCTION__ . ' ' . 
				$e->getMessage() ?? '';
		}
		
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
				return [];
			}
			
			$stm	= $this->statement( $db, $sql );
			foreach ( $params as $p ) {
				$res[]	= 
				$this->getDataResult( 
					$db, $params, $rtype, $stm 
				);
			}
			$stm->closeCursor();
			$db->commit();
			
		} catch( \PDOException $e ) {
			
			$this->_err[] = 
				'PDO Exception in ' . 
				__CLASS__ . ' @ ' . 
				__FUNCTION__ . ' ' . 
				$e->getMessage() ?? '';
		}
		return \PubCabin\Util::arrayFormat( $res );
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
	 *  Get list of tables from specified database
	 *  
	 *  @param string	$dsn	Database string
	 *  @return array		List of tables
	 */
	public function getTables( string $dsn = \DATA ) : array {
		static $sql = 
		"SELECT tables FROM ( 
			SELECT * FROM sqlite_schema 
			UNION ALL SELECT * FROM sqlite_temp_schema 
		) WHERE type = 'table' ORDER BY name;";
		
		$res = $this->dataExec( $sql, [], 'results', $dsn );
		
		return \PubCabin\Util::arrayFormat( $res );
	}
	
	/**
	 *  Get table definition
	 * 
	 *  @param string	$name	Raw table name
	 *  @param string	$dsn	Database string
	 */
	public function getTableInfo( 
		string		$name, 
		string		$dsn		= \DATA 
	) : array {
		$name = \PubCabin\Util::labelName( $name );
		if ( empty( $name ) ) {
			return [];
		}
		$db	= $this->getDb( $dsn );
		$res	= 
		$db->query(
			'PRAGMA schema.table_info(' . $name . ')'
		) ?? [];
		
		return \PubCabin\Util::arrayFormat( $res );
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
		return \PubCabin\Util::arrayFormat( $res );
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


