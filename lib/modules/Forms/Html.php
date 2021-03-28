<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Module/Forms/Html.php
 *  @brief	Whitelist based HTML tag and attribute filter
 */
namespace PubCabin\Modules\Forms;

class Html {
	
	/**
	 *  Form encoding type helper, defaults to application/x-www-form-urlencoded
	 *  
	 *  @param string	$v	Raw encoding type
	 *  @return string
	 */
	public static function cleanFormEnctype( string $v ) : string {
		$v = \trim( $v );
		if (
			0 == \strcasecmp( $v, 'application/x-www-form-urlencoded' )	|| 
			0 == \strcasecmp( $v, 'multipart/form-data' )			|| 
			0 == \strcasecmp( $v, 'text/plain' )
		) {
			return $v;
		}
		
		return 'multipart/form-data';
	}
	
	/**
	 *  Filter form sending method type, defaults to get or post
	 *  
	 *  @param string	$v	Raw form method
	 *  @return string
	 */
	public static function cleanFormMethodType( string $v ) : string {
		$v = \trim( $v );
		if (
			0 == \strcasecmp( $v, 'GET' )	|| 
			0 == \strcasecmp( $v, 'POST' )
		) {
			return $v;
		}
		
		return 'get';
	}
	
	/**
	 *  Clean DOM node attribute against whitelist
	 *  
	 *  @param DOMNode	$node	Object DOM Node
	 *  @param array	$white	Whitelist of allowed tags and params
	 *  @param string	$prefix	URL prefix to prepend text
	 */
	public static function cleanAttributes(
		\DOMNode	&$node,
		array		$white,
		string		$prefix		= ''
	) {
		if ( !$node->hasAttributes() ) {
			return null;
		}
		
		foreach ( 
			\iterator_to_array( $node->attributes ) as $at
		) {
			$n = $at->nodeName;
			$v = $at->nodeValue;
			
			// Default action is to remove attribute
			// It will only get added if it's safe
			$node->removeAttributeNode( $at );
			if ( \in_array( $n, $white[$node->nodeName] ) ) {
				switch ( $n ) {
					case 'longdesc':
					case 'url':
					case 'src':
					case 'data-src':
					case 'data-path':
					case 'data-url':
					case 'href':
					case 'cite':
					case 'action':
						// Use prefix for relative paths
						$v = 
						\PubCabin\Util::prependPath( 
							$v, $prefix 
						);
						break;
					
					// Form-specific extras
					case 'method':
						$v = 
						static::cleanFormMethodType( $v );
						break;
					
					case 'enctype':
						$v = 
						static::cleanFormEnctype( $v );
						break;
					
					case 'pattern':
						$v = 
						\preg_replace( 
							'/[^[:alnum:]_\-\{\}\[\]\/\+\.\s]/', 
							'', $v
						);
						break;
						
					default:
						$v = 
						\PubCabin\Util::entities( 
							$v, false, false 
						);
				}
				
				$node->setAttribute( $n, $v );
			}
		}
	}
	
	/**
	 *  Scrub each node against white list
	 *  @param DOMNode	$node	Document element node to filter
	 *  @param array	$white	Whitelist of allowed tags and params
	 *  @param string	$prefix	URL prefix to prepend text
	 *  @param array	$flush	Elements to remove from document
	 */
	public static function scrub(
		\DOMNode	$node,
		array		$white,
		string		$prefix,
		array		&$flush		= []
	) {
		if ( isset( $white[$node->nodeName] ) ) {
			// Clean attributes first
			static::cleanAttributes( $node, $white, $prefix );
			if ( $node->childNodes ) {
				// Continue to other tags
				foreach ( $node->childNodes as $child ) {
					static::scrub( 
						$child, 
						$white, 
						$prefix, 
						$flush 
					);
				}
			}
			
		} elseif ( $node->nodeType == \XML_ELEMENT_NODE ) {
			// This tag isn't on the whitelist
			$flush[] = $node;
		}
	}
	
	/**
	 * Convert an unformatted text block to paragraphs
	 * 
	 * @link http://stackoverflow.com/a/2959926
	 * @param string	$val		Filter variable
	 * @param bool		$skipCode	Ignore code blocks
	 */
	public static function makeParagraphs( 
		string		$val, 
		bool		$skipCode	= false 
	) {
		$out = $val;
		
		// Escape block level code first
		if ( !$skipCode ) {
			// Format inside code tags
			$out = 
			\preg_replace_callback( 
				'/<code>(.*)<\/code>/ism',
				function ( $m ) {
					if ( empty( $m[1] ) ) {
						return '';
				}
				return 
				\sprintf( '<pre><code>%s</code></pre>', 
					\PubCabin\Util::entities( 
						\trim( $m[1] ), false, false 
					)
				);
			}, $out );	
		}
		
		$filters	= 
		[
			// Turn consecutive line breaks to new paragraph
			'#\s{2,}\n|\n{2}#'		=>
			function( $m ) {
				return '</p><p>';
			},
			
			// Turn consecutive <br>s to paragraph breaks
			'#(?:<br\s*/?>\s*?){2,}#'	=>
			function( $m ) {
				return '</p><p>';
			},
			
			// Remove <br> abnormalities
			'#<p>(\s*<br\s*/?>)+#'		=> 
			function( $m ) {
				return '</p><p>';
			},
			
			'#<br\s*/?>(\s*</p>)+#'		=> 
			function( $m ) {
				return '<p></p>';
			},
			
			// Breaks after tags
			'#</([\w\d]+)>(\s*<br\s*/?>)#'	=> 
			function( $m ) {
				return '</' . $m[1] . '>';
			},
		];
		
		$out		= 
		\preg_replace_callback_array( $filters, $out );
		
		if ( $skipCode ) {
			return $out;
		}
		
		$filters	= [
			// Block of code
			'#^\n`{3,}([\s\S]*)(^(?!\s)`{3,}.*$)\n#smU' =>
			function( $m ) {
				return
				\sprintf(
					'<pre><code>%s</code></pre>',
					\PubCabin\Util::entities( 
						trim( $m[1], '`' ), 
						false, 
						false 
					)
				);
			},
			
			// Remove <br> tags inside <pre> and <code>
			'#<(pre|code)>(.*)<\/\1>#ism'	=>
			function( $m ) {
				return 
				'<' . $m[1] . '>' . 
				\preg_replace( '#<br\s*/?>#', "", $m[2] ) . 
				'</' . $m[1] . '>';
			}
		];
		
		return \preg_replace_callback_array( $filters, $out );
	}
	
