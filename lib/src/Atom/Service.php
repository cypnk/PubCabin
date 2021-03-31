<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/Service.php
 *  @brief	Available application feature discovery document
 */
namespace PubCabin\Atom;

class Service extends Base {
	
	/**
	 *  Generated service document
	 *  @var \DOMDocument
	 */
	protected $service;
	
	public function __construct() {
		parent::__construct( true );
		
		$this->xmlns	= 'http://www.w3.org/2007/app';
		$this->service	= $this->createNode( 'service' );
	}
	
	public function addWorkspace( Workspace $work ) {
		$spaces	= 
		$work->dom->getElementsByTagName( 'workspace' );
		
		foreach( $spaces as $w ) {
			$this->servce->appendChild( $w );
		}
	}
}


