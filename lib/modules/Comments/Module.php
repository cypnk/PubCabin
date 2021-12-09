<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Comments/Module.php
 *  @brief	Visitor feedback
 */
namespace PubCabin\Modules\Comments;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 
			'Styles', 
			'Forms', 
			'Site', 
			'Manager', 
			'Membership'
		];
	}
}
