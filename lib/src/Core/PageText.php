<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/PageText.php
 *  @brief	Site content text data
 */

namespace PubCabin\Core;

class PageText extends \PubCabin\Entity {
	
	/**
	 *  Anchor content page
	 *  @var int
	 */
	public $page_id;
	
	/**
	 *  Localized page content heading
	 *  @var string
	 */
	public $title;
	
	/**
	 *  Prefix URL before slug or language options
	 *  @var string
	 */
	public $url;
	
	/**
	 *  URL friendly page slug, usually from title
	 *  @var string
	 */
	public $slug;
	
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
	 *  Base language
	 *  @var int
	 */
	public $lang_id;
	
	/**
	 *  Local URL path
	 *  @var int
	 */
	public $path_id;
	
	/**
	 *  External text body sources
	 *  @var array
	 */
	public $text_sources	= [];
	
	/**
	 *  Edit history
	 *  @var array
	 */
	public $revisions	= [];
	
	/**
	 *  Remote text options
	 */
	
	/**
	 *  External source path
	 *  @var string
	 */
	public $remote_url;
	
	/**
	 *  External source expiration (time to live)
	 *  @var int
	 */
	public $remote_ttl;
	
	/**
	 *  External source created date, if available
	 *  @var string
	 */
	public $remote_created;
	
	/**
	 *  External source last modified date, if available
	 *  @var string
	 */
	public $remote_updated;
	
	// TODO
	public function save() { }
	
}
