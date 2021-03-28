<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Module/Forms/Module.php
 *  @brief	User input, form field generator, and handler
 */
namespace PubCabin\Modules\Forms;

class Module extends \PubCabin\Modules\Module {
	
	// Form check statuses
	const FORM_STATUS_VALID		= 0;
	const FORM_STATUS_INVALID	= 1;
	const FORM_STATUS_EXPIRED	= 2;
	const FORM_STATUS_FLOOD		= 3;
	
	const NONCE_HASH		= 'tiger160,4';
	const TOKEN_BYTES		= 8;
	
	public function dependencies() : array {
		return [ 'Hooks', 'Sessions', 'Files', 'Styles', 'Sites' ];
	}
	
	/**
	 *  Submitted HTML formatting helper
	 *  
	 *  @param string	$html	Raw input from the user
	 *  @param string	$path	Relative link URL path
	 *  @param bool		$form	Allow form field related HTML
	 * 
	 */
	public function formatHTML(
		string		$html, 
		string		$path	= '/', 
		bool		$form	= false
	) : string {
		$config	= $this->getConfig();
		
		// Base HTML whitelist
		$white	= [
			'html'	=> $config->setting( 'tag_white', 'json' )
		];
		
		// Add form tag whitelist if this is a form
		if ( $form ) {
			$white['form']	= 
			\array_merge( 
				$white['html'], 
				$config->setting( 'form_white', 'json' )
			);
		}
		
		return Html::html( $html, $path, $white );
	}
	
	/**
	 *  Initiate field token or reset existing
	 *  
	 *  @return string
	 */ 
	public function tokenKey( bool $reset = false ) : string {
		$this->getModule( 'Sessions' )->sessionCheck();
		if ( empty( $_SESSION['TOKEN_KEY'] ) || $reset ) {
			$_SESSION['TOKEN_KEY'] = 
				\PubCabin\Util::genId( 16 );
		}
		
		return $_SESSION['TOKEN_KEY'];
	}
	
	/**
	 *  Generate a hash for meta data sent to HTML forms
	 *  
	 *  This function helps prevent tampering of metadata sent separately
	 *  to the user via other hidden fields
	 *  
	 *  @example genMetaKey( [ 'id=12','name=DoNotChange' ] ); 
	 *  
	 *  @param array	$args	Form field names sent to generate key
	 *  @param bool		$reset	Reset any prior token key if true
	 *  @return string
	 */
	public function genMetaKey( array $args, bool $reset = false ) : string {
		static $gen	= [];
		$data		= \implode( ',', $args );
		
		if ( \array_key_exists( $data, $gen ) && !$reset ) {
			return $gen[$data];
		}
		
		$ha		= 
		$this->getConfig()->hashAlgo( 'nonce_hash', 'string' ) ?? 
			self::NONCE_HASH;
		$gen[$data]	= 
		\base64_encode( 
			\hash( 
				$ha, 
				$data . $this->tokenKey( $reset ), true 
			) 
		);
		
		return $gen[$data];
	}
	
	/**
	 *  Verify meta data key
	 *  
	 *  @param string	$key	Token key name
	 *  @param array	$args	Original form field names sent to generate key
	 *  @return bool		True if token matched
	 */
	public function verifyMetaKey( string $key, array $args ) : bool {
		if ( empty( $key ) ) {
			return false;
		}
		
		$info	= \base64_decode( $key, true );
		if ( false === $info ) {
			return false;
		}
		
		$data	= \implode( ',', $args );
		$ha	= 
		$this->getConfig()->hashAlgo( 'nonce_hash', 'string' );
		
		return 
		\hash_equals( 
			$info, 
			\hash( $ha, $data . tokenKey(), true ) 
		);
	}
	
	/**
	 *  Create a unique nonce and token pair for form validation and meta key
	 *  
	 *  @param string	$name	Form label for this pair
	 *  @param array	$fields	If set, append form anti-tampering token
	 *  @param bool		$reset	Reset any prior anti-tampering token key if true
	 *  @return array
	 */
	public function genNoncePair( 
		string		$name, 
		array		$fields		= [], 
		bool		$reset		= false 
	) : array {
		$config = $this->getConfig();
		
		$tb	= $config->setting( 'token_bytes', 'int' ) ?? 
			self::TOKEN_BYTES;
		$ha	= $config->hashAlgo( 'nonce_hash', 'string' ) ?? 
			self::NONCE_HASH;
	
		$nonce	= 
		\PubCabin\Util::genId( 
			\PubCabin\Util::intRange( $tb, 8, 64 ) 
		);
		
		$time	= time();
		$data	= $name . $time;
		$token	= "$time:" . \hash( $ha, $data . $nonce );
		return [ 
			'token' => \base64_encode( $token ), 
			'nonce' => $nonce,
			'meta'	=> 
			empty( $fields ) ? 
				'' : 
				$this->genMetaKey( $fields, $reset )
		];
	}
	
	/**
	 *  Verify form submission by checking sent token and nonce pair
	 *  
	 *  @param string	$name	Form label to validate
	 *  @params string	$token	Sent token
	 *  @params string	$nonce	Sent nonce
	 *  @param bool		$chk	Check for form expiration if true
	 *  @return int
	 */
	public function verifyNoncePair(
		string		$name, 
		string		$token, 
		string		$nonce,
		bool		$chk
	) : int {
		
		$ln	= \strlen( $nonce );
		$lt	= \strlen( $token );
		
		// Sanity check
		if ( 
			$ln > 100 || 
			$ln <= 10 || 
			$lt > 350 || 
			$lt <= 10
		) {
			return self::FORM_STATUS_INVALID;
		}
		
		// Open token
		$token	= \base64_decode( $token, true );
		if ( false === $token ) {
			return self::FORM_STATUS_INVALID;
		}
		
		// Token parameters are intact?
		if ( false === \strpos( $token, ':' ) ) {
			return self::FORM_STATUS_INVALID;
		}
		
		$parts	= \explode( ':', $token );
		$parts	= \array_filter( $parts );
		if ( \count( $parts ) !== 2 ) {
			return self::FORM_STATUS_INVALID;
		}
		
		$config	= $this->getConfig();
		if ( $chk ) {
			// Check for flooding
			$time	= time() - ( int ) $parts[0];
			$fdelay	= $config->setting( 'form_delay', 'int' );
			if ( $time < $fdelay ) {
				return self::FORM_STATUS_FLOOD;
			}
			
			// Check for form expiration
			$fexp	= $config->setting( 'form_expire', 'int' );
			if ( $time > $fexp ) {
				return self::FORM_STATUS_EXPIRED;
			}
		}
		
		$ha	= $config->hashAlgo( 'nonce_hash', self::NONCE_HASH );
		$data	= $name . $parts[0];
		$check	= \hash( $ha, $data . $nonce );
		
		return \hash_equals( $parts[1], $check ) ? 
			self::FORM_STATUS_VALID : 
			self::FORM_STATUS_INVALID;
	}
}


