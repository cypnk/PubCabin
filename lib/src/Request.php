<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Request.php
 *  @brief	Client request sent by visitor
 */
namespace PubCabin;

class Request extends Message {
	
	/**
	 *  Visitor's IP address (IPv4 or IPv6)
	 *  @var string
	 */
	private $ip;
	
	/**
	 *  Raw IP address, unfiltered
	 *  @var string
	 */
	private $raw_ip;
	
	/**
	 *  Visitor signature
	 *  @var string
	 */
	private $sig;
	
	/**
	 *  Current request guessed to be a secure connection, if true
	 *  @var bool
	 */
	private $secure;
	
	/**
	 *  Forwarded headers from reverse proxy, load balancer etc...
	 *  @var array
	 */
	private $forwarded;
	
	/**
	 *  Raw user agent header sent by visitor
	 *  @var string
	 */
	private $user_agent;
	
	/**
	 *  Current HTTP request method E.G. get, post, head etc...
	 *  @var string
	 */
	private $request_method;
	
	/**
	 *  Accept languages sorted by priority
	 *  @var array
	 */
	private $language;
	
	/**
	 *  Process HTTP_* variables
	 *  
	 *  @param bool		$lower		Get array keys in lowercase
	 *  @return array
	 */
	public function httpHeaders( bool $lower = false ) : array {
		if ( $lower ) {
			if ( isset( $this->lv_headers ) ) {
				return $this->lv_headers;
			}
		} else {
			if ( isset( $this->headers ) ) {
				return $this->headers;
			}
		}
		
		$val	= [];
		$lval	= [];
		foreach ( $_SERVER as $k => $v ) {
			if ( 0 === \strncasecmp( $k, 'HTTP_', 5 ) ) {
				$a = explode( '_' ,$k );
				\array_shift( $a );
				\array_walk( $a, function( &$r ) {
					$r = \ucfirst( \strtolower( $r ) );
				} );
				$val[ \implode( '-', $a ) ] = $v;
				$lval[ \strtolower( \implode( '-', $a ) ) ] = $v;
			}
		}
		
		$this->headers		= $val;
		$this->lv_headers	= $lval;
		
		return $lower ? $lval : $val;
	}
	
	/**
	 *  Create current visitor's browser signature by sent headers
	 *  
	 *  @return string
	 */
	public function signature() : string {
		if ( isset( $this->sig ) ) {
			return $this->sig;
		}
		
		$headers	= $this->httpHeaders();
		$skip		= 
		[
			'Access-Control-Request-Headers',
			'Access-Control-Request-Method',
			'Upgrade-Insecure-Requests',
			'If-Unmodified-Since',
			'If-Modified-Since',
			'Accept-Datetime',
			'Accept-Encoding',
			'Content-Length',
			'Authorization',
			'Cache-Control',
			'If-None-Match',
			'Content-Type',
			'Content-Md5',
			'Connection',
			'Forwarded',
			'If-Match',
			'Referer',
			'Cookie',
			'Expect',
			'Accept',
			'Pragma',
			'Date',
			'A-Im',
			'TE'
		];
		
		$search		= 
		\array_diff_key( 
			$headers, \array_reverse( $skip ) 
		);
		
		$sig		= '';
		foreach ( $search as $k => $v ) {
			$sig .= $v[0];
		}
		
		$this->sig	= $sig;
		return $sig;
	}
	
	/**
	 *  Get the first non-empty server parameter value if set
	 *  
	 *  @param array	$headers	Server parameters
	 *  @param array	$terms		Searching terms
	 *  @param bool		$case		Search only in lowercase if true
	 *  @return mixed
	 */
	public static function serverParamWhite( 
		array		$headers, 
		array		$terms, 
		bool		$case		= false 
	) {
		$found	= null;
		
		foreach ( $headers as $h ) {
			// Skip unset or empty keys
			if ( empty( $_SERVER[$h] ) ) {
				continue;
			}
			
			// Search in lowercase
			if ( $case ) {
				$lc	= 
				\array_map( '\PubCabin\Util::lowercase', $terms );
				
				$sh	= Util::lowercase( $_SERVER[$h] );
				$found	= \in_array( $sh, $lc ) ? $sh : '';
			} else {
				$found	= 
				\in_array( $_SERVER[$h], $terms ) ? 
					$_SERVER[$h] : '';
			}
			break;
		}
		return $found;
	}
	
