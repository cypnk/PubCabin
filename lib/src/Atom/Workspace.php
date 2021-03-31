<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/Workspace.php
 *  @brief	Application workspace for collections and categories
 */
namespace PubCabin\Atom;

class Workspace extends Base {
	
	/**
	 *  Current workspace holding collections and other attributes
	 *  @var \DOMDocument
	 */
	public $work;
	
	/**
	 *  Create new workspace with title
	 *  
	 *  @param string	$title	Region title (ideally unique)
	 */
	public function __construct( string $title ) {
		parent::__construct( false );
		
		$this->work	= $this->createNode( 'workspace', '' );
		
		// Add workspace title
		$this->title	= $title;
		
		$telement	= 
		$this->dom->createElement( 'atom:title', $title, '' );
		$this->work->appendChild( $telement );
	}
	
	public function addCollection( EntryCollection $col ) {
		$collection	= 
		$col->dom->getElementsByTagName( 'collection' );
		
		foreach ( $collection as $c ) {
			$this->work->appendChild( $c );
		}
	}
}


