<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Memebership/Module.php
 *  @brief	User authentication and profile management
 */
namespace PubCabin\Modules\Membership;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Sites', 'Menues', 'Forms' ];
	}	
}


