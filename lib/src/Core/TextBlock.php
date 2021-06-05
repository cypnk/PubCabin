<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/TextBlock.php
 *  @brief	Segmented page content
 */

namespace PubCabin\Core;

class TextBlock extends \PubCabin\Entity {
	
	/**
	 *  Parent page text anchor
	 *  @var int
	 */
	public $text_id;
	
	/**
	 *  Authorship or editorial relationships
	 *  @var array
	 */
	private $_users	= [];
	
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
	
	// TODO: Parse authorship
	public function save( \PubCabin\Data $data ) : bool {
		$params	= [
			':body'	=> $this->body,
			':bare'	=> $this->bare
		];
		
		if ( empty( $this->id ) ) {
			$sql			= 
			"INSERT INTO text_blocks (
				body, bare, text_id
			) VALUES ( :body, :bare, :text_id )";
			
			$params[':text_id']	= $this->text_id;
			
			$this->id		= 
			$data->setInsert( $sql, $params, static::MAIN_DATA );
			
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] => $this->id;
		$sql = 
		"UPDATE text_blocks SET body = :body, bare = :bare 
			WHERE id = :id LIMIT 1;";
		
		return $data->setUpdate( $sql, $params, static::MAIN_DATA );
	}
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'users':
				// TODO: Parse editorial relationships
				return;
		}
		
		parent::__set( $name, $value );
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'users':
				return $_users;
		}
		
		return parent::__get( $name );
	}
}


