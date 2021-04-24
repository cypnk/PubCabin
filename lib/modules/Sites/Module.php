<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Sites/Module.php
 *  @brief	Main website handler
 */
namespace PubCabin\Modules\Sites;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  Translations for currently set language
	 *  @var array
	 */
	protected $language;
	
	/**
	 *  Current base website
	 */
	protected $site;
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Files' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks	= $this->getModule( 'Hooks' );
		
		// Register this module's request handler
		$hooks->event( [ 'request', [ $this, 'begin' ] ] );
		
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
		$config	= $this->getConfig();
		$req	= $this->getRequest();
		$db	= $this->getData();
		$host	= $req->getHost();
		$uri	= $req->getURI();
		
		$sites	= $this->getSites( $host, $uri );
		
		if ( empty( $sites ) ) {
			$ns	= 'No Host Defined';
			errors( $ns );
			$rsp	= new \PubCabin\Response( $config );
			$rsp->sendError( 400, $ns );
			return;
		}
		
		// Current visitor language
		$lang = $this->getLanguage();
		
		// Override base config settings
		$config->overrideDefaults( [ 
			'translations'	=> $lang->translations,
			'basename'	=> $host
		] );
		
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
		], 'results', $stm );
		
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
	
	/**
	 *  Get list of enabled sites based on basename
	 *  
	 *  @param string	$host	Server or hostname
	 *  @param string	$uri	Full path to search basename
	 *  @return array
	 */
	protected function getSites( string $host, string $uri ) : array {
		$segs	= 
		\PubCabin\Util::trimmedList( $uri, false, '/' );
		
		// Limit maximum basepath search
		$depth = $this->config->setting( 'site_depth', 'int' );
		$depth = \PubCabin\Util::intRange( $depth, 1, 255 );
		
		if ( count( $segs ) > $depth ) {
			$segs = \array_slice( $segs, 0, $depth );
		}
		
		$paths	= [];
		$i	= count( $segs );
		$u	= '';
		
		// Build incremental path from shortest to longest
		for ( $j = 0; $j < $i; $j++ ) {
			$u .= \PubCabin\Util::slashPath( $segs[$j] );
			// Keep preceeding slash
			if ( 0 !== \strcmp( $u, '/' ) ) {
				$u = \rtrim( $u, '/' );
			}
			$paths[] = $u;
		}
		
		// Base search parameters
		$params = [
			':balias'	=> $host, 
			':bname'	=> $host
		];
		$data	= $this->getData();
		
		// Create IN () parameters
		$ins	= $data->getInParam( $paths, $params );
		
		// Sites database
		$db	= $data->getDb( static::MAIN_DATA );
		$stm	= 
		$db->prepare(
			"SELECT * FROM sites_enabled 
				WHERE ( base_alias = :balias OR 
				basename = :bname ) AND basepath {$ins}
				ORDER BY basepath DESC;"
		);
		
		// Return collection of class Site
		$sites	= 
		$db->getDataResult( 
			$db, 
			$params, 
			'class, \\PubCabin\\Core\\Site', 
			$stm 
		);
		
		return empty( $sites ) ? [] : $sites;
	}
	
	/**
	 *  Get currently loaded site data
	 */
	public function getWebsite() {
		return isset( $this->site ) ? $this->site : null;
	}
}

