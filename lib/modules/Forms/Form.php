<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Form.php
 *  @brief	User input submission form
 */

namespace PubCabin\Modules\Forms;

class Form extends \PubCabin\Entity {
	
	/**
	 *  Form name
	 *  @var string
	 */
	public $title;
	
	/** 
	 *  Submission path id
	 *  @var int
	 */
	public $path_id;
	
	/** 
	 *  Form fields list
	 *  @var array
	 */
	public $fields		= [];
	
	/** 
	 *  Uploaded files list
	 *  @var array
	 */
	public $files		= [];
	
	/**
	 *  Capture event list
	 *  @var array
	 */
	public $events		= [];
	
	/**
	 *  Anti-XSS request forgery token
	 *  @var string
	 */
	public $token;
	
	/**
	 *  Generated one-time string per submission
	 *  @var string
	 */
	public $nonce;
	
	/**
	 *  Metadata signature to prevent tampering presets
	 *  @var string
	 */
	public $meta_key;
	
	/**
	 *  Form submission data encoding
	 *  @var string
	 */
	protected $_enctype;
	
	/**
	 *  Submission method
	 *  @var string
	 */
	protected $_form_method;
	
	/**
	 *  Attribute whitelists
	 */
	private static $_methods = [ 'get', 'post' ];
	private static $_enctypes = [
		'multipart/form-data',
		'application/x-www-form-urlencoded',
		'text/plain'
	];
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'form_method':
				$value = \PubCabin\Util::lowercase( $value );
				if ( !\in_array( $value, static::$_methods ) ) {
					return;
				}
				$this->_form_method = $value;
				break;
			
			case 'enctype':
				$value = \PubCabin\Util::lowercase( $value );
				if ( !\in_array( $value, static::$_enctypes ) ) {
					return;
				}
				$this->_enctype = $value;
				break;
				
			default:
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'form_method':
				return $this->_form_method ?? 'post';
			
			case 'enctype':
				return $this->_enctype ?? 
					'multipart/form-data';
			
			default:
				return parent::__get( $name );
		}
	}
	
	// TODO
	public function save() : bool {
		return false;
	}
}


