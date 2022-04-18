<?php declare( strict_types = 1 );
/**
 *  @file	/lib/Modules/Base/PageType.php
 *  @brief	Page behavior and rendering parameters
 */

namespace PubCabin\Modules\Base;

class PageType extends \PubCabin\Entity {
	
	/**
	 *  Overridable page type behavior
	 *  @var array
	 */
	protected $default_behavior = [
		'type_handler'	=> '\\PubCabin\\Modules\\Base\\PageType',
		'parents'	=> [ '*', 'page' ],
		'children'	=> [ 'page' ],
		'label'		=> '{lang:ptypes:page}',
		'description'	=> '{lang:ptypes:pagedesc}',
		'allow_children'=> 1,
		'allow_comments'=> 1,
		'privileges'	=> [
			'create_roles'	=> [],
			'edit_roles'	=> [],
			'delete_roles'	=> [],
			'upload_roles'	=> [],
			'mod_roles'	=> [],
			'comment_roles'	=> []
		],
		'settings'	=> []
	];
	
	/**
	 *  Page specific type E.G. blogpost, forum, shop etc...
	 *  @var string
	 */ 
	public $label;
	
	/**
	 *  HTML template override
	 *  @var string
	 */
	public $render;
	
	/**
	 *  Behavior JSON separate from settings
	 *  @var array
	 */
	protected $_behavior;
	
	public function __set( $name, $value ) {
		// Intercept behavior
		switch( $name ) {
			case 'behavior':
				if ( !isset( $this->_behavior ) ) {
					$this->_behavior = [];
				}
				
				$this->_behavior = 
				static::formatSettings( $value );
				return;
		}
		
		// Fallthrough to rest
		parent::__set( $name, $value );
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'behavior':
				return 
				$this->_behavior ?? 
				static::$default_behavior;
		}
		
		return parent::__get( $name );
	}
	
	/**
	 *  Create or update page type entity
	 *  
	 *  @return bool	True on success
	 */
	public function save() : bool {
		$params	= [
			':label'	=> $this->label,
			':render'	=> $this->render,
			':behavior'	=> 
			\PubCabin\Util::encode( $this->_behavior ),
		];
		
		$data	= static::getData();
		if ( empty( $this->id ) ) {
			$sql = 
			"INSERT INTO page_types ( 
				label, render, behavior
			) VALUES ( :label, :render, :behavior );";
			
			$this->id = 
			$data->setInsert( $sql, $params, static::dsn( static::MAIN_DATA ) );
			
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] => $this->id;
		$sql = 
		"UPDATE page_types SET label = :label, render = :render, 
			behavior = :behavior 
			WHERE id = :id LIMIT 1;";
		
		return 
		$data->setUpdate( $sql, $params, static::dsn( static::MAIN_DATA ) );
	}
}

