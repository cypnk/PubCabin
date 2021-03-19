<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Comment.php
 *  @brief	Page feedback item
 */

namespace PubCabin\Core;

class Comment extends \PubCabin\Entity {
	
	/**
	 *  Registered user id if comment by logged in member
	 *  @var int
	 */
	public $user_id;
	
	/**
	 *  Raw content as entered by the user
	 *  @var string
	 */
	public $body;
	
	/**
	 *  Filtered content stripped of any HTML or formatting
	 *  @var string
	 */
	public $bare;
	
	/**
	 *  Comment visible to all if true
	 *  @var bool
	 */
	protected $_is_approved;
	
	/**
	 *  Registered user or guest author given name
	 *  @var string
	 */
	public $author_name;
	
	/**
	 *  User stored email or guest author email
	 *  @var string
	 */
	public $author_email;
	
	/**
	 *  Guest author homepage
	 *  @var string
	 */
	public $author_url;
	
	/**
	 *  Client IP address as IPv4 or IPv6
	 *  @var string
	 */
	public $author_ip;
	
	
	
	/**
	 *  View based fields
	 */
	
	/**
	 *  Feedback language
	 *  @var int
	 */
	public $lang_id;
	
	/**
	 *  Comment's contextual language label
	 *  @var string
	 */
	public $lang_label;
	
	/**
	 *  2-Letter language code E.G. en, jp etc...
	 *  @var string
	 */
	public $lang_iso;
	
	/**
	 *  Previously approved comment
	 *  @var int
	 */
	public $prev_id;
	
	/**
	 *  Next approved comment
	 *  @var int
	 */
	public $next_id;
	
	/**
	 *  Created date and unique identifier based permalink URL
	 *  @var string
	 */
	public $id_link;
	
	/**
	 *  If registered user comment, last activity timestamp
	 *  @var string
	 */
	public $author_last_active;
	
	/**
	 *  Registered user comment, last login timestamp
	 *  @var string
	 */
	public $author_last_login;
	
	/**
	 *  Registered user id
	 *  @var int
	 */
	public $author_id;
	
	
	public function __set( $name, $value ) {
		
		switch ( $name ) {
			
			case 'is_approved':
				$this->_is_approved = ( bool ) $value;
				break;
			
			default:
				parent::__set( $name, $value );
		}
	}
	
	public function __get( $name ) {
		
		switch ( $name ) {
			
			case 'is_approved':
				return $this->_is_approved ?? false;
				break;
				
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
}


