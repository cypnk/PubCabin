<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/Base.php
 *  @brief	XML component builder base for AtomPub and Atom feeds
 */
namespace PubCabin\Atom;

abstract class Base {
	
	/**
	 *  XML Namespace
	 *  @var string
	 */
	public $xmlns	= 'http://www.w3.org/2005/Atom';
		
	/**
	 *  Unique URN identifier
	 *  @var string
	 */
	public $id;
	
	/**
	 *  Main label for this area as atom:title
	 *  @var string
	 */
	public $title;
	
	/**
	 *  UTF timestamp
	 *  @var string
	 */
	public $updated;
	
	/**
	 *  Base component
	 *  @var \DOMDocument
	 */
	public $dom;
	
	
	public function __construct( bool $xml = false ) {
		$this->dom	= 
		$xml ? 
			new \DOMDocument( '1.0', 'utf-8' ) : 
			new \DOMDocument();
	}
	
	/**
	 *  String content loader to current base
	 *  
	 *  @param string	$content	Inner content
	 */
	public function load( string $content ) {
		$err		= \libxml_use_internal_errors( true );
		$lstate		= 
		$this->dom->loadHTML( 
			$content, 
			\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
			\LIBXML_NOERROR | \LIBXML_NOWARNING | 
			\LIBXML_NOXMLDECL | \LIBXML_COMPACT | 
			\LIBXML_NOCDATA | \LIBXML_NONET
		);
		if ( !$lstate ) {
			// Log last error if possible and return
			$e = \libxml_get_last_error();
			if ( false !== $e ) {
				errors( 
					$e->message ?? 
					'Error loading DOMDocument' 
				);
			}
		}
		
		\libxml_clear_errors();
		\libxml_use_internal_errors( $err );
	}
	
	/**
	 *  Create node element within current document
	 *  
	 *  @param string	$name	Node element name
	 *  @param string	$ns	Element XML namespace
	 */
	public function createNode( 
		string		$name,
		string		$content,
		?string		$ns		= null 
	) : ?\DOMElement {
		if ( !isset( $this->dom ) ) {
			return null;
		}
		
		if ( \is_null( $ns ) ) {
			$ns = $this->xmlns;
		}
		
		$node = 
		empty( $ns ) ? 
			$this->dom->createElement( $ns, $content );
			$this->dom->createElementNS( $ns, $name, $content );
		
		if ( false === $node ) {
			return null;
		}
		
		$this->dom->appendChild( $node );
		return $node;
	}
	
	/**
	 *  Remove element from current document if it exists
	 *  
	 *  @param \DOMNode	$node	XML Element
	 */
	public function removeNode( \DOMNode $node ) : bool {
		if ( !$this->dom->hasChildNodes() ) {
			return false;
		}
		return 
		( false === $this->dom->removeChild( $node ) ) ? 
			false : true;
	}
	
	/**
	 *  Render document as string
	 *  
	 *  @param bool		$xml	Render as XML if true
	 *  @return string
	 */
	public function save( bool $xml = false ) : string {
		if ( !isset( $this->doc ) ) {
			return '';
		}
		
		return $xml ? 
			$this->dom->saveXML() : 
			$this->dom->saveHTML();
	}
}


