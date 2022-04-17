<?php declare( strict_types = 1 );
/**
 *  @file	/lib/bootstrap.php
 *  @brief	PubCabin loader and environment constants
 */

// Path to this file's directory
define( 'PUBCABIN_PATH',	\realpath( \dirname( __FILE__ ) ) . '/' );

// Storage directory. Must be writable (chmod -R 0755 on *nix)
define( 'PUBCABIN_DATA',	\realpath( \dirname( __FILE__, 2 ) ) . '/data/' );

// Uploaded and editable file directory
define( 'PUBCABIN_FILES',	\PUBCABIN_DATA . 'uploads/' );

// Temporary data directory
define( 'PUBCABIN_CACHE',	\PUBCABIN_DATA . 'cache/' );



/**
 *  Caution editing below
 */

// Core class location
define( 'PUBCABIN_BASE',		\PUBCABIN_PATH . 'src/' );

// Plugin and extension class location
define( 'PUBCABIN_MODBASE',	\PUBCABIN_PATH . 'modules/' );

// Optional components
define( 'PUBCABIN_OPTIONAL',	\PUBCABIN_PATH . 'opt/' );

// Externally contributed code
define( 'PUBCABIN_CONTRIB',	\PUBCABIN_PATH . 'contrib/' );

// Backup folder
define( 'PUBCABIN_BACKUP',		\PUBCABIN_DATA . 'backup/' );

// Module created files
define( 'PUBCABIN_MODSTORE',	\PUBCABIN_DATA . 'modules/' );

// Outgoing mail spool
define( 'PUBCABIN_OUTBOX',		\PUBCABIN_DATA . 'outbox/' );

// Error log file
define( 'PUBCABIN_ERRORS',		\PUBCABIN_DATA . 'errors.log' );

// Notification log file
define( 'PUBCABIN_NOTICES',	\PUBCABIN_DATA . 'notices.log' );


/**
 *  Environment preparation
 */
\date_default_timezone_set( 'UTC' );
\ignore_user_abort( true );
\ob_end_clean();

/**
 *  Isolated message holder
 *  
 *  @param string	$type		Message type, determines storage location
 *  @param string	$message	Log content body
 *  @param bool		$ret		Optional, returns stored log if true
 */
function messages( string $type, string $message, bool $ret = false ) {
	static $log	= [];
	
	if ( $ret && $message ) {
		return $log;
	}
	
	if ( !isset( $log[$type] ) ) {
		$log[$type] = [];	
	}
	
	// Clean message to file safe format
	$log[$type][] = 
	\preg_replace( 
		'/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F[\x{fdd0}-\x{fdef}\p{Cs}\p{Cf}\p{Cn}]/u', 
		'', 
		$message 
	);
}

/**
 *  Notice
 */
function notice( string $message, bool $ret = false ) {
	if ( $ret ) {
		return messages( '', '', true )['notice'] ?? [];
	}
	
	messages( 'notice', $message );
}

/**
 *  Write messages to given error file
 */
function logToFile( string $msg, string $dest ) {
	\error_log( 
		\gmdate( 'D, d M Y H:i:s T', time() ) . "\n" . 
			$msg . "\n\n\n\n", 
		3, 
		$dest
	);
}

/**
 *  Environment check
 */
function baseEnv() : bool {
	$req	= [
		'mb_strlen'		=> 'mbstring',
		'mime_content_type'	=> 'fileinfo',
		'tidy_repair_string'	=> 'tidy',
		'normalizer_normalize'	=> 'intl',
		'libxml_clear_errors'	=> 'libxml',
		'iconv'			=> 'iconv',
		'imagecreatetruecolor'	=> 'GD',
		'sodium_crypto_box'	=> 'sodium'
	];
	
	$miss	= [];
	foreach ( $req as $f => $name ) {
		if ( !\function_exists( $f ) ) {
			$miss[] = $name;
		}
	}
	
	if ( !defined( 'PDO::ATTR_DEFAULT_FETCH_MODE' ) ) {
		$miss[] = 'pdo-sqlite';
	}
	
	if ( empty( $miss ) ) {
		return true;
	}
	
	messages(
		'error', 
		'The following needs to be installed or enabled: ' . 
			\implode( ', ', $miss ) 
	);
	return false;
}

/**
 *  Internal error logger
 */
\register_shutdown_function( function() {
	
	if ( !\is_readable( \PUBCABIN_ERRORS ) ) {
		\touch( \PUBCABIN_ERRORS );
	}
	
	if ( !\is_readable( \PUBCABIN_NOTICES ) ) {
		\touch( \PUBCABIN_NOTICES );
	}
	
	$msgs = messages( '', '', true );
	if ( empty( $msgs ) ) {
		return;
	}
	
	foreach ( $msgs as $k => $v ) {
		switch ( $k ) {
			case 'error':
			case 'errors':
				foreach( $v as $m ) {
					logToFile( $m, \RIVER_ERRORS );
				}
				break;
				
			case 'notice':
			case 'notices':
				foreach( $v as $m ) {
					logToFile( $m, \RIVER_NOTICES );
				}
				break;
				
			case 'mail':
				// TODO: Handle outbox contents
				break;
		}
	}
} );


/**
 *  Class loader
 */
\spl_autoload_register( function( $class ) {
	// Path replacements
	static $rpl	= [ '\\' => '/', '-' => '_' ];
	
	// Class prefix replacements
	static $prefix	= [
		'PubCabin\\Modules\\'	=> \PUBCABIN_MODBASE,
		'PubCabin\\Contrib\\'	=> \PUBCABIN_CONTRIB,
		'PubCabin\\Opt\\'	=> \PUBCABIN_OPTIONAL,
		'PubCabin\\'		=> \PUBCABIN_BASE
	];
	
	foreach ( $prefix as $k => $v ) {
		if ( !\str_starts_with( $class, $k ) ) {
			continue;
		}
		
		// Build file path
		$file	= $v . 
		\strtr( \substr( $class, \strlen( $k ) ), $rpl ) . '.php';
		
		if ( \is_readable( $file ) ) {
			require $file;
		} else {
			messages( 'error', 'Unable to read file: ' . $file );
		}
		break;
	}
} );

/**
 *  Begin
 */
if ( baseEnv() ) {
	$dir = [
		\PUBCABIN_FILES,
		\PUBCABIN_CACHE,
		\PUBCABIN_BACKUP,
		\PUBCABIN_MODSTORE,
		\PUBCABIN_OUTBOX
	];
	
	$er	= [];
	foreach ( $dir as $d ) {
		if ( \is_dir( $d ) ) {
			continue;
		}
		if ( \mkdir( $d, 0755, true ) ) {
			continue;
		}
		$er[] = \basename( $d );
	}
	
	
	if ( !empty( $er ) ) {
		\messages(
			'error', 
			'Required folder(s) could not be created: ' . 
			\implode( ', ', $er )
		);
	}
	
	// Create config, controller and run begin event
	$config	= new \PubCabin\Config();
	$ctrl	= new \PubCabin\Controller( $config );
	$ctrl->run( 'begin', [] );
	
	// Run shutdown event
	$ctrl->run( 'shutdown', [] );
}




