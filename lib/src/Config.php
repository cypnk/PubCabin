<?php declare( strict_types = 1 );
/**
 *  @file	/libs/src/Config.php
 *  @brief	Global configuration and settings
 */
namespace PubCabin;

class Config {
	
	/**
	 *  Default configuration file name in the data directory
	 */
	const DEFAULT_CONFIG	= 'defaultconfig.json';
	
	/**
	 *  Configuration placeholder replacements
	 *  @var array
	 */
	private static $replacements	= [
		'{path}'	=> \PUBCABIN_PATH,
		'{store}'	=> \PUBCABIN_DATA,
		'{files}'	=> \PUBCABIN_FILES,
		'{cache}'	=> \PUBCABIN_CACHE,
		'{backup}'	=> \PUBCABIN_BACKUP,
		'{modfiles}'	=> \PUBCABIN_MODSTORE,
		'{error}'	=> \PUBCABIN_ERRORS
	];
	
	/**
	 *  Configuration presets
	 *  @var array
	 */
	private static $options		= [];
	
	public function __construct() {
		// Default options loaded first
		$this->overrideDefaults( $this->loadDefaults() );
	}
	
	/**
	 *  Replace relative paths and other placeholder values
	 *  
	 *  @param mixed	$settings	Raw configuration
	 *  @return mixed
	 */
	public function placeholders( $settings ) {
		// Replace if string
		if ( \is_string( $settings ) ) {
			return 
			\strtr( $settings, static::$replacements );
		
		// Keep going if an array
		} elseif ( \is_array( $settings ) ) {
			return $this->placeholders( $settings );
		}
		
		// Everything else as-is
		return $settings;
	}
	
	/**
	 *  Overriden configuration, if set
	 * 
	 *  @return array
	 */
	public function getConfig() : array {
		return static::$options;
	}
	
	/**
	 *  Override default configuration with new runtime defaults
	 *  E.G. From database
	 * 
	 *  @param array	$options	New configuration
	 */
	public function overrideDefaults( array $options ) {
		static::$options = 
		\array_merge( static::$options, $options );
		
		foreach ( static::$options as $k => $v ) {
			static::$options[$k] = 
				$this->placeholders( $v );
		}
	}
	
	/**
	 *  Get configuration setting or default value
	 *  
	 *  @param string	$name		Configuration setting name
	 *  @param string	$type		String, integer, or boolean
	 *  @param string	$filter		Optional parse function
	 *  @return mixed
	 */
	public function setting( 
		string		$name, 
		string		$type		= 'string',
		string		$filter		= ''
	) {
		if ( !isset( static::$options[$name] ) ) { 
			return null;
		}
		
		switch( $type ) {
			case 'int':
			case 'integer':
				return ( int ) static::$options[$name];
				
			case 'bool':
			case 'boolean':
				return ( bool ) static::$options[$name];
			
			case 'json':
				$json	= static::$options[$name];
				
				return 
				\is_array( $json ) ? 
					$json : 
					Util::decode( ( string ) $json );
					
			case 'lines':
				$lines	= static::$options[$name];
				
				return 
				\is_array( $lines ) ? 
					$lines : 
					FileUtil::lineSettings( 
						( string ) $lines, 
						$filter
					);
			
			// Core configuration setting fallback
			default:
				return static::$options[$name];
		}
	}
	
	/**
	 *  Helper to determine if given hash algo exists or returns default
	 *  
	 *  @param string	$token		Configuration setting name
	 *  @param string	$default	Defined default value
	 *  @param bool		$hmac		Check hash_hmac_algos() if true
	 *  @return string
	 */
	public function hashAlgo(
		string	$token, 
		string	$default, 
		bool	$hmac		= false 
	) : string {
		static $algos	= [];
		$t		= $token . ( string ) $hmac;
		if ( isset( $algos[$t] ) ) {
			return $algos[$t];
		}
		
		$ht		= $this->setting( $token ) ?? $default;
		
		$algos[$t]	= 
			\in_array( $ht, 
				( $hmac ? \hash_hmac_algos() : \hash_algos() ) 
			) ? $ht : $default;
			
		return $algos[$t];	
	}
	
	/**
	 *  Load default configuration
	 *  
	 *  @return array
	 */
	protected function loadDefaults() : array {
		$data = FileUtil::loadFile( self::DEFAULT_CONFIG );
		if ( empty( $data ) ) {
			return [];
		}
		
		return Util::decode( $data );
	}
}

