<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Site.php
 *  @brief	Website base data object
 */

namespace PubCabin\Core;

class Site extends \PubCabin\Entity {
	
	/**
	 *  Site description
	 *  @var string
	 */ 
	public $label;
	
	/**
	 *  Domain or IP address
	 *  @var string
	 */
	public $basename;
	
	/**
	 *  URL or sub section path
	 *  @var string
	 */
	public $basepath;
	
	/**
	 *  Site is currently active if true
	 *  @var bool
	 */
	public $is_active;
	
	/**
	 *  Site under maintenance if true
	 *  @var bool
	 */
	public $is_maintenance;
	
	// TODO
	public function save( \PubCabin\Data $data ) : bool {
		if ( isset( $this->id ) ) {
			
		} else {
			
		}
		
		return true;
	}
}

