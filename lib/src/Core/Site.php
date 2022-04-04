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
	
	const DEFAULT_BASEPATH =<<<JSON
{
	"basepath"		: "\/",
	"is_active"		: 1,
	"is_maintenance"	: 0,
	"settings"		: []
}
JSON;
	
	/**
	 *  Data SQL strings
	 *  @var array
	 */
	protected static $sql	= [
		"insert"	=>
		"INSERT INTO sites ( 
			label, basename, basepath, settings, 
			is_active, is_maintenance
		) VALUES ( :label, :basename, :basepath, :settings, 
			:active, :maint );",
		
		"update"	=> 
		"UPDATE sites SET label = :label, basename = :basename, 
			basepath = :basepath, settings = :settings, 
			is_active = :active, is_maintenance = :maint 
		WHERE id = :id LIMIT 1;"
	];
	
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
			$this->id = 
			$data->setInsert( 
				static::$sql['insert'], 
				$params, 
				static::MAIN_DATA 
			);
			
			return empty( $this->id ) ? false : true;
		}
		
		$params[':id'] = $this->id;
		return 
		$data->setUpdate( 
			static::$sql['update'], 
			$params, 
			static::MAIN_DATA 
		);
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
		$data->statement( $db, 
			"SELECT * FROM sites_enabled WHERE 
				( base_alias = :balias OR 
					basename = :bname ) 
				AND basepath {$ins}
				ORDER BY basepath DESC;"
		);
		
		$result	=
		$data->getDataResult( 
			$db, 
			$params, 
			'class, \\PubCabin\Core\\Site', 
			$stm 
		);
		$stm->closeCursor();
		return $result;
	}
	
	/**
	 *  Format available sites with default parameters
	 *  
	 *  @param array	$sites		Available sites
	 *  @return array
	 */
	public static function formatSites( array $sites ) : array {
		if ( empty( $sites ) ) {
			return [];
		}
		
		$se = [];
		foreach ( $sites as $host => $base ) {
			// Skip if invalid hostname
			if ( false === \filter_var( 
				$host, 
				\FILTER_VALIDATE_DOMAIN,
				\FILTER_FLAG_HOSTNAME
			) ) {
				continue;
			}
			
			// Add default site if empty
			if ( empty( $base ) ) {
				$base	= [
					\PubCabin\Config::setting( 
						'default_basepath', 
						self::DEFAULT_BASEPATH, 
						'json' 
					)
				];
			}
		
			// Decode went wrong or setting is invalid
			if ( !\is_array( $base ) ) {
				continue;
			}
			
			// Found sub sites
			$f = [];
			
			// Set default sub parameters
			foreach ( $base as $b ) {
				if ( !\is_array( $b ) ) {
					continue;
				}
				
				// Slash basepath
				$b['basepath'] = 
				\PubCabin\Util::slashPath( $b['basepath'] ?? '/' );
			
				// Set active mode if not set
				$b['is_active'] ??= 1;
				
				// Set maintenance mode
				$b['is_maintenance'] ??= 0;
				
				// Custom site settings or empty array
				$b['settings'] ??= [];
				$f[] = $b;
			}
			
			// No valid sites?
			if ( empty( $f ) ) {
				continue;
			}
			// Append to enabled sites under this host
			$se[$host] = $f;
		}
		
		\natcasesort( $se );
		return $se;
	}
	
	/**
	 *  Get whitelisted paths for current host
	 *  
	 *  @param string	$host	Current server host
	 *  @param array	$sp	Enabled websites
	 *  @return array
	 */
	public static function getHostPaths( string $host, array $sp ) : array {
		static $paths	= [];
		if ( !empty( $paths[$host] ) ) {
			return $paths[$host];
		}
		
		$sa	= [];
		foreach ( $sp[$host] as $s ) {
			// Assume inactive site if not explicitly enabled
			$a = ( bool ) ( $s['is_active'] ?? false );
			if ( $a ) {
				$sa[] = 
				\PubCabin\Util::slashPath( $s['basepath'] ?? '/' );
			}
		}
		
		\natcasesort( $sa );
		$sa	= \array_unique( $sa, \SORT_STRING );
		
		$paths[$host]	= $sa;
		return $paths[$host];
	}
	
	/**
	 *  Check if the current host and path are in the whitelist
	 *  
	 *  @param string	$host		Server host name
	 *  @param string	$path		Current URI
	 *  @param array	$sp		Enabled websites
	 *  @return bool
	 */
	public static function hostPathMatch( string $host, string $path, array $sp ) : bool {
		$pm	= static::getHostPaths( $host, $sp );
		
		// Root folder is allowed?
		if ( \in_array( '/', $pm, true ) ) {
			return true;
		}
		
		// Shortest matching allowed subfolder
		$pe	= explode( '/', $path );
		$px	= '';
		foreach ( $pe as $k => $v ) {
			$px .= \PubCabin\Util::slashPath( $v );
			if ( \in_array( $px, $pm, true ) ) {
				return true;
			}
		}
		return false;
	}
}

