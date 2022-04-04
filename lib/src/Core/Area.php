<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Area.php
 *  @brief	Site content render region
 */

namespace PubCabin\Core;

class Area extends \PubCabin\Entity {
	
	/**
	 *  Anchor website
	 *  @var int
	 */
	public $site_id;
	
	/**
	 *  Region description
	 *  @var string
	 */
	public $label;
	
	/**
	 *  Base render style
	 *  @var int
	 */
	public $style_id;
	
	/**
	 *  Base style render override (this component, not pages)
	 *  @var array
	 */
	protected $_templates;
	
	/**
	 *  Region HTML render templates
	 *  @var array
	 */
	protected $_template_render;
	
	/**
	 *  Custom render settings
	 *  @var array
	 */
	protected $_render_settings;
	
	/**
	 *  Data SQL strings
	 *  @var array
	 */
	protected static $sql	= [
		"insert"	=>
		"INSERT INTO areas 
			( label, permisisons, settings, site_id )
			VALUES ( :label, :perms, :settings, :site );",
		
		"update"	=> 
		"UPDATE areas SET label = :label, permissions = :perms, 
			settings = :settings WHERE id = :id;"
	];
	
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'templates':
				$this->_templates = 
				\is_array( $value ) ? 
					$value : 
					explode( '|', ( string ) $value );
				break;
				
			case 'template_render':
				$this->_template_render = 
				\is_array( $value ) ? 
					$value : 
					explode( '|', ( string ) $value );
				break;
			
			// Base render settings
			case 'render_settings':
				$this->_render_settings = 
				\is_array( $value ) ? 
					$value : 
					\PubCabin\Util::decode( ( string ) $value );
				break;
			
			// Overridenr render settings E.G. permissions-based
			case 'render_settings_override':
				if ( !isset( $this->_settings ) ) {
					$this->_render_settings = [];
				}
				
				$this->_render_settings = 
				\array_merge( 
					$this->_render_settings, 
					\is_array( $value ) ? 
						$value : 
						\PubCabin\Util::decode( ( string ) $value )
				);
				break;
				
			default: 
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'templates':
				return $this->_templates ?? [];
				
			case 'template_render':
				return $this->_template_render ?? [];
				
			case 'render_settings':
				return $this->render_settings ?? [];
				
			default: 
				return parent::__get( $name );
		}
	}
	
	/**
	 *  Save changes or create new Area 
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @return bool			True on success
	 */
	public function save( \PubCabin\Data $data ) : bool {
		$params	= [
			':label'	=> $this->label,
			':perms'	=> \PubCabin\Util::encode( $this->permissions ),
			':settings'	=> \PubCabin\Util::encode( $this->settings ),
		];
		
		if ( empty( $this->id ) ) {
			$params[':site'] = $this->site_id;
			
			$this->id = 
			$data->setInsert( 
				static::$sql['insert'], 
				$params, 
				static::MAIN_DATA 
			);
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] = $this->id;
		
		return 
		$data->setUpdate( 
			static::$sql['update'], 
			$params, 
			static::MAIN_DATA 
		);
	}
}


