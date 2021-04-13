<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Files/Module.php
 *  @brief	User uploads and file sending handler
 */
namespace PubCabin\Modules\Files;

class Module extends \PubCabin\Modules\Module {
	
	public function dependencies() : array {
		return [ 'Hooks' ];
	}
	
	public function __construct() {
		parent::__construct();
		
		// Check for captcha generated event
		$hooks		= $this->getModule( 'Hooks' );
		$captcha	= $hooks->stringResult( 'captchamade' );
		
		$req		= $this->getRequest();
		
		// Try to send a generated captcha
		if ( !empty( $captcha ) ) {
			$img = new Captcha();
			$img->genCaptcha( 
				$captcha, $this->getConfig() 
			);
			
			// Execution should end here, but end anyway
			die();
		}
		
		// Not an upload or static file ?
		if (
			!$this->upload( $hooks, $req ) || 
			!$this->fileRequest( $hooks, $req )
		) {
			$hooks->event( [ 'nofile', '' ] );
		}
	}
	
	/**
	 *  Get whitelisted file extensions
	 *  
	 *  @param array	$group	File type group text, video etc...
	 *  @return array
	 */
	public function extGroups( string $group = '' ) : array {
		static $ext;
		static $all;
		
		if ( !isset( $ext ) ) {
			// Configured whitelist of extensions
			$ext	= 
			$this->getConfig()->setting( 
				'ext_whitelist', 'json' 
			);
			
			// All extensions
			$all	= \implode( ',', $ext );
			
			// TODO Extend whitelist via hooks
		}
		
		return 
		empty( $group ) ? 
			\array_unique( \PubCabin\Util::trimmedList( $all, true ) ) : 
			\array_unique( \PubCabin\Util::trimmedList( $ext[$group] ?? '', true ) );
	}
	
	/**
	 *  Check if the requested path has a whitelisted extension
	 *  
	 *  @param string	$path		Requested URI
	 *  @param string	$group		Specific type I.E. "images"
	 *  @return bool
	 */
	public function isSafeExt( string $path, string $group = '' ) : bool {
		static $safe	= [];
		static $checked	= [];
		$key		= $group . $path;
		
		if ( isset( $checked[$key] ) ) {
			return $checked[$key];
		}
		
		if ( !isset( $safe[$group] ) ) {
			$safe[$group]	= $this->extGroups( $group );
		}
		
		$ext		= 
		\pathinfo( $path, \PATHINFO_EXTENSION ) ?? '';
		
		$checked[$key] = 
		\in_array( \strtolower( $ext ), $safe[$group] );
		
		return $checked[$key];
	}
	
	/**
	 *  Create a new respons and send file with etag
	 */ 
	public function sendWithEtag( string $path ) {
		$rsp = new \PubCabin\Response();
		$rsp->sendWithEtag( $path )
	}
	
	/**
	 *  Source directory helper for host/domain specific folders
	 *  
	 *  @param string	$path	Prefix directory
	 *  @return string
	 */
	public function getHostDirectory( string $path ) : string {
		static $req;
		if ( !isset( $req ) ) {
			$req = $this->getRequest();
		}
		
		$dr = $path . 
			\PubCabin\Util::slashPath( $req->getHost(), true );
		return \is_dir( $dr ) ? $dr : $path;
	}
	
	/**
	 *  Get the relative post directory or host-specific module file path, or 
	 *  global module file storage path if there's a subfolder with current hostname
	 *  
	 *  @param string	$src	Source type post, module, file
	 *  @return string
	 */
	public function getFileDirectory( string $src = 'none' ) : string {
		static $pd	= [];
		
		if ( isset( $pd[$src] ) ) {
			return $pd[$src];
		}
		
		switch( $src ) {
			// Host prefixed uploads folder
			case 'file':
				$pd[$src] = 
				$this->getHostDirectory( \PUBCABIN_FILES);
				break;
			
			// Host prefixed module storage folder	
			case 'module':
				$pd[$src] = 
				$this->getHostDirectory( \PUBCABIN_MODSTORE );
				break;
				
			// Host prefixed backup folder (future use)
			case 'backup':
				$pd[$src] = 
				$this->getHostDirectory( \PUBCABIN_BACKUP );
				break;
			
			// Global uploads folder
			default:
				$pd[$src] = \PUBCABIN_FILES;
		}
		
		return $pd[$src];
	}
	
