<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/Entry.php
 *  @brief	Main item entry data and content type
 */
namespace PubCabin\Atom;

class Entry extends Base {
	
	/**
	 *  Current entry document holding attributes
	 *  @var \DOMDocument
	 */
	public $entry;
	
	/**
	 *  List of creators
	 *  @var array
	 */
	public $author	= [];
	
	/**
	 *  Main HTML body or resource
	 *  @var \PubCabin\Atom\Content
	 */
	public $content;
	
	/**
	 *  Synopsis or description tag
	 *  @var string
	 */
	public $summary;
	
	/**
	 *  @var array
	 */
	public $links	= [];
	
	/**
	 *  Content render type
	 *  @var string
	 */
	public $entry_type;
	
	public function __construct() {
		parent::__construct( false );
		$this->entry	= $this->createNode( 'entry', '' );
	}
	
	/**
	 *  Add summary data
	 *  
	 *  @param string	$data	Summaries are short text content
	 */
	public function addSummary( string $data ) {
		$this->summary = 
		$this->dom->createElement( 'summary', $data );
		$this->summary->setAttribute( 'type', 'text' );
		
		$this->entry->appendChild( $this->summary );
	}
	
	/**
	 *  Add main entry content
	 *  
	 *  @param string	$data	Main content data or empty string
	 *  @param string	$type	Content formatting type
	 *  @param string	$src	Optional external URL
	 */
	public function addContent( 
		string	$data, 
		string	$type	= 'text/plain',
		?string	$src	= null 
	) {
		// Create content with source or internal data
		if ( empty( $src ) ) {
			$this->content	= 
			$this->dom->createElement( 'content', $data );
		} else {
			$this->content	= 
			$this->dom->createElement( 'content', '' );
			
			$this->content->setAttribute( 'src', $src );
		}
		
		$this->content->setAttribute( 'type', $type );
		$this->entry->appendChild( $this->content );
	}
	
	/**
	 *  TODO: Add authors, links, updated
	 */
}


