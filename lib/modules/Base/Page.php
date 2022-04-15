<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Base/Page.php
 *  @brief	Basic site content unit
 */

namespace PubCabin\Modules\Base;

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
	 *  Display render override HTML
	 *  @var string
	 */
	public $render;
	
	/**
	 *  Page type and behavior identifier
	 *  @var \PubCabin\Core\PageType
	 */
	protected $_ptype;
	
	/**
	 *  Page type label description
	 *  @var string
	 */
	public $type_label;
	
	/**
	 *  Modified behavior from settings
	 *  @var array
	 */
	protected $_type_behavior;
	
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
	
	/**
	 *  Set or override current page type
	 *  
	 *  @param string	$name	Type parameter identifier
	 *  @param mixed	$value	Override value
	 */
	protected function setPageType( string $name, $value ) {
		// Override current type
		if ( 
			0 == \strcasecmp( $name, 'page_type' )	&& 
			!\is_string( $value )			&& 
			!\is_array( $value )			&& 
			!\is_int( $value ) 
		) {
			if ( $value instanceof \PubCabin\Modules\Base\PageType ) {
				$this->_ptype = $value;
				return;
			}
			
		// Else init type
		} elseif ( !isset( $this->_ptype ) ) {
			$this->_ptype = new PageType();
		}
		
		switch ( $name ) {
			case 'type_id':
				$this->_ptype->id = ( int ) $value;
				break;
				
			case 'type_render':
				$this->_ptype->render = $value;
				break;
				
			case 'type_behavior':
				$this->_ptype->behavior = $value;
				break;
		}
	}
	
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
				
			// Override type
			case 'page_type':
			case 'type_id':
			case 'type_render':
			case 'type_behavior':
				$this->setPageType $name, $value );
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
			
			case 'page_type':
				return $this->_ptype;
			
			default:
				return parent::__get( $name );
		}
	}
	
	// TODO
	public function save() : bool {
		if ( isset( $this->id ) ) {
			
		} else {
			
		}
		
		return true;
	}
	
	/**
	 *  Get parent hierarchy of given page for breadcrumbs etc...
	 *  
	 *  @param int			$id	Page unique identifier
	 *  @return array
	 */
	public static function getParents( int $id ) : array {
		static $sql = 
		"WITH RECURSIVE ph ( id, parent_id ) AS (
			SELECT id, parent_id FROM pages WHERE id = :id
		
			UNION ALL
			SELECT p.id, p.parent_id FROM pages p 
			JOIN ph ON p.parent_id = ph.id
		) SELECT DISTINCT * FROM ph WHERE id IS NOT :cid;";
		
		$data	= static::getData();
		return 
		$data->getResults( 
			$sql, 
			[ ':id' => $id, ':cid' => $id ], 
			static::dsn( static::MAIN_DATA )
		);
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