	/**
	 *  Get resource from module directory(ies)
	 *  
	 *  @param bool		$dosend	Send the file if found
	 */
	public function sendModuleFile( 
		string		$module, 
		string		$path, 
		bool		$dosend		= false 
	) : bool {
		$loaded	= $this->getLoadedModules();
		
		// Nothing loaded to search?
		if ( empty( $loaded ) ) {
			return false;
		}
		
		// Clip module name from path to prepare for asset searching
		$path	= 
		\PubCabin\Util::truncate( $path, 
			\PubCabin\Util::strsize( $module ) - 1, 
			\PubCabin\Util::strsize( $path ) 
		);
		
		foreach ( $loaded as $p ) {
			// Check if first path fragment is the same as the module name
			if ( 0 !== \strcasecmp( $p, $module ) ) {
				continue;
			}
			
			// Send first occurence of file in the module's public folder
			$fpath = 
			\PubCabin\FileUtil::buildPath( [
				\PUBCABIN_MODBASE, 
				$p,
				static::PUBLIC_DIR, 
				$path
			] );
				
			if ( \file_exists( $fpath ) ) {
				if ( $dosend ) {
					$this->sendWithEtag( $fpath )
				}
				return true;
			}
			
			// File written by module?
			$fpath = 
			\PubCabin\FileUtil::buildPath( [
				$this->getFileDirectory( 'module' ), 
				$p, 
				$path
			] );
			
			if ( \file_exists( $fpath ) ) {
				if ( $dosend ) {
					$this->sendWithEtag( $fpath )
				}
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 *  Upload type request
	 */
	public function fileUpload( 
		\PubCabin\Modules\Hooks\Module	$hooks,
		\PubCabin\Request		$req
	) : bool {
		$verb	= $req->getMethod();
		
		// Only handle post and put
		if ( 
			0 != \strcmp( 'post', $verb ) && 
			0 != \strcmp( 'put', $verb )
		) {
			return false;
		}
		
		// Check auth priority for handling uploads
		$auth	= $hooks->event( [ 'uploadauthset', '' ] );
		
		// Uploading not allowed?
		if ( empty( $auth ) ) {
			return false;
		}
		if ( false === $auth ) {
			return false;
		}
		
		// Get set upload path from events
		$uppath = $hooks->event( [ 'uploadpath', '' ] );
		$uppath = empty( $uppath ) ? '' : $uppath;
		
		$upload	= new Upload( $this );
		$files	= ( 0 == \strcmp( 'post', $verb ) ?
			$upload->saveUploads( $uppath ) : 
			$upload->saveStream( $uppath );
		
		$hooks->event( [ 
			'fileupload', [ 
				'file_path'	=> $fpath,
				'uri'		=> $path,
				'method'	=> $verb,
				'send'		=> $dosend,
				'files'		=> $files
			] 
		] );
		
		// Trigger after upload event
		$hook->event( [ 'fileupload', '' ] );
		return true;
	}
	
	/**
	 *  Check path for file request
	 */	
	public function fileRequest( 
		\PubCabin\Modules\Hooks\Module	$hooks,
		\PubCabin\Request		$req
	) : bool {
		
		$verb	= $req->getMethod();
		$path	= $req->getURI();
		
		// Only handle get
		if ( 
			0 != \strcmp( 'get', $verb ) || 
			!$this->isSafeExt( $path ) 
		) {
			return false;
		}
		
		$config	= $this->getConfig();
		
		// Don't actually send file for head method
		$dosend = 
		( 0 === \strcmp( 'head', $verb ) ) false : true;
		
		// Trim leading slash(es)
		$path	= \preg_replace( '/^\//', '', $path );
		
		// Break path to count folders and search modules
		$segs	= explode( '/', $path );
		
		// Check folder limits
		$climit	= $config->setting( 'folder_limit', 'int' );
		$c	= count( $segs );
		if ( $c > $climit ) {
			return false;
		}
		
		// Rebuild cleaned path
		$path	= \PubCabin\FileUtil::buildPath( $segs );
		
		// Static file path
		$fpath	= 
		$this->getFileDirectory( 'file' ) . $path;
		
		if ( \file_exists( $fpath ) ) {
			$hooks->event( [ 
				'filerequest', [ 
					'file_path'	=> $fpath,
					'uri'		=> $path,
					'method'	=> $verb,
					'send'		=> $dosend
				] 
			] );
			
			// Trigger after file request event
			$hook->event( [ 'filerequest', '' ] );
			if ( $dosend ) {
				$this->sendWithEtag( $fpath )
			}
			return true;
		}
		
		// If there's no prefix, there's no module folder to check 
		if ( $c < 2 ) {
			return false;
		}
		
		// If direct path doesn't exist, try to send it via module asset path
		return 
		$this->sendModuleFile( $segs[0], $path, $dosend );
	}
}


