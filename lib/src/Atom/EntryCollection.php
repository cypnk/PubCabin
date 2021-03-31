<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/EntryCollection.php
 *  @brief	Application categories and entries
 */
namespace PubCabin\Atom;

class EntryCollection extends Base {
	
	/**
	 *  Current pagination index
	 *  @var int
	 */
	public $index;
	
	/**
	 *  Content types
	 *  @var array
	 */
	public $accept		= [];
	
	/**
	 *  Base entry and category holder
	 *  @var \DOMDocument
	 */
	public $col;
	
	/**
	 *  Create new collection with title
	 *  
	 *  @param string	$title	Collection title (unique)
	 */
	public function __construct( string $title ) {
		parent::__construct( false );
		
		$this->col	= $this->createNode( 'collection', '' );
		
		// Add collection title
		$this->title	= $title;
		
		$telement	= 
		$this->dom->createElement( 'atom:title', $title, '' );
		$this->col->appendChild( $telement );
	}
	
	/**
	 *  Add submission handler accept MIME types
	 *   
	 *  @param array	$accept		Content types
	 */
	public function addAccept( array $accept ) {
		if ( \in_array( $accept, $this->accept ) ) {
			return;
		}
		
		$this->accept[] = $accept;
		foreach ( $accept as $a ) {
			$ael	= 
			$this->dom->createElement( 'accept', $a );
			$this->col->appendChild( $ael );
		}
	}
	
	/**
	 *  Add standalone category resource path
	 *  
	 *  @param string	$url		Categories list path
	 */
	public function addCategoryUrl( string $href ) {
		$cat	= $this->dom->createElement( 'category' );
		$caa	= $this->dom->createAttribute( 'href' );
		
		$caa->value = 
		\htmlspecialchars( 
			$href, 
			\ENT_NOQUOTES | \ENT_COMPAT, 
			'UTF-8', 
			false 
		);
		
		$cat->appendChild( $caa );
		$this->col->appendChild( $cat );
	}
	
	/**
	 *  Add colleciton category
	 *  
	 *  @param \PubCabin\Atom\Category	$cat Atom category
	 */
	public function addCategory( Category $cat ) {
		$categories	= 
		$col->dom->getElementsByTagName( 'category' );
		
		foreach ( $categories as $c ) {
			$this->col->appendChild( $c );
		}
	}
}


