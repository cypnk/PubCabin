<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Base/TextBlock.php
 *  @brief	Segmented page content
 */

namespace PubCabin\Modules\Base;

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
	
	/**
	 *  Data SQL strings
	 *  @var array
	 */
	protected static $sql	= [
		'insert'	=>
		"INSERT INTO text_blocks 
			( body, bare, sort_order, text_id ) 
		VALUES ( :body, :bare, :sort, :text_id )",
		
		'update'	=>
		"UPDATE text_blocks SET body = :body, bare = :bare, 
			sort_order = :sort WHERE id = :id LIMIT 1;",
		
		'authors'	=>
		"INSERT OR IGNORE INTO text_block_users 
			( block_id, user_id, ttype )
		VALUES ( :block_id, :user_id, :ttype );"
	];
	
	/**
	 *  Create or update text block with author info
	 *  
	 *  @return bool
	 */
	public function save() : bool {
		$this->bare	= \PubCabin\Util::bland( $this->body );
		$params		= [
			':body'	=> $this->body,
			':bare'	=> $this->bare,
			':sort'	=> $this->sort_order
		];
		
		$data		= static::getData();
		if ( empty( $this->id ) ) {
			$params[':text_id']	= $this->text_id;
			
			$this->id		= 
			$data->setInsert( 
				static::$sql['insert'], 
				$params, 
				static::MAIN_DATA 
			);
			
			if ( empty( $this->id ) )  {
				return false;
			}
			$this->saveAuthors( $data );
			return true;
		}
		
		$params[':id'] => $this->id;
		
		$ok	=  
		$data->setUpdate( 
			static::$sql['update'], 
			$params, 
			static::MAIN_DATA 
		);
		if ( $ok ) {
			$this->saveAuthors( $data );
		}
		return $ok;
	}
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'users':
				// Probably user set
				if ( \is_array( $value ) ) {
					$this->parseAuthors( $value );
					
				// Try to grab from query result
				} elseif ( \is_string( $value ) ) {
					$this->parseAuthors( 
						static::filterAuthors( $value )
					);
				}
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
	
	/**
	 *  String to associative editor array helper
	 *  
	 *  @example
	 *  id[]=id1&ttype[]=editor&id[]=id2&ttype[]=author etc...
	 *  
	 *  @param string	$value	Raw query result
	 *  @return array
	 */
	public static function filterAuthors( string $value ) : array {
		$out = [];
		\parse_str( $value, $out );
		
		if ( empty( $out ) ) {
			return [];
		}
		
		// Some kind of mismatch?
		if ( empty( $out['id'] ) || empty( $out['ttype'] ) ) {
			return [];
		}
		
		if ( count( $out['id'] ) != count( $out['ttype'] ) ) {
			return [];
		}
		
		return \array_combine( $out['id'], $out['ttype'] );
	}
	
	/**
	 *  Process associative array of user ids and editorship statuses
	 *  
	 *  @param array	$users		User id with editor status
	 */
	protected function parseAuthors( array $users ) {
		if ( empty( $users ) ) {
			return;
		}
		foreach ( $users as $k => $v ) {
			if ( !\is_numeric( $k ) ) {
				continue;
			}
			
			// Set id with text editor type. Default to 'editor'
			$this->_users[] = [
				'id'	=> ( int ) $k,
				'ttype' => 
				\PubCabin\Util::labelName( $v ?? 'editor' )
			];
		}
	}
	
	/**
	 *  Apply block text and text user relationships
	 */
	protected function saveAuthors() {
		// No users to add?
		if ( empty( $this->_users ) ) {
			return;
		}
		
		$params = [];
		foreach ( $this->_users as $u ) {
			$params[] = [
				':block_id'	=> $this->id,
				':user_id'	=> $u['id'],
				':ttype'	=> $u['ttype']
			];
		}
		
		$data	= static::getData();
		$data->dataBatchExec( 
			static::$sql['authors'], 
			$params, 
			'success', 
			static::MAIN_DATA 
		);
	}
}


