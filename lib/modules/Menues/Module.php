<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Menues/Module.php
 *  @brief	Site, area, and privilege based navigation
 */
namespace PubCabin\Modules\Menues;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Styles', 'Sites' ];
	}
}