	/**
	 *  Forwarded HTTP header chain from load balancer
	 *  
	 *  @return array
	 */
	public function getForwarded() : array {
		if ( isset( $this->forwarded ) ) {
			return $this->forwarded;
		}
		
		$fwd	= [];
		$terms	= 
			$_SERVER['HTTP_FORWARDED'] ??
			$_SERVER['FORWARDED'] ?? 
			$_SERVER['HTTP_X_FORWARDED'] ?? '';
		
		// No headers forwarded
		if ( empty( $terms ) ) {
			return [];
		}
		
		$pt	= explode( ';', $terms );
		
		// Gather forwarded values
		foreach ( $pt as $p ) {
			// Break into comma delimited list, if any
			$chain = Util::trimmedList( $p );
			if ( empty( $chain ) ) {
				continue;
			}
			
			foreach ( $chain as $c ) {
				$k = explode( '=', $c );
				// Skip empty or odd values
				if ( count( $k ) != 2 ) {
					continue;
				}
				
				// Existing key?
				if ( isset( $fwd[$k[0]] ) ) {
					// Existing array? Append
					if ( \is_array( $fwd[$k[0]] ) ) {
						$fwd[$k[0]][] = $k[1];
					
					// Multiple values? 
					// Convert to array and then append new
					} else {
						$tmp		= $fwd[$k[0]];
						$fwd[$k[0]]	= [];
						$fwd[$k[0]][]	= $tmp;
						$fwd[$k[0]][]	= $k[1];
	 				}
				// Fresh value
				} else {
					$fwd[$k[0]] = $k[1];
				}
			}
		}
		$this->forwarded = $fwd;
		return $fwd;
	}
	
	/**
	 *  Get the current IP address connection chain including given proxies
	 *  
	 *  @return array
	 */
	public function getProxyChain() : array {
		static $chain;
		
		if ( isset( $chain ) ) {
			return $chain;
		}
		
		$chain = 
		Util::trimmedList( 
			$_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
			$_SERVER['HTTP_CLIENT_IP'] ?? 
			$_SERVER['REMOTE_ADDR'] ?? '' 
		);
		
		return $chain;
	}
	
	/**
	 *  Get IP address (best guess)
	 *  
	 *  @return string
	 */
	public function getIP() : string {
		if ( isset( $this->ip ) ) {
			return $this->ip;
		}
		
		$fwd	= $this->getForwarded();
		
		$ip	= '';
		// Get IP from reverse proxy, if set
		if ( \array_key_exists( 'for', $fwd ) ) {
			$ip = 
			\is_array( $fwd['for'] ) ? 
				\array_shift( $fwd['for'] ) : 
				( string ) $fwd['for'];
		
		// Get from sent headers
		} else {
			$raw = $this->getProxyChain();
			if ( empty( $raw ) ) {
				$ip = '';
				return '';
			}
			
			$ip	= \array_shift( $raw );
		}
		
		$this->raw_ip	= $ip;
		
		$skip		= 
		$this->config->setting( 'skip_local', 'int' );
		
		$va		=
		( $skip ) ?
		\filter_var( $ip, \FILTER_VALIDATE_IP ) : 
		\filter_var(
			$ip, 
			\FILTER_VALIDATE_IP, 
			\FILTER_FLAG_NO_PRIV_RANGE | 
			\FILTER_FLAG_NO_RES_RANGE
		);
		
		$ip		= ( false === $va ) ? '' : $ip;
		$this->ip	= $ip;
		return $ip;
	}
	
	/**
	 *  Guess if current request is secure
	 */
	public function isSecure() : bool {
		if ( isset( $this->secure ) ) {
			return $this->secure;
		}
		
		$ssl	= $_SERVER['HTTPS'] ?? '0';
		$frd	= 
			$_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 
			$_SERVER['HTTP_X_FORWARDED_PROTOCOL'] ?? 
			$_SERVER['HTTP_X_URL_SCHEME'] ?? 'http';
		
		if ( 
			0 === \strcasecmp( $ssl, 'on' )		|| 
			0 === \strcasecmp( $ssl, '1' )		|| 
			0 === \strcasecmp( $frd, 'https' )
		) {
			$this->secure = true;
		} else {
			$this->secure = 
			( 443 == 
				( int ) ( 
					$_SERVER['SERVER_PORT'] ?? 80 
				) 
			);
		}
		
		return $this->secure;
	}
	
	
	/**
	 *  Standard request parameter helpers
	 */
	
