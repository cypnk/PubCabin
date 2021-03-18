<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Response.php
 *  @brief	Server response back to visitor
 */

namespace PubCabin;

class Response extends Message {
	
	public function __construct( \PubCabin\Config $config ) {
		parent::__construct( $config );
		
		// Default headers
		$this->headers[] = 'X-XSS-Protection: 1; mode=block';
		$this->headers[] = 'X-Content-Type-Options: nosniff';
		$this->headers[] = 'X-Frame-Options: SAMEORIGIN'
		$this->headers[] = 
			'Referrer-Policy: ' .
			'no-referrer, strict-origin-when-cross-origin';
	}
	
	/**
	 *  Set expires header
	 */
	public function setCacheExp( int $ttl ) {
		$this->headers[] = 'Cache-Control: max-age=' . $ttl;
		$this->headers[] = 
			'Expires: ' . 
			\gmdate( 'D, d M Y H:i:s', time() + $ttl ) . 
			' GMT';
	}
	
	/**
	 *  Generate ETag from file path
	 */
	public function genEtag( string $path ) {
		static $tags		= [];
		
		if ( isset( $tags[$path] ) ) {
			return $tags[$path];
		}
		
		$tags[$path]		= [];
		
		// Find file size header
		$tags[$path]['fsize']	= \filesize( $path );
		
		// Send empty on failure
		if ( false === $tags[$path]['fsize'] ) {
			$tags[$path]['fmod'] = 0;
			$tags[$path]['etag'] = '';
			return $tags;
		}
		
		// Similar to Nginx ETag algo: 
		// Lowercase hex of last modified date and filesize
		$tags[$path]['fmod']	= \filemtime( $path );
		if ( false !== $tags[$path]['fmod'] ) {
			$tags[$path]['etag']	= 
			\sprintf( '%x-%x', 
				$tags[$path]['fmod'], 
				$tags[$path]['fsize']
			);
		} else {
			$tags[$path]['etag'] = '';
		}
		
		return $tags[$path];
	}
	
	/**
	 *  Check If-None-Match header against given ETag
	 *  
	 *  @return true if header not set or if ETag doesn't match
	 */
	public function ifModified( $etag ) : bool {
		$mod = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
	
		if ( empty( $mod ) ) {
			return true;
		}
		
		return ( 0 !== \strcmp( $etag, $mod ) );
	}
	
	/**
	 *  Send file-specific headers
	 *  
	 *  @param string	$dsp		Content disposition
	 *  @param string	$fname		Download file name
	 *  @param bool		$cache		Set file cache
	 */
	public function sendFileHeaders( string $dsp, string $fname, bool $cache ) {
		// Setup file parameters
		$this->headers[] = 
		"Content-Disposition: {$dsp}; filename=\"{$fname}\"";
	
		// If cached, set long expiration
		if ( $cache ) {
			$this->headers[] = 
			'Cache-Control:public, max-age=31536000';
			return;
		}
		
		// Uncached
		$this->headers[] = 'Cache-Control: must-revalidate';
		$this->headers[] = 'Expires: 0';
		$this->headers[] = 'Pragma: no-cache';
	}
	
	/**
	 *  Remove previously set headers, output
	 */
	public function scrubOutput() {
		// Scrub output buffer
		\ob_clean();
		\header_remove( 'Pragma' );
		
		// This is best done in php.ini : expose_php = Off
		\header( 'X-Powered-By: nil', true );
		\header_remove( 'X-Powered-By' );
	}
	
	/**
	 *  Safety headers
	 *  
	 *  @param string	$chk	Content checksum
	 *  @param bool		$send	CSP Send Content Security Policy header
	 *  @param bool		$type	Send content type (html)
	 */
	public function preamble(
		string	$chk		= '', 
		bool	$send_csp	= true,
		bool	$send_type	= true
	) {
		if ( $send_type ) {
			$this->headers[] = 
			'Content-Type: text/html; charset=utf-8';
		}
		
		// If sending CSP and content checksum isn't used
		if ( $send_csp ) {
			$cjp = Util::decode( DEFAULT_JCSP );
			$csp = 'Content-Security-Policy: ';
			
			// Approved frame ancestors ( for embedding media )
			$frl = 
			$this->config->setting( 'frame_whitelist' );
			$raw = \is_array( $frl ) ? 
					\array_map( 'cleanUrl', $frl ) : 
					FileUtil::lineSettings( 
						$frl, -1, 
						'\PubCabin\Util::cleanUrl' 
					);
			
			$raw = \array_unique( \array_filter( $raw ) );
			$frm = \implode( ' ', $raw );
			
			foreach ( $cjp as $k => $v ) {
				$csp .= 
				( 0 == \strcmp( $k, 'frame-ancestors' ) ) ? 
					"$k $v $frm;" : "$k $v;";
			}
			$this->headers[] =  \rtrim( $csp, ';' );
			
		// Content checksum used
		} elseif ( !empty( $chk ) ) {
			$this->headers[] = 
			"Content-Security-Policy: default-src " .
				"'self' '{$chk}'"
		}
	}
	
	/**
	 *  Send list of supported HTTP request methods
	 */
	public function getAllowedMethods( bool $arr = false ) {
		$ap	= 
		$this->config->setting( 'allow_post', 'int' );
		if ( $arr ) {
			return $ap ?  
			[ 'get', 'post', 'head', 'options' ] : 
			[ 'get', 'head', 'options' ];
		}
		
		return $ap ? 
		'GET, POST, HEAD, OPTIONS' : 'GET, HEAD, OPTIONS';
	}
	
	/**
	 *  Send list of allowed methods in "Allow:" header
	 */
	public function sendAllowHeader() {
		$this->headers[] = 
			'Allow: ' . $this->getAllowedMethods();
	}
	
	/**
	 *  Create HTTP status code message
	 *  
	 *  @param int		$code		HTTP Status code
	 */
	public function httpCode( int $code ) {
		$green	= [
			200, 201, 202, 204, 205, 206, 
			300, 301, 302, 303, 304,
			400, 401, 403, 404, 405, 406, 407, 409, 410, 411, 412, 
			413, 414, 415,
			500, 501
		];
		
		if ( \in_array( $code, $green ) ) {
			\http_response_code( $code );
			
			// Some codes need additional headers
			switch( $code ) {
				case 405:
					$this->sendAllowHeader();
					break;
			}
			
			return;
		}
		
		$prot = $this->getProtocol();
		
		// Special cases
		switch( $code ) {
			case 425:
				$this->headers[] =
				"$prot $code Too Early";
				return;
			
			case 429:
				$this->headers[] =
				"$prot $code Too Many Requests";
				return;
				
			case 431:
				$this->headers[] = 
				"$prot $code " . 
				'Request Header Fields Too Large';
				return;
			
			case 503:
				$this->headers[] = 
				"$prot $code Service Unavailable";
				return;
		}
		
		// Log unkown status type
		errors( 'Unknown status code "' . $code . '"' );
		die();
	}
}
