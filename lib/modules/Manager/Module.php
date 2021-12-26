<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Manager/Module.php
 *  @brief	Backend major sections and user handling
 */
namespace PubCabin\Modules\Manager;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Styles', 'Forms', 'Menues', 'Sites', 'Membership' ];
	}
}
