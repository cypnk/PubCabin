<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Site.php
 *  @brief	Website base data object
 */

namespace PubCabin\Core;

class Site extends \PubCabin\Entity {
	
	/**
	 *  Site description
	 *  @var string
	 */ 
	public $label;
	
	/**
	 *  Domain or IP address
	 *  @var string
	 */
	public $basename;
	
	/**
	 *  Alternate domain or IP address
	 *  @var string
	 */
	public $base_alias;
	
	/**
	 *  URL or sub section path
	 *  @var string
	 */
	public $basepath;
	
	/**
	 *  Site is currently active if true
	 *  @var bool
	 */
	public $is_active;
	
	/**
	 *  Site under maintenance if true
	 *  @var bool
	 */
	public $is_maintenance;
	
	/**
	 *  Create or update site entity
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @return bool			True on success
	 */
	public function save( \PubCabin\Data $data ) : bool {
		$params	= [
			':label'	=> $this->label,
			':basename'	=> $this->basename,
			':basepath'	=> $this->basepath ?? '/',
			':settings'	=> \PubCabin\Util::encode( $this->settings ),
			':active'	=> ( int ) ( $this->is_active ?? 1 ),
			':maint'	=> ( int ) ( $this->is_maintenance ?? 0 )
		];
		
		if ( empty( $this->id ) ) {
			$sql = 
			"INSERT INTO sites ( 
				label, basename, basepath, settings, 
				is_active, is_maintenance
			) VALUES ( :label, :basename, :basepath, :settings, 
				:active, :maint );";
			
			$this->id = 
			$data->setInsert( $sql, $params, static::MAIN_DATA );
			
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] => $this->id;
		$sql = 
		"UPDATE sites SET label = :label, basename = :basename, 
			basepath = :basepath, settings = :settings, 
				is_active = :active, is_maintenance = :maint 
			WHERE id = :id LIMIT 1;";
		
		return $data->setUpdate( $sql, $params, static::MAIN_DATA );
	}
	
	/**
	 *  Find site by domain prefixed path and base URI
	 *  
	 *  @param \PubCabin\Data	$data	Storage handler
	 *  @param string		$path	URI String including domain
	 *  @param int			$depth	Maximum subdirectory depth
	 *  @return array
	 */
	public static function findByPath(
		\PubCabin\Data	$data, 
		string		$path, 
		int		$depth
	) : array {
		$segs	= \PubCabin\Util::trimmedList( $path, false, '/' );
		if ( empty( $segs ) ) {
			return [];
		}
		
		$domain	= \array_shift( $segs );
		
		// Trim to max depth
		if ( count( $segs ) > $depth ) {
			$segs = \array_slice( $segs, 0, $depth );
		}
		
		$dirs	= [];
		$paths	= [];
		foreach( $segs as $s ) {
			$dirs[]		= $s;
			$paths[]	= 
			implode( '/', $dirs ) . '/' . $s;
		}
		
		$params	= [ 
			':balias'	=> $domain,
			':bname'	=> $domain
		];
		
		$ins	= $data->getInParam( $paths, $params );
		$db	= $data->getDb( static::MAIN_DATA );
		$stm	= 
		$db->prepare( 
			"SELECT * FROM sites_enabled WHERE 
				( base_alias = :balias OR 
					basename = :bname ) 
				AND basepath {$ins}
				ORDER BY basepath DESC;"
		);
		
		return 
		$data->getDataResult( 
			$db, 
			$params, 
			'class, \\PubCabin\Core\\Site', 
			$stm 
		);
	}
}

