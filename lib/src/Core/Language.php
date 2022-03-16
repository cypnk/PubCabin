<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Core/Language.php
 *  @brief	Site content translation unit
 */
namespace PubCabin\Core;

class Language extends \PubCabin\Entity {
	
	/** 
	 *  Currently supported languages and locales
	 *  @var array
	 */
	private static $supported_lang	= [ 'en' => [ 'us' ] ];
	
	/**
	 *  Definition placeholder replacements
	 *  @var array
	 */
	private static $placeholders	= [];
	
	/**
	 *  Contextual language label E.G. English, 日本語 etc...
	 *  @var string
	 */
	public $label;
	
	/**
	 *  2-Letter language code E.G. en, jp etc...
	 *  @var string
	 */
	public $iso_code;
	
	/**
	 *  3-Letter language group, E.G. eng, jpn etc...
	 *  @var string
	 */
	public $lang_group;
	
	/**
	 *  Language-specific locale. E.G. for English US, UK etc...
	 *  @var string
	 */
	public $locale;
	
	/** 
	 *  This is the global default language if true
	 *  @var bool
	 */
	protected $_lang_default;
	
	/** 
	 *  This is the global default locale if true
	 *  @var bool
	 */
	protected $_locale_default;
	
	/**
	 *  Array of language placeholders
	 *  @var array
	 */
	protected $_def;
	
	/**
	 *  Locale translations
	 *  @var array
	 */
	public	$translations	= [];
	
	/**
	 *  Override default supported languages
	 *  
	 *  @param array	$lang	Languages and locales by priority
	 */
	public static function setSupported( array $lang ) {
		static::$supported_lang = $lang;
	}
	
	/**
	 *  Set definition placeholders
	 */
	public static function setPlaceholders( array $place ) {
		static::$placeholders  = $place;
	}
	
	/**
	 *  First supported language and first locale
	 *  
	 *  @return array
	 */
	private static function priorityLang() : array {
		$k = \array_keys( static::$supported_lang )[0];
		$v = static::$supported_lang[$k][0];
			
		return [ 'lang' => $k, 'locale' => $v ];
	}
	
	private static function loadLanguage( 
		\PubCabin\Data	$data,
		array		$lang 
	) {
		static $default = 
		"SELECT * FROM locale_view WHERE 
			is_lang_default = 1 AND 
			is_locale_default = 1 LIMIT 1;";
		
		$db		= $data->getDb( static::MAIN_DATA );
		
		// Default language and default locale
		if ( 0 === \strcmp( $lang['lang'], 'default' ) ) {
			$sql	= $default;
				
			$params	= [];
		
		// Set language, default locale
		} elseif ( 0 === \strcmp( $lang['locale'], 'default' ) ) {
			$sql	=
			"SELECT * FROM locale_view WHERE 
				lang = :lang AND is_locale_default = 1
				LIMIT 1;";
			
			$params = [ ':lang' => $lang['lang'] ];
		
		// Set language and set locale
		} else {
			$sql	=
			"SELECT * FROM locale_view WHERE 
				lang = :lang AND locale = :locale 
				LIMIT 1;";
			$params = [
				':lang'		=> $lang['lang'],
				':locale'	=> $lang['locale']
			];
		}
		
		$stm	= $db->prepare( $sql );
		$res	= $db->getDataResult( $db, $params, 'item', $stm );
		// Fallback to default
		if ( empty( $res ) ) {
			$stm	= $db->prepare( $default );
			$res	= $db->getDataResult( $db, [], 'item', $stm );
		}
		
		return $res;
	}
	
	public static function find( 
		\PubCabin\Data	$data,
		array		$vlang
	) {
		// Language setting and priority or default
		if ( empty( $vlang ) ) {
			$vlang = static::priorityLang();
			return static::loadLanguage( $data, $lang );
		}
		
		$lang	= [];
		
		// Filter out languages to those supported
		foreach ( $vlang as $l ) {
			if ( empty( $l['lang'] ) ) {
				continue;
			}
			
			if ( \in_array( $l['lang'], 
				\array_keys( static::$supported_lang ) 
			) ) {
				// Set first matching supported language
				$lang['lang'] = $l['lang'];
			
				if ( !\in_array( 
					$l['locale'] ?? '', 
					static::$supported_lang[$l['lang']]
				) ) {
					$lang['locale'] = 'default';
					
				} else {
					$lang['locale'] = $l['locale'];
				}
				break;
			}
		}
		
		// No supported matches? Get default instead
		if ( empty( $lang ) ) {
			$lang = [ 
				'lang' => 'default', 
				'locale' => 'defalut' 
			];
		}
		return static::loadLanguage( $data, $lang );
	}
	
	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'definitions':
				$this->_def = 
				\is_array( $value ) ? 
					// Send array as-is
					$value :
					
					// Or parse as JSON with placeholders
					\PubCabin\Util::decode( 
						\strtr( 
							( string ) $value, 
							static::$placeholders
						)
					);
				break;
			
			case 'is_lang_default':
				if ( !\is_array( $value ) ) {
					$this->_lang_default = 
						( bool ) $value;
				}
				
				break;
				
			default:
				parent::__set( $name, $value );
		}
		
	}
	
	public function __get( $name ) {
		switch ( $name ) {
			case 'is_lang_default':
				return $this->_lang_default;
				
			case 'is_locale_default':
				return $this->_locale_default;
			
			case 'definitions':
				return 
				isset( $this->_def ) ? $this->_def : [];
			
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

