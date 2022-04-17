<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Filter/Module.php
 *  @brief	Content and URL filtering
 */
namespace River\Modules\Filter;

class Module extends \River\Handler {
	
	/**
	 *  Handle notifications
	 *  
	 *  @param \River\Event		$event	Notification handler
	 *  @param \River\Params	$params	Optional staring properties
	 */
	public function update( \SplSubject $event, ?array $params = null ) {
		switch ( $event->name() ) {
			case 'begin':
				$data	= $ctrl->output( 'begin' )['data'];
				
				// Set install dir
				$data->installDir( 
					\River\Entity::FILTER_DATA,
					static::resourcePath( 
						$this, '', 'install'
					)
				);
				
				break;
		}
	}
}

