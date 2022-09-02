<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Entity.php
 *  @brief	Common data object
 */
namespace PubCabin;

abstract class Entity {
	
	/**
	 *  Core database file names
	 */
	
	/**
	 *  Main database name
	 */
	const MAIN_DATA			= 'main.db';
	
	/**
	 *  Session database name
	 */
	const SESSION_DATA		= 'session.db';
	
	/**
	 *  Cache database name
	 */
	const CACHE_DATA		= 'cache.db';
	
	/**
	 *  Data logging database name
	 */
	const LOGS_DATA			= 'logs.db';
	
	/**
	 *  Moderation and content filtering database name
	 */
	const FILTER_DATA		= 'filter.db';
	
	/**
	 *  Database index primary key (set once)
	 *  @var int
	 */
	public readonly int $id;
	
	/**
	 *  Unique identifier (set once)
	 *  @var string
	 */
	public readonly string $uuid;
	
	/**
	 *  Configuration settings
	 *  @var array 
	 */
	private array $_settings;
	
	/**
	 *  Settings identifier
	 *  @var int
	 */
	private int $_settings_id;
	
	/**
	 *  New settings ID if swapping settings
	 *  @var int
	 */
	private int $_n_settings_id;
	
	/**
	 *  Created UTC datetime stamp
	 *  @var string
	 */
	public string $created;
	
	/**
	 *  Last modified UTC datetime stamp
	 *  @var string
	 */
	public string $updated;
	
	/**
	 *  Trigger events
	 *  @var array
	 */
	public array $events		= [];
	
	/**
	 *  Sorting order
	 *  @var int
	 */
	private int $_sort_order;
	
	/**
	 *  Special handling status, based on object type
	 *  @var int
	 */
	private int $status;
	
	/**
	 *  Inherited or overriden base permissions
	 *  @var array
	 */
	private array $_permissions;
	
	/**
	 *  Settings changed since first loading and needs saving
	 *  @var bool
	 */
	protected bool $s_changed	= false;
	
	/**
	 *  When in paged mode, current index of paged items 
	 *  @var int
	 */
	protected int $_total;
	
	/**
	 *  Main event controller
	 *  @var \PubCabin\Controller
	 */
	protected static $_ctrl;
	
	/**
	 *  Store changes by creating a new item or updating if ID is set
	 *  
	 *  @return bool True if successfully saved
	 */
	abstract public function save() : bool;
	
	public function __construct() {
		if ( isset( $this->_settings_id ) ) {
			$this->_n_settings_id = $this->_settings_id;
		}
	}
	
	/**
	 *  Handle ID and settings when loading from database
	 */
	public function __set( $name, $value ) {
		
		switch ( $name ) {
			
			case 'settings_id':
				// Settings ID change separately
				if ( isset( $this->_settings_id ) ) {
					return;
				} 
				$this->_settings_id = ( int ) $value;
				break;
			
			// Inherited settings
			case 'settings':
				$this->_settings = 
				static::formatSettings( $value );
				
				break;
			
			// Overriden from inherited settings
			case 'settings_override':
				if ( !isset( $this->_settings ) ) {
					$this->_settings = [];
				}
				
				$this->_settings = 
				\array_merge( 
					$this->_settings, 
					static::formatSettings( $value )
				);
				break;
			
			case 'sort_order':
				$this->_sort_order = ( int ) $value;
				break;
				
			case 'permissions':
				$this->_permissions = 
				\is_array( $value ) ? 
					$value : 
					\PubCabin\Util::decode( ( string ) $value );
				break;
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'settings_id':
				return $this->_settings_id ?? 0;
				
			case 'settings':
			case 'settings_override':
				return $this->_settings ?? [];
				
			case 'sort_order':
				return $this->_sort_order ?? 0;
			
			case 'permissions':
				return $this->_permissions ?? [];
		}
		
		return null;
	}
	
	/**
	 *  Database path string helper
	 *  
	 *  @param string		$db	Database file name
	 *  @return string
	 */
	protected static function dsn( string $db ) : string {
		return \PubCabin\Util::slashPath( \PUBCABIN_DATA ) . $db;
	}
	
	/**
	 *  Set the main event controller for all shared entities
	 *  
	 *  @param \PubCabin\Controller	$ctrl	Main event controller
	 */
	public static function setController( \PubCabin\Controller $ctrl ) {
		static::$_ctrl = $ctrl;
	}
	
	/**
	 *  Get the currently set controller for all shared entities
	 *   
	 *  @return mixed
	 */
	protected static function getController() {
		return static::$_ctrl ?? null;
	}
	
	/**
	 *  Get the current request from the 'begin' event output
	 *   
	 *  @return mixed
	 */
	protected static function getRequest() {
		if ( !isset( static::$_ctrl ) ) {
			return null;
		}
		return static::$_ctrl->output( 'begin' )['request'];
	}
	
	/**
	 *  Get the current data handler from the 'begin' event output
	 *   
	 *  @return mixed
	 */
	protected static function getData() {
		if ( !isset( static::$_ctrl ) ) {
			return null;
		}
		return static::$_ctrl->output( 'begin' )['data'];
	}
	
	/**
	 *  Get the configuration from the main event controller
	 *   
	 *  @return mixed
	 */
	protected static function getConfig() {
		if ( !isset( static::$_ctrl ) ) {
			return null;
		}
		return static::$_ctrl->getConfig;
	}
	
	/**
	 *  Property helper to set any modified entity setttings
	 *   
	 *  @return mixed
	 */
	public function setSettings( array $new_settings ) {
		$this->_settings = 
			\array_merge( $this->_settings, $new_settings );
		$this->s_changed = true;
	}
	
	/**
	 *  Change the current entity ID property
	 *   
	 *  @return mixed
	 */
	public function swapSettingID( int $_new ) {
		$this->_n_settings_id	= $_new;
	}
	
	/**
	 *  Get the current request from the 'begin' event output
	 *   
	 *  @return mixed
	 */
	public static function populateFromArray( array $cols ) {
		$obj	= new self;
		$props	= \get_object_vars( $obj );
		
		foreach( $props as $k => $v ) {
			if ( !\array_key_exists( $k, $cols ) ) {
				continue;
			}
			$obj->{$k} = $cols[$k];
		}
		return $obj;
	}
	
	/**
	 *  Helper to detect and parse a 'settings' data type
	 *  
	 *  @param string	$name		Setting name
	 *  @return array
	 */
	public static function formatSettings( $value ) : array {
		// Nothing to format?
		if ( \is_array( $value ) ) {
			return $value;
		}
		
		// Can be decoded?
		if ( 
			!\is_string( $value )	|| 
			\is_numeric( $value )
		) {
			return [];
		}
		$t	= \trim( $value );
		if ( empty( $t ) ) {
			return [];
		}
		if ( 
			\str_starts_with( $t, '{' ) && 
			\str_ends_with( $t, '}' )
		) {
			return \PubCabin\Util::decode( ( string ) $t );
		}
		
		return [];
	}
}


