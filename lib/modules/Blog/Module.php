<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Blog/Module.php
 *  @brief	User weblog and syndication feed
 */
namespace PubCabin\Modules\Blog;

class Module extends \PubCabin\Modules\Module {
	
	// Maximum page index
	const MAX_PAGE		= 500;
	
	// Starting date for post archive
	const YEAR_START	= 2000;
	
	// Ending date for post archive
	const YEAR_END		= 2099;
	
	/**
	 *  List of events attached to the blog module
	 *  @var array
	 */
	protected static $event_list = [
		// Primary views
		'blogArchiveView',
		'blogArchiveIndex',
		'blogFeedView',
		'blogViewPost',
		
		'blogMainView',
		'blogSearchView',
		'blogExcerptView',
		'blogRelatedView',
		'blogNextPrevView',
		
		// Events
		'blogSettings',
		'blogCreating',
		'blogEditing',
		'blogDeleting',
		'blogSave',
		
		'blogPostCreating',
		'blogPostEditing',
		'blogPostDeleting',
		'blogPostDraftSaving',
		'blogPostDraftSave',
		'blogPostDraftSaved',
		
		// Processing
		'blogPostPublishing',
		'blogPostPublish',
		'blogPostPublished',
		
		'blogPostCreated',
		'blogPostEdited',
		'blogPostSave',
		'blogPostDeleted',
		
		'blogCommentCreating',
		'blogCommentEditing',
		'blogCommentDeleting',
		'blogCommentSave',
		'blogCommentCreated',
		'blogCommentEdited',
		'blogCommentDeleted',
		
		// When retrieving data
		
		'blogPostLoaded',
		'blogPostUserLoaded',
		
		'blogPostRelatedBefore',
		'blogPostRelatedAfter',
		'blogPostRelated',
		
		'blogPostPreviousBefore',
		'blogPostPrevious',
		'blogPostPreviousAfter',
		
		'blogPostNextBefore',
		'blogPostNext',
		'blogPostNextAfter',
		
		// When editing data
		'blogPostFormRenderBefore',
		'blogPostFormRenderAfter',
		
		'blogPostFormTitleRenderBefore',
		'blogPostFormTitleRenderAfter',
		
		'blogPostFormPubRenderBefore',
		'blogPostFormPubRenderAfter',
		
		'blogPostFormBodyRenderBefore',
		'blogPostFormBodyRenderAfter',
		
		'blogPostFormExcerptRenderBefore',
		'blogPostFormExcerptRenderAfter',
		
		// When rendering data
		'blogPostTitleParsed',
		'blogPostBodyParsed',
		'blogPostExcerptParsed',
		
		'blogCommentTitleParsed',
		'blogCommentBodyParsed',
		'blogCommentAuthorParsed',
		'blogCommentEmailParsed',
		
		// Special components
		'blogCreatingPageHeadBegin',
		'blogCreatingPageHeadEnd',
		'blogCreatingPageBodyBegin',
		'blogCreatingPageBodyEnd',
		
		'blogEditingPageHeadBegin',
		'blogEditingPageHeadEnd',
		'blogEditingPageBodyBegin',
		'blogEditingPageBodyEnd',
		
		'blogDeletingPageHeadBegin',
		'blogDeletingPageHeadEnd',
		'blogDeletingPageBodyBegin',
		'blogDeletingPageBodyEnd',
		
		'blogPostCreatingPageHeadBegin',
		'blogPostCreatingPageHeadEnd',
		'blogPostCreatingPageBodyBegin',
		'blogPostCreatingPageBodyEnd',
		
		'blogPostEditingPageHeadBegin',
		'blogPostEditingPageHeadEnd',
		'blogPostEditingPageBodyBegin',
		'blogPostEditingPageBodyEnd',
		
		'blogPostDeletingPageHeadBegin',
		'blogPostDeletingPageHeadEnd',
		'blogPostDeletingPageBodyBegin',
		'blogPostDeletingPageBodyEnd',
		
		
		'blogCommentCreatingPageHeadBegin',
		'blogCommentCreatingPageHeadEnd',
		'blogCommentCreatingPageBodyBegin',
		'blogCommentCreatingPageBodyEnd',
		
		'blogCommentEditingPageHeadBegin',
		'blogCommentEditingPageHeadEnd',
		'blogCommentEditingPageBodyBegin',
		'blogCommentEditingPageBodyEnd',
		
		'blogCommentDeletingPageHeadBegin',
		'blogCommentDeletingPageHeadEnd',
		'blogCommentDeletingPageBodyBegin',
		'blogCommentDeletingPageBodyEnd',
		
		
		// Rendering blog post
		'blogFeaturedPostBefore',
		'blogFeaturedPostAfter',
		
		'blogRelatedPostsBefore',
		'blogRelatedPostsAfter',
		
		'blogPostRenderBefore',
		'blogPostRenderAfter',
		
		'blogBeforeFeaturedImage',
		'blogAfterFeaturedImage',
		
		'blogPostStatusRenderBefore',
		'blogPostStatusRenderAfter',
		
		'blogPostTitleRenderBefore',
		'blogPostTitleRenderAfter',
		
		'blogPostPubRenderBefore',
		'blogPostPubRenderAfter',
		
		'blogPostBodyRenderBefore',
		'blogPostBodyRenderAfter',
		
		'blogPostExcerptRenderBefore',
		'blogPostExcerptRenderAfter',
		
		// Author information
		'blogPostAuthorRenderBefore',
		'blogPostAuthorRenderAfter',
		
		'blogPostAuthorStatusBefore',
		'blogPostAuthorStatusAfter',
		
		'blogPostBodyRenderBefore',
		'blogPostBodyRenderAfter',
		
		// Feedback?
		'blogPostCommentsEnabled',
		'blogPostCommentsDisabled',
		
		'blogCommentsRenderBefore',
		'blogCommentsRenderAfter',
		
		// Feedback form
		'blogCommentFormBefore',
		'blogCommentFormAfter',
		
		'blogCommentBodyBefore',
		'blogCommentFormAfter',
		
		// Commenter's name (if given or a user)
		'blogCommentAuthorRenderBefore',
		'blogCommentAuthorRenderAfter',
		
		// Commenter is a registered user
		'blogCommentAuthorIsUser',
		
		// Registered username
		'blogCommentUsernameBefore',
		'blogCommentUsernameAfter',
		
		// Commenter's email (if given)
		'blogCommentEmailRenderBefore',
		'blogCommentEmailRenderAfter',
		
		// Commenter's IP address
		'blogCommentIpRenderBefore',
		'blogCommentIpRenderAfter',
		
		// Moderation dropdown
		'blogCommentModRenderBefore',
		'blogCommentModRenderAfter',
		'blogCommentModItemRenderBefore',
		'blogCommentModItemRenderAfter',
		
		// Comment published
		'blogCommentPubRenderBefore',
		'blogCommentPubRenderAfter',
		
		// Rendered status
		'blogCommentStatusBefore',
		'blogCommentStatusAfter',
		
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
		$now	= time();
		$config	= $this->getConfig();
		
		$mpage	= $this->config( 'max_page', 'int' ) ?? 
				self::MAX_PAGE;
		
		$ys	= $this->config( 'year_start', 'int' ) ?? 
				self::YEAR_START;
		
		$ye	= config( 'year_end', 'int' ) ?? 
				self::YEAR_END;
		
		
		$filter	= [
			'id'	=> [
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
					'max_range'	=> $mpage,
					'default'	=> 1
				]
			],
			'year'	=> [
				'filter'	=> \FILTER_SANITIZE_NUMBER_INT,
				'options'	=> [
					'min_range'	=> $ys,
					'max_range'	=> $ye,
					'default'	=> 
					( int ) \date( 'Y', $now )
				]
			],
			'month'	=> [
				'filter'	=> \FILTER_SANITIZE_NUMBER_INT,
				'options'	=> [
					'min_range'	=> 1,
					'max_range'	=> 12,
					'default'	=> 
					( int ) \date( 'n', $now )
				]
			],
			'day'	=> [
				'filter'	=> \FILTER_SANITIZE_NUMBER_INT,
				'options'	=> [
					'min_range'	=> 1,
					'max_range'	=> 31,
					'default'	=> 
					( int ) \date( 'j', $now )
				]
			],
			'tag'	=> [
				'filter'	=> \FILTER_CALLBACK,
				'options'	=> '\PubCabin\Util::unifySpaces'
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
