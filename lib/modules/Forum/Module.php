<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Forum/Module.php
 *  @brief	Discussion board and community feedback
 */
namespace PubCabin\Modules\Forum;

class Module extends \PubCabin\Modules\Module {
	
	/**
	 *  List of events attached to the forum module
	 *  @var array
	 */
	protected static $event_list = [
		
		'forumIndex',
		'forumView',
		
		// Events
		'forumSettings',
		'forumCreating',
		'forumEditing',,
		'forumDeleting',
		'forumSorting',
		'forumSave',
		
		'forumCreated',
		'forumEdited',
		'forumDeleted',
		'forumSorted',
		
		'forumPostCreating',
		'forumPostEditing',
		'forumPostDeleting',
		'forumPostFileUploading',
		'forumPostSaving',
		'forumPostStatus',
		
		'forumPostCreated',
		'forumPostEdited',
		'forumPostSave',
		'forumPostDeleted',
		
		// When retrieving data
		
		'forumBoardLoaded',
		'forumThreadLoaded',
		
		'forumPostRelatedBefore',
		'forumPostRelatedAfter',
		'forumPostRelated',
		
		'forumBoardPreviousBefore',
		'forumBoardPrevious',
		'forumBoardPreviousAfter',
		
		'forumBoardNextBefore',
		'forumBoardNext',
		'forumBoardNextAfter',
		
		// When editing categories
		'forumBoardFormTitleRenderBefore',
		'forumBoardFormTitleRenderAfter',
		
		'forumBoardFormBodyRenderBefore',
		'forumBoardFormBodyRenderAfter',
		
		'forumPostPreviousBefore',
		'forumPostPrevious',
		'forumPostPreviousAfter',
		
		'forumPostNextBefore',
		'forumPostNext',
		'forumPostNextAfter',
		
		
		// When editing data
		'forumPostFormRenderBefore',
		'forumPostFormRenderAfter',
		
		'forumPostFormTitleRenderBefore',
		'forumPostFormTitleRenderAfter',
		
		'forumPostFormBodyRenderBefore',
		'forumPostFormBodyRenderAfter',
		
		// Author information
		'forumPostAuthorRenderBefore',
		'forumPostAuthorRenderAfter',
		
		// When rendering data
		'forumPostTitleParsed',
		'forumPostBodyParsed',
		
		// Admin views
		'adminComponentPageBegin',
		'adminComponentPage',
		'adminComponentPageEnd',
		
		'adminComponentLinks',
		'adminBreadcrumbLinks'
	];
	
	
	public function eventList() : array {
		return static::$event_list;	
	}
	
	public function dependencies() : array {
		return [ 
			'Styles', 
			'Forms', 
			'Sites', 
			'Manager', 
			'Membership',
			'Comments'
		];
	}
	
	public function __construct() {
		parent::__construct();
		
		$hooks = $this->getModule( 'Hooks' );
		
		// Register url request
		$hooks->register( [ 'requesturl', [ 
			$this, 'filterRequest' 
		] );
	}
	
	/**
	 *  Request filter event
	 *  
	 *  @param string	$event	Request event name
	 *  @param array	$hook	Previous hook event data
	 *  @param array	$params	Passed event data
	 */
	public function filterRequest( 
		string		$event, 
		array		$hook, 
		array		$params 
	) {
		$filter	= [
			'forum'	=> [
				'filter'	=> \FILTER_VALIDATE_INT,
				'options'	=> [
					'min_range'	=> 1,
					'default'	=> 0
				]
			],
			'topic'	=> [
				'filter'	=> \FILTER_VALIDATE_INT,
				'options'	=> [
					'min_range'	=> 1,
					'default'	=> 0
				]
			],
			'page'	=> [
				'filter'	=> \FILTER_VALIDATE_INT,
				'options'	=> [
					'min_range'	=> 1,
					'default'	=> 1
				]
			],
			'slug'	=> [
				'filter'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'options'	=> [ 'default' => '' ]
			],
			'find'	=> [
				'filter'	=> \FILTER_CALLBACK,
				'options'	=> '\PubCabin\Util::unifySpaces'
			],
			'token'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'nonce'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'meta'	=> \FILTER_SANITIZE_FULL_SPECIAL_CHARS
		];
		
		return 
		\array_merge( $hook, \filter_var_array( $params, $filter ) );
	}
}

