<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Language.php
 *  @brief	Site content translation unit
 */
namespace PubCabin\Core;

class Language extends \PubCabin\Entity {
	
	/**
	 *  Contextual language label E.G. English, 日本語 etc...
	 *  @var string
	 */
	public $label;
	
	/**
	 *  2-Letter language code E.G. en, jp etc...
	 *  @var string
	 */
	public $iso_code;
	
	/**
	 *  3-Letter language group, E.G. eng, jpn etc...
	 *  @var string
	 */
	public $lang_group;
	
	/**
	 *  Language-specific locale. E.G. for English US, UK etc...
	 *  @var string
	 */
	public $locale;
	
	/** 
	 *  This is the global default language if true
	 *  @var bool
	 */
	protected $_is_default;
	
	/**
	 *  Array of language placeholders
	 *  @var array
	 */
	protected $_def;
	
	/**
	 *  Locale translations
	 *  @var array
	 */
	public	$translations	= [];
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'definitions':
				$this->_def = 
				\is_array( $value ) ? 
					$value : 
					\PubCabin\Util::decode( ( string ) $value );
				break;
			
			case 'is_default':
				if ( !\is_array( $value ) ) {
					$this->_is_default = 
						( bool ) $value;
				}
				
				break;
				
			default:
				parent::__set( $name, $value );
		}
		
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'is_default':
				return $this->_is_default;
			
			case 'definitions':
				return 
				isset( $this->_def ) ? $this->_def : [];
			
			default: 
				return parent::__get( $name );
		}
	}
}
