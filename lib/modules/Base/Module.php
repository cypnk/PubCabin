<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Base.php
 *  @brief	First module to run and set the PubCabin environment
 */
namespace PubCabin\Modules\Base;

class Module extends \PubCabin\Handler {
	
	/**
	 *  Base trigger events
	 *  *creating parameters sent from other modules
	 *  @var array
	 */
	protected static $events = [
		'sessionstarted',
		'sitecreating',
		'sitecreated',
		'sitefound',
		'sitenotfound',
		'areacreating',
		'areacreated',
		'areafound',
		'areanotfound',
		'pagecreating',
		'pagecreated',
		'pagefound',
		'pagenotfound',
		'commentcreating',
		'commentcreated',
		'commentfound',
		'commentnotfound',
		'shutdown'
	];
	
	/**
	 *  Handle begin notification
	 *  
	 *  @param \PubCabin\Event		$event	Notification handler
	 *  @param \PubCabin\Params	$params	Optional staring properties
	 */
	public function update( \SplSubject $event, ?array $params = null ) {
		switch ( $event->name() ) {
			case 'begin':
				$this->begin( $params );
				break;
			
			case 'modulesloaded':
				// TODO: Find site
				break;
				
			case 'sessionstarted':
				// TODO: Init stuff
				break;
				
			case 'shutdown':
				$this->shutdown();
				break;
		}
	}
	
	/**
	 *  Begin event
	 */
	protected function begin( ?array $params = null ) {
		$ctrl		= &$this->controller;
		
		// Register site start after session
		$ctrl->register( static::$events, 'Base' );
		
		// Get default config, request, and data
		$config		= $ctrl->getConfig();
		$data		= new \PubCabin\Data( $ctrl );
		
		// Set main data install dir
		$data->installDir( 
			\PubCabin\Entity::MAIN_DATA,
			static::resourcePath( 
				$this, '', 'install'
			)
		);
				
		// Shared components with current controller
		$this->data['begin']	= 
		[
			'config'	=> $config, 
			'data'		=> $data,
			'request'	=> new \PubCabin\Request( $ctrl ),
			'render'	=> new \PubCabin\Render( $config )
		];
		
		// Set entity controller dependency
		\PubCabin\Entity::setController( $ctrl );
	}
	
	/**
	 *  Final tasks
	 */
	protected function shutdown() {
		// TODO: Cleanup
	}
}
