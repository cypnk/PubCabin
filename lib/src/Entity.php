<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Entity.php
 *  @brief	Common data object
 */
namespace PubCabin;

abstract class Entity {
	
	/**
	 *  Database index primary key (set once)
	 *  @var int
	 */
	private $_id;
	
	/**
	 *  Unique identifier (set once)
	 *  @var string
	 */
	private $_uuid;
	
	/**
	 *  Configuration settings
	 *  @var array 
	 */
	private $_settings;
	
	/**
	 *  Settings identifier
	 *  @var int
	 */
	private $_settings_id;
	
	/**
	 *  New settings ID if swapping settings
	 *  @var int
	 */
	private $_n_settings_id;
	
	/**
	 *  Created UTC datetime stamp
	 *  @var string
	 */
	public $created;
	
	/**
	 *  Last modified UTC datetime stamp
	 *  @var string
	 */
	public $updated;
	
	/**
	 *  Sorting order
	 *  @var int
	 */
	private $_sort_order;
	
	/**
	 *  Special handling status, based on object type
	 *  @var int
	 */
	private $status;
	
	/**
	 *  Inherited or overriden base permissions
	 *  @var array
	 */
	private $_permissions;
	
	/**
	 *  Settings changed since first loading and needs saving
	 *  @var bool
	 */
	protected $s_changed	= false;
	
	
	/**
	 *  When in paged mode, current index of paged items 
	 *  @var int
	 */
	protected $_total;
	
	/**
	 *  Store changes by creating a new item or updating if ID is set
	 *  
	 *  @param \PubCabin\Data	$data	Storage class
	 *  @return bool			True if successfully saved
	 */
	abstract public function save( \PubCabin\Data $data ) : bool;
	
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
			case 'id':
				// ID must not be overwritten
				if ( isset( $this->_id ) ) {
					return;
				} 
				$this->_id = ( int ) $value;
				break;
			
			case 'uuid':
				// UUID must not be overwritten
				if ( isset( $this->_uuid ) ) {
					return;
				}
				$this->_uuid = ( string ) $value;
				break;
			
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
				\is_array( $value ) ? 
					$value : 
					Util::decode( ( string ) $value );
				break;
			
			// Overriden from inherited settings
			case 'settings_override':
				if ( !isset( $this->_settings ) ) {
					$this->_settings = [];
				}
				
				$this->_settings = 
				\array_merge( 
					$this->_settings, 
					\is_array( $value ) ? 
						$value : 
						Util::decode( ( string ) $value )
				);
				break;
			
			case 'sort_order':
				$this->_sort_order = ( int ) $value;
				break;
				
			case 'permissions':
				$this->_permissions = 
				\is_array( $value ) ? 
					$value : 
					Util::decode( ( string ) $value );
				break;
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'id':
				return $this->_id ?? 0;
				
			case 'uuid':
				return $this->_uuid ?? '';
				
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
	
	
	public function setSettings( array $new_settings ) {
		$this->_settings = 
			\array_merge( $this->_settings, $new_settings );
		$this->s_changed = true;
	}
	
	public function swapSettingID( int $_new ) {
		$this->_n_settings_id	= $_new;
	}
}