	/**
	 *  Tidy settings
	 *  
	 *  @param string	$text	Unformatted, unfiltered raw HTML
	 *  @return string
	 */
	public static function tidyup( string $text ) : string {
		static $newtags;
		static $opt;
		
		if ( \PubCabin\Util::missing( 'tidy_repair_string' ) ) {
			return $text;
		}
		
		if ( !isset( $newtags ) ) {
			$newtags = 
			'figure, figcaption, picture, summary, details';
			// TODO Custom tag hooks
		}
		
		if ( !isset( $opt ) ) {
			$opt = [
				'bare'				=> 1,
				'hide-comments' 		=> 1,
				'drop-proprietary-attributes'	=> 1,
				'fix-uri'			=> 1,
				'join-styles'			=> 1,
				'output-xhtml'			=> 1,
				'merge-spans'			=> 1,
				'show-body-only'		=> 1,
				'new-blocklevel-tags'		=> $newtags,
				'wrap'				=> 0
			];
		}
		
		return \trim( \tidy_repair_string( $text, $opt ) );
	}
	
	/**
	 *  HTML filter
	 *  
	 *  @param string	$value		Unformatted content
	 *  @param string	$prefix		URL path prefix
	 *  @param array	$white		Whitelist of tags, attributes
	 *  @return string
	 */
	public static function html( 
		string	$value, 
		string	$prefix		= '', 
		array	$white		= []
	) : string {
		static $sanity;
		
		if ( !isset( $sanity ) ) {
			if ( \PubCabin\Utils::missing( 'libxml_clear_errors' ) ) {
				$sanity = false;
				errors( 
					'Error: Bare requires the libxml extension be enabled.' 
				);
				return '';
			} else {
				$sanity = true;
			}
		}
		
		if ( !$sanity ) {
			return '';
		}
		
		// Remove preceding/trailing slashes
		$prefix		= \trim( $prefix, '/' );
		
		// Preliminary cleaning
		$html		= \PubCabin\Util::pacify( $value, true );
		
		// Nothing to format?
		if ( empty( $html ) ) {
			return '';
		}
		
		// Format linebreaks and code
		$html		= static::makeParagraphs( $html );
		
		// Clean up HTML
		$html		= static::tidyup( $html );
		
		// Skip errors
		$err		= \libxml_use_internal_errors( true );
		
		// HTML tag filter
		$dom		= new \DOMDocument();
		$lstate		= 
		$dom->loadHTML( 
			$html, 
			\LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | 
			\LIBXML_NOERROR | \LIBXML_NOWARNING | 
			\LIBXML_NOXMLDECL | \LIBXML_COMPACT | 
			\LIBXML_NOCDATA | \LIBXML_NONET
		);
		
		// Loading failed?
		if ( !$lstate ) {
			// Log last error if possible and return
			$e = \libxml_get_last_error();
			if ( false !== $e ) {
				errors( 
					$e->message ?? 
					'Error loading DOMDocument' 
				);
			}
				
			\libxml_clear_errors();
			\libxml_use_internal_errors( $err );
			return '';
		}
		
		$domBody	= $dom->getElementsByTagName( 'body' );
		$flush		= [];
		
		// Iterate through every HTML element 
		if ( !empty( $domBody->childNodes ) ) {
			// Use form inclusive tags if this is a form page
			$wtags	= $form ? $white['form'] : $white['html'];
			foreach ( $domBody->childNodes as $node ) {
				static::scrub( 
					$node, $wtags, $prefix, $flush 
				);
			}
		}
		
		// Remove any tags not found in the whitelist
		if ( !empty( $flush ) ) {
			foreach ( $flush as $node ) {
				if ( $node->nodeName == '#text' ) {
					continue;
				}
				// Replace tag with harmless text
				$safe	= $dom->createTextNode( 
						$dom->saveHTML( $node )
					);
				$node->parentNode
					->replaceChild( $safe, $node );
			}
		}
		
		// Fix formatting
		$dom->formatOutput	= true;
		$clean			= $dom->saveHTML();
		$clean			= 
			static::makeParagraphs( $clean, true );
		
		// Final clean
		$clean			= static::tidyup( $clean );
		
		\libxml_clear_errors();
		\libxml_use_internal_errors( $err );
		
		return $clean;
	}
}



