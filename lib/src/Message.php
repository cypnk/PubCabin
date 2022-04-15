<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Message.php
 *  @brief	Common server/client message type
 */
namespace PubCabin;

class Message {
	
	/**
	 *  Content MIME type
	 *  @var string
	 */
	public $content_type;
	
	/**
	 *  Message headers list
	 *  @var array
	 */
	public $headers;
	
	/**
	 *  Message headers list
	 *  @var array
	 */
	public $lv_headers;
	
	/**
	 *  Message source or destination URI
	 *  @var string
	 */
	protected $uri;
	
	/**
	 *  Message protocol. E.G. HTTP 1.1
	 *  @var string
	 */
	protected $protocol;
	
	/**
	 *  Current message querystring or path attachment
	 *  @var string
	 */
	protected $querystring;
	
	/**
	 *  Core settings and configuration 
	 *  @var \PubCabin\Config
	 */
	protected $config;
	
	/**
	 *  Main event controller
	 *  @var \PubCabin\Controller
	 */
	protected $ctrl;
	
	public function __construct( \PubCabin\Controller $ctrl ) {
		$this->ctrl	= $ctrl;
		$this->config	= $ctrl->getConfig();
	}
	
	/**
	 *  Get or guess current server protocol
	 *  
	 *  @param string	$assume		Default protocol to assume if not given
	 *  @return string
	 */
	public function getProtocol( string $assume = 'HTTP/1.1' ) : string {
		if ( isset( $this->protocol ) ) {
			return $this->protocol;
		}
		$this->protocol = $_SERVER['SERVER_PROTOCOL'] ?? $assume;
		return $this->protocol;
	}
	
	/**
	 *  Helper to append or replace currently set headers
	 *  
	 *  @param array	$_headers	New set of headers
	 */
	public function mergeHeaders( array $_headers ) {
		$this->headers = \array_merge( $this->headers, $_headers );
	}
}


