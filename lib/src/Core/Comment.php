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
	 *  Anonymous author signature
	 *  @var string
	 */
	public $author_sign;
	
	
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
	
	/**
	 *  Update/Insert SQL parameters
	 *  @var array
	 */
	private static $csql = [
		'insauth'	=> 
		"INSERT INTO comments ( body, bare, author_ip, 
			is_approved, user_id, page_id, lang_id )
		VALUES( :body, :bare, :aip, :isap, :uid, :pid, :lid );",
		'insanon'	=>
		"INSERT INTO comments ( body, bare, author_ip, 
			is_approved, author_name, author_sign, 
			author_url, author_email, page_id, lang_id )
		VALUES( :body, :bare, :aip, :isap, :pid, :lid, 
			:aname, :asign, :aurl, :aemail );",
		'upauth'	=> 
		"UPDATE comments SET body = :body, bare = :bare,
			author_ip = :aip, is_approved = :isap
			WHERE id = :id;",
		'upanon'	=> 
		"UPDATE comments SET body = :body, bare = :bare,
			author_ip = :aip, is_approved = :isap, 
			author_name = :aname, author_sign = :asign, 
			author_url = :aurl, author_email = :aemail
			WHERE id = :id;"
	];
		
	
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
	
	/**
	 *  Anon author parameter helper 
	 *  
	 *  @param array	$params		SQL Parameters
	 */
	protected function authorIdentity( array &$params ) {
		$params[':aname']	= $this->author_name ?? null;
		$params[':asign']	= $this->author_sign ?? null;
		$params[':aurl']	= $this->author_url ?? null;
		$params[':aemail']	= $this->author_email ?? null;
	}
	
	public function save( \PubCabin\Data $data ) : bool {
		$params = [
			':body'	=> $this->body,
			':bare'	=> \PubCabin\Util::bland( $this->body ),
			':aip'	=> 
				$this->author_ip ?? 
				static::getRequest()->getIP(),
			':isap'	=> $this->is_approved
		];
		
		if ( isset( $this->user_id ) ) {
			$params[':uid'] = $this->user_id;
		} else {
			$this->authorIdentity( $params );
		}
		
		$db	= $data->getDb( static::MAIN_DATA );
		if ( isset( $this->id ) ) {
			$this->id = 
			$data->setInsert( 
				isset( $this->user_id ) ? 
					static::$csql['insauth'] : 
					static::$csql['insanon'], 
				$params, 
				static::MAIN_DATA 
			);
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] => $this->id;
		return 
		$data->setUpdate( 
			isset( $this->user_id ) ? 
				static::$csql['upauth'] : 
				static::$csql['upanon'], 
			$params, 
			static::MAIN_DATA 
		);
	}
}


