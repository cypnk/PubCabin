<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Atom/Base.php
 *  @brief	XML component builder base for AtomPub and Atom feeds
 */
namespace PubCabin\Atom;

abstract class Base {
	
	/**
	 *  XML Namespaces
	 *  @var array
	 */
	protected $xmlns	= [
		'atom'	=> 'http://www.w3.org/2005/Atom', 
		'app'	=>'http://www.w3.org/2007/app'
	];
		
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
	
	/**
	 *  Error storage
	 *  @var array
	 */
	protected $err	= [];
	
	/**
	 *  Internal error state for libxml
	 *  @var bool
	 */
	protected static $e_state;
	
	public function __construct( bool $xml = false ) {
		$this->dom	= 
		$xml ? 
			new \DOMDocument( '1.0', 'utf-8' ) : 
			new \DOMDocument();
	}
	
	public function __destruct() {
		if ( empty( $this->err ) ) {
			return;
		}
		
		foreach ( $this->err as $e ) {
			errors( $e );
		}
	}
	
	/**
	 *  String content loader to current base
	 *  
	 *  @param string	$content	Inner content
	 */
	public function load( string $content ) {
		$this->captureXMLError( true );
		
		$lstate		= 
		$this->dom->loadHTML( 
			$content, 
			\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
			\LIBXML_NOERROR | \LIBXML_NOWARNING | 
			\LIBXML_NOXMLDECL | \LIBXML_COMPACT | 
			\LIBXML_NOCDATA | \LIBXML_NONET
		);
		if ( !$lstate ) {
			$this->captureXMLError( 
				false, 'Error loading DOMDocument.' 
			);
			return;
		}
		
		$this->resetEState();
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
		
		$this->captureXMLError( true );
		$out = $xml ? 
			$this->dom->saveXML() : 
			$this->dom->saveHTML();
		
		if ( false !== $out ) {
			$this->resetEState();
			return ( string ) $out;
		}
		
		$msg	= 
		'Error saving Document output as ' . 
			( $xml ? 'XML.' : 'HTML.' );
		
		$this->captureXMLError( false, $msg );
		return '';
	}
	
	/** 
	 *  Prepare and process error storage
	 *  
	 *  @param bool		$err	Begin internal errors if true
	 *  @param string	$msg	Optional prepended message
	 */
	protected function captureXMLError( bool $begin, string $msg = '' ) {
		if ( $begin ) {
			if ( !isset( static::$e_state ) ) {
				static::$e_state	= 
				\libxml_use_internal_errors( true );
			} else {
				\libxml_use_internal_errors( true );
			}
			return;
		}
		
		if ( !empty( $msg ) ) {
			$this->err[] = $msg;
		}
		
		$xerr = \libxml_get_errors();
		foreach ( $xerr as $e ) {
			$this->err[] = $this->formatXMLError( $e );
		}
		
		\libxml_clear_errors();
		$this->resetEState();
	}
	
	/**
	 *  Reset libxml error state
	 */
	protected function resetEState() {
		if ( isset( static::$e_state ) ) {
			\libxml_use_internal_errors( static::$e_state );
		}
	}
	
	/**
	 *  Detailed XML error message or plain string 
	 *  
	 *  @param mixed	$e	Error string or LibXMLError
	 *  @return string
	 */
	protected function formatXMLError( $e ) : string {
		// Default error message
		static $d	= 'XML Error';
		
		if ( false === $e ) {
			return $d;
		} elseif ( \is_string( $e ) ) {
			return $e;
		}
		
		$msg = $e->message ?? $d;
		if ( 0 === \strcmp( $d, $msg ) ) {
			// Nothing further to extract?
			return $d;
		}
		
		$out	= '';
		switch ( $e->level ) {
			case \LIBXML_ERR_ERROR:
				$out .= 'Error: ';
				break;
			
			case \LIBXML_ERR_FATAL:
				$out .= 'Fatal error: ';
				break;
				
			case \LIBXML_ERR_WARNING:
				$out .= 'Warning: ';
				break;
				
			default:
				$out .= 'Error: ';
		}
		
		return 
		$out . $e->code . 
			' in line ' . $e->line . 
			' column ' . $e->column . 
			' - ' . \trim( $msg );
	}
}


