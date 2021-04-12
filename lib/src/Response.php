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
	 *  Flush and end all output buffers
	 *  
	 *  @param bool		$done	End execution if true
	 */
	public function flushBuffers( bool $done = false ) {
		while ( \ob_get_level() > 0 ) {
			\ob_end_flush();
		}
		
		if ( $done ) {
			die();
		}
	}
	
	/**
	 *  Erase and end all output buffers
	 *  
	 *  @param bool		$done	End execution if true
	 */
	public function eraseBuffers( bool $done = false ) {
		while ( false !== \ob_get_length() ) {
			\ob_end_clean();
		}
		
		if ( $done ) {
			die();
		}
	}
	
	/**
	 *  Send all currently set headers
	 */
	public function sendHeaders() {
		$headers = \array_unique( $this->headers );
		foreach ( $headers as $h ) {
			\header( $h, true );
		}
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
	 *  Prepare to send a file instead of an HTTP response
	 *  
	 *  @param string	$path		File path to send
	 *  @param int		$code		HTTP Status code
	 *  @param bool		$verify		Verify mime content type
	 */
	public function sendFilePrep( 
		string		$path, 
		int		$code		= 200, 
		bool		$verify		= true 
	) {
		$this->scrubOutput();
		$this->httpCode( $code );
		
		// Set content type if mime is found
		if ( $verify ) {
			$mime			= 
			\FileUtil::detectMime( $path );
			$this->headers[]	= "Content-Type: {$mime}";
		}
		$this->headers[] = 
		"Content-Security-Policy: default-src 'self'";
		
		// Setup content security
		$this->preamble( '', false, false );
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
	 *  Finish file sending functionality
	 *  
	 *  @param string	$path		File path to send
	 */
	public function sendFileFinish( $path, bool $nosend = false ) {
		// Prepare content length and etag headers
		if ( !$nosend ) {
			$tags	= $this->genEtag( $path );
			$fsize	= $tags['fsize'];
			$etag	= $tags['etag'];
			if ( false !== $tags['fsize'] ) {
				$this->headers[] = "Content-Length: {$fsize}";
				if ( !empty( $etag ) ) {
					$this->headers[] = "ETag: {$etag}";
				}	
				
				if ( $this->config->setting( 'show_modified', 'int' ) ) {
					$fmod	= $tags['fmod'];
					if ( !empty( $fmod ) ) {
						$this->headers[]  =
						'Last-Modified: ', 
						\PubCabin\Util::dateRfcFile( 
							$fmod 
						);
					}
				}
			}
		}
		
		$this->scrubOutput();
		$this->sendHeaders();
		
		if ( $this->ifModified( $etag ) && !$nosend ) {
			\readfile( $path );
		}
		
		// Flush and end buffers
		$this->flushBuffers( true );
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
	 *  Prepare to send back a dynamically generated file (E.G. Captcha)
	 *  This function is a plugin helper
	 *  
	 *  @param string	$mime		Generated file's mime content-type
	 *  @param string	$fname		File name
	 *  @param int		$code		HTTP Status code
	 *  @param bool		$cache		Cache generated file if true
	 */
	public function sendGenFilePrep( 
		string		$mime, 
		string		$fname, 
		int		$code		= 200, 
		bool		$cache		= false 
	) {
		$this->sendFilePrep( $fname, $code, false );
		$this->headers[] = "Content-Type: {$mime}";
		$this->sendFileHeaders( 'inline', $fname, $cache );
	}
	
	/**
	 *  Send a physical file if it exists
	 *  
	 *  @param string	$path		Physical path relative to script
	 *  @param bool		$down		Prompt download if true
	 *  @param int		$code		HTTP Status code
	 */
	public function sendFile(
		string		$path,
		bool		$down		= false, 
		bool		$cache		= true,
		int		$code		= 200
	) : bool {
		// No file found
		if ( !\is_readable( $path ) || !\is_file( $path ) ) {
			return false;
		}
		
		// Client save path
		$fname	= \basename( $path );
		
		// Show inline or prompt download
		$dsp	= $down ? 'attachment' : 'inline';
		
		// Prepare to send file
		$this->sendFilePrep( $path, $code );
		$this->sendFileHeaders( $dsp, $fname, $cache );
		
		// Finish sending file
		$this->sendFileFinish( $path );
		return true;
	}
	
	/**
	 *  Print headers, content, and end execution
	 *  
	 *  @param int		$code		HTTP Status code
	 *  @param string	$content	Page data to send to client
	 *  @param bool		$cache		Cache page data if true
	 */
	public function send(
		int		$code		= 200,
		string		$content	= '',
		bool		$cache		= false,
		bool		$feed		= false
	) {
		$this->scrubOutput();
		$this->httpCode( $code );
		
		if ( $feed ) {
			$this->headers[] = 
			'Content-Type: application/xml; charset=utf-8';
			
			$this->headers[] = 
			'Content-Disposition: inline';
			$this->preamble( '', true, false );
		} else {
			$this->preamble();
		}
		
		// Also save to cache?
		if ( $cache ) {
			$ex	= 
			$this->config->setting( 'cache_ttl', 'int' );
			
			$this->setCacheExp( $ex );
			// TODO: Schedule 'saveCache' with full URI
		}
		
		// TODO: Trigger 'contentsend' hook and schedule 'ob_end_flush'
		
		// Check gzip prerequisites
		if ( $code != 304 && \extension_loaded( 'zlib' ) ) {
			\ob_start( 'ob_gzhandler' );
		}
		
		$this->sendHeaders();
		
		// Send to visitor
		echo $content;
		
		// Flush and end buffers
		$this->flushBuffers( true );
	}
	
	/**
	 *  Send file with ETag data
	 *  
	 *  @param string	$path	File path after confirming it exists
	 */
	public function sendWithEtag( $path ) : bool {
		$tags	= $this->genEtag( $path );
		
		// Couldn't generate ETag?
		// Either filesize() or filemtime() failed
		if ( empty( $tags['etag'] ) ) {
			return false;
		}
		
		// Create return code based on returned ETag
		$code	= $this->ifModified( $tags['etag'] )? 200 : 304;
		
		// Send on success
		return $this->sendFile( $path, false, true, $code );
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
			$cjp = 
			$this->config->setting( 'default_jcsp', 'json' );
			$csp = 'Content-Security-Policy: ';
			
			// Approved frame ancestors ( for embedding media )
			$frl = 
			$this->config->setting( 'frame_whitelist' );
			$raw = \is_array( $frl ) ? 
					\array_map( 
						'\PubCabin\Util::cleanUrl', $frl 
					) : 
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
		
		\http_response_code( 500 );
		$this->flushBuffers( true );
	}
	
	/**
	 *  Redirect with status code
	 *  
	 *  @param int		$code	HTTP Status code
	 *  @param string	$path	Full URL to from current domain
	 */
	public function redirect(
		int		$code		= 200,
		string		$path		= ''
	) {
		$url	= \parse_url( $path );
		$host	= $url['host'] ?? '';
		
		// Arbitrary redirect attempt?
		if ( 0 !== \strcasecmp( $host, $_SERVER['SERVER_NAME'] ) ) {
			errors( 'Invalid URL: ' . $path );
			$this->eraseBuffers( true );
		}
		
		// Get get current path
		$path	= $host . 
		\PubCabin\Util::slashPath( $url['path'] ?? '' );
		
		// Directory traversal
		$path	= \preg_replace( '/\.{2,}', '.', $path );
		
		if ( false === \headers_sent() ) {
			\header( 'Location: ' . $path, true, $code );
		}
		
		$this->eraseBuffers( true );
	}
}

