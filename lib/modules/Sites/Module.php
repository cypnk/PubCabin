<?php declare( strict_types = 1 );
/**
 *  @file	/libs/modules/Sites/Module.php
 *  @brief	Main website handler
 */
namespace PubCabin\Modules\Sites;

class Module extends \PubCabin\Modules\Module {
	
	protected $language;
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Files' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks	= $this->getModule( 'Hooks' );
		
		// Register this module's request handler
		$hooks->event( [ 'request', [ $this, 'begin' ] );
		
		// Trigger begin
		$hooks->event( [ 'request', '' ] );
	}
	
	
	/**
	 *  Application start
	 *  
	 *  @param string	$event	Request event name
	 *  @param array	$hook	Previous hook event data
	 *  @param array	$params	Passed event data
	 */
	public function begin( 
		string		$event, 
		array		$hook, 
		array		$params 
	) {
		$req	= $this->getRequest();
		$db	= $this->getData();
		
		// TODO Load websites and configuration
	}
	
	/**
	 *  Configure language for this site based on visitor preference
	 *  
	 *  @return array
	 */
	public function getLanguage() : array {
		if ( isset( $this->language ) ) {
			return $this->language;
		}
		
		// Visitor language and locale
		$req	= $this->getRequest();
		
		// Set placeholder replacements
		// TODO Get these from config
		\PubCabin\Core\Language::setPlaceholders( [
			'{name_min}'	=> 2,
			'{name_max}'	=> 80,
			'{pass_min}'	=> 8,
			'{display_min}'	=> 2,
			'{display_max}'	=> 100,
			
			'{formatting}'	=> '/formatting',
			'{terms}'	=> '/terms'
		] );
		
		
		$this->language = 
			\PubCabin\Core\Language( 
				$this->getData(), $req->getLang()
			);
		
		return $this->language;
	}
}

