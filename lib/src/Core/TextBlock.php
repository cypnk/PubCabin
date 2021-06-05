<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/TextBlock.php
 *  @brief	Segmented page content
 */

namespace PubCabin\Core;

class TextBlock extends \PubCabin\Entity {
	
	/**
	 *  Parent page text anchor
	 *  @var int
	 */
	public $text_id;
	
	/**
	 *  Authorship or editorial relationships
	 *  @var array
	 */
	private $_users	= [];
	
	/**
	 *  Raw content as entered by the user
	 *  @var string
	 */
	public $body;
	
	/**
	 *  Filtered content stripped of any HTML or formatting
	 *  @var string
	 */
	public $bare;
	
	// TODO
	public function save( \PubCabin\Data $data ) : bool {
		if ( isset( $this->id ) ) {
			
		} else {
			
		}
		
		return true;
	}
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'users':
				// TODO: Parse editorial relationships
				return;
		}
		
		parent::__set( $name, $value );
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'users':
				return $_users;
		}
		
		return parent::__get( $name );
	}
}