	/**
	 *  Browser User Agent
	 *  
	 *  @return string
	 */
	public function getUA() : string {
		if ( isset( $this->user_agent ) ) {
			return $this->user_agent;
		}
		$this->user_agent	= 
			trim( $_SERVER['HTTP_USER_AGENT'] ?? '' );
		return $this->user_agent;
	}
	
	/**
	 *  Get full request URI
	 *  
	 *  @return string
	 */
	public function getURI() : string {
		if ( isset( $this->uri ) ) {
			return $this->uri;
		}
		$this->uri	= $_SERVER['REQUEST_URI'] ?? '';
		return $this->uri;
	}
	
	/**
	 *  Current querystring, if present
	 *  
	 *  @return string
	 */
	public public function getQS() : string {
		if ( isset( $this->querystring ) ) {
			return $this->querystring;
		}
		$this->querystring	= 
			$_SERVER['QUERY_STRING'] ?? '';
		return $this->querystring;
	}
	
	/**
	 *  Current client request method
	 *  
	 *  @return string
	 */
	public function getMethod() : string {
		if ( isset( $this->request_method ) ) {
			return $this->request_method;
		}
		$this->request_method = 
		\strtolower( trim( $_SERVER['REQUEST_METHOD'] ?? '' ) );
		return $this->request_method;
	}
	
	/**
	 *  Visitor's preferred languages based on Accept-Language header
	 *  
	 *  @return array
	 */
	public function getLang() : array {
		if ( isset( $this->language ) ) {
			return $this->language;
		}
		
		$found	= [];
		$lang	= 
		Util::bland( 
			$this->httpheaders( true )['accept-language'] ?? '' 
		);
		
		// No header?
		if ( empty( $lang ) ) {
			return [];
		}
		
		// Find languages by locale and priority
		\preg_match_all( 
			'/(?P<lang>[^-;,\s]{2,8})' . 
			'(?:-(?P<locale>[^;,\s]{2,8}))?' . 
			'(?:;q=(?P<weight>[0-9]{1}(?:\.[0-9]{1})))?/is',
			$lang,
			$matches
		);
		$matches =
		\array_filter( 
			$matches, 
			function( $k ) {
				return !\is_numeric( $k );
			}, \ARRAY_FILTER_USE_KEY 
		);
		
		if ( empty( $matches ) ) {
			return [];
		}
		
		// Re-arrange
		$c	= count( $matches );
		for ( $i = 0; $i < $c; $i++ ) {
			
			foreach ( $matches as $k => $v ) {
				if ( !isset( $found[$i] ) ) {
					$found[$i] = [];
				}
				
				switch ( $k ) {
					case 'lang':
						$found[$i][$k] = 
						empty( $v[$i] ) ? '*' : $v[$i];
						break;
						
					case 'locale':
						$found[$i][$k] = 
						empty( $v[$i] ) ? '' : $v[$i];
						break;
						
					case 'weight':
						// Lower global or empty language priority
						if ( 
							empty( $matches['lang'][$i] ) ||
							0 == \strcmp( $found[$i]['lang'], '*' )
						) {
							$found[$i][$k] = 
							( float ) ( empty( $v[$i] ) ? 0 : $v[$i] );
						} else {
							$found[$i][$k] = 
							( float ) ( empty( $v[$i] ) ? 1 : $v[$i] );						
						}
						break;
				
					default:
						// Anything else, send as-is
						$found[$i][$k] = 
						empty( $v[$i] ) ? '' : $v[$i];
				}
			}
		}
		
		// Sorting columns
		$weight = \array_column( $found, 'weight' );
		$locale	= \arary_column( $found, 'locale' );
		
		// Sort by weight priority, followed by locale
		$this->language = 
		\array_multisort( 
			$weight, \SORT_DESC, 
			$locale, \SORT_ASC, 
			$found
		) ? $found : [];
		
		return $this->language;
	}
}
