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
		
		// Current visitor language
		$lang = $this->getLanguage();
		
		// Override preconfigured language definitions
		$this->getConfig()->overrideDefaults( 
			[ 'translations' => $lang->translations ]
		);
		
		// TODO Override site style templates from database
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
	
	/**
	 *  Override the default templates with site specific styles
	 *  
	 *  @param int		$id	Current site ide
	 *  @param string	$area	Rendered area
	 */
	public function setRenderTemplates( int $id, string $area ) {
		$tpl	= [];
		$db	= $this->getData()->getDb( static::MAIN_DATA );
		$stm	= 
		$db->prepare(
			"SELECT * FROM area_view WHERE 
				site_id = :id AND area = :area"
		);
		
		$rows	= 
		$db->getDataResult( $db, [ 
			':id'	=> $id, 
			':area'	=> $area
		], $stm );
		
		foreach ( $rows as $r ) {
			$tpl = 
			\array_combine( 
				explode( '|', $r['templates'] ), 
				explode( '|', $r['template_render'] )
			);
			break;
		}
		
		$this->getRender()->template( '', $tpl );
	}
	
}

