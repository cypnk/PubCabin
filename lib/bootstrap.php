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
define( 'PUBCABIN_FILES',	PUBCABIN_DATA . 'uploads/' );

// Temporary data directory
define( 'PUBCABIN_CACHE',	PUBCABIN_DATA . 'cache/' );




/**
 *  Caution editing below
 */

// Core class location
define( 'PUBCABIN_BASE',	PUBCABIN_PATH . 'src/' );

// Plugin and extension class location
define( 'PUBCABIN_MODBASE',	PUBCABIN_PATH . 'modules/' );

// Language and translation files
define( 'PUBCABIN_LANG',	PUBCABIN_DATA . 'lang/' );

// Backup folder
define( 'PUBCABIN_BACKUP',	PUBCABIN_DATA . 'backup/' );

// Module created files
define( 'PUBCABIN_MODSTORE',	PUBCABIN_DATA . 'modules/' );

// Error log file
define( 'PUBCABIN_ERRORS',	PUBCABIN_DATA . 'errors.log' );

// Class name prefixes
define( 'PUBCABIN_PREFIX',	'PubCabin\\' );
define( 'PUBCABIN_MODPREFIX',	'PubCabin\\Modules\\' );


spl_autoload_register( function( $class ) {
	static $rpl	= [ '\\' => '/' ];
	static $len;
	static $mlen;
	
	if ( !isset( $len ) ) {
		$len		= \strlen( \PUBCABIN_PREFIX );
		$mlen		= \strlen( \PUBCABIN_MODPREFIX );	
	}
	
	// Core class file
	if ( 0 === \strncmp( \PUBCABIN_PREFIX, $class, $len ) ) {
		$file	= 
		\PUBCABIN_BASE . \strtr( \substr( $class, $len ), $rpl ) . '.php';
	
	// Module file
	} elseif ( 0 === \strncmp( \PUBCABIN_MODPREFIX, $class, $mlen ) ) {
		$file	= 
		\PUBCABIN_MODBASE . \strtr( \substr( $class, $mlen ), $rpl ) . '.php';
		
	} else {
		return;
	}
	
	if ( \is_readable( $file ) ) {
		require $file;
	}
} );


