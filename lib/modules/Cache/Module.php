<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Cache/Module.php
 *  @brief	Handle storage and retrieval of rendered content
 */
namespace PubCabin\Modules\Cache;

class Module extends \PubCabin\Handler {
	
	/**
	 *  Caching events
	 */
	protected static $events = [
		'getcache',
		'storecache',
		'shutdown'
	];
	
	/**
	 *  Handle notifications
	 *  
	 *  @param \PubCabin\Event		$event	Notification handler
	 *  @param \PubCabin\Params	$params	Optional staring properties
	 */
	public function update( \SplSubject $event, ?array $params = null ) {
		switch ( $event->name() ) {
			case 'getcache':
				break;
				
			case 'storecache':
				break;
				
			case 'shutdown':
				$this->shutdown();
				break;
		}
	}
	
	/**
	 * TODO: Handle saving to cache on shutdown
	 */
	protected function shutdown() {
		
	}
}
