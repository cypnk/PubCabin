<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Page.php
 *  @brief	Basic site content unit
 */

namespace PubCabin\Core;

class Page extends \PubCabin\Entity {
	
	/**
	 *  Source website
	 *  @var int
	 */
	public $site_id;
	
	/**
	 *  Currently loaded text language
	 *  @var int
	 */
	public $lang_id;
	
	/**
	 *  Content anchor region
	 *  @var int
	 */
	public $area_id;
	
	/**
	 *  Content anchor description
	 *  @var string
	 */
	public $area_label;
	
	/**
	 *  Parent item for subcategories
	 *  @var int
	 */
	public $parent_id;
	
	/**
	 *  This is the website homepage if true
	 *  @var bool
	 */
	public $is_home;
	
	/**
	 *  Page specific type E.G. blogpost, forum, shop etc...
	 *  @var string
	 */
	public $ptype;
	
	/**
	 *  Display render override HTML
	 *  @var string
	 */
	public $render;
	
	/**
	 *  Creator ID
	 *  @var int
	 */
	public $user_id;
	
	/**
	 *  Publish datetime stamp
	 *  @var string
	 */
	public $published;
	
	/**
	 *  Enable visitor feedback if true
	 *  @var bool
	 */
	public $allow_comments;
	
	/**
	 *  Allow sub pages to be added to this one if true
	 *  @var bool
	 */
	public $allow_children;
	
	/**
	 *  Visitor feedback comment count
	 *  @var int
	 */
	public $comment_count;
	
	/**
	 *  Sub page count
	 *  @var int
	 */
	public $child_count;
	
	/**
	 *  Page tag term, category or other sorting taxonomy
	 *  @var array
	 */
	protected $_taxonomy	= [];
	
	/**
	 *  Authorship or ownership
	 *  @var array
	 */
	public $page_users	= [];
	
	/**
	 *  Content texts attached to this page
	 *  @var array
	 */
	public $page_texts	= [];
	
	/**
	 *  Previously published relative page id
	 *  @var int
	 */
	public $prev_id;
	
	/**
	 *  Next published relative page id
	 *  @var int
	 */
	public $next_id;
	
	/**
	 *  Topmost text content
	 */
	
	/**
	 *  Primary text id
	 */
	public $text_id;
	
	/**
	 *  Page text title
	 *  @var string
	 */
	public $title;
	
	/**
	 *  Page path URL relative to site basepath
	 *  @var string
	 */
	public $url;
	
	/**
	 *  URL friendly slug, usually from title
	 *  @var string
	 */
	public $slug;
	
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			// Set category or terms etc...
			case 'taxonomy':
				$this->_taxonomy = 
				\is_array( $value ) ? 
					$value : 
					static::applyTaxonomy( 
						( string ) $value 
					);
				break;
			
			// Fallback
			default:
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'taxonomy':
				return $this->_taxonomy;
			
			default:
				return parent::__get( $name );
		}
	}
	
	// TODO
	public function save( \PubCabin\Data $data ) : bool {
		if ( isset( $this->id ) ) {
			
		} else {
			
		}
		
		return true;
	}
	
	/**
	 *  Helper to turn concated labels and fields into taxonomy
	 *  
	 *  @param string	$field		Tag or category field from user form
	 *  @return array
	 */
	public static applyTaxonomy( string $field = null ) : array {
		if ( empty( $filed ) ) {
			return [];
		}
		
		\preg_match_all( 
			'/(?<key>[\p{L}\-\s_]+)(?:=)(?<value>[\p{L}\p{M}\p{N}\-\s_]+)(?:\&)?/is', 
			$field, 
			$matches 
		);
		
		$matches =
			\array_filter( 
			$matches, 
			function( $k ) {
				return !\is_numeric( $k );
			}, \ARRAY_FILTER_USE_KEY 
		);
		
		return empty( $matches ) ? [] : $matches;
	}
}


