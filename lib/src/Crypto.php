<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Crypto.php
 *  @brief	Encryption and hashing
 */
namespace PubCabin;

class Crypto {
	
	/**
	 *  Hash password to storage safe format
	 *  
	 *  @param string	$password	Raw password as entered
	 *  @return string
	 */
	public static function hashPassword( string $password ) : string {
		return 
		\base64_encode(
			\password_hash(
				\base64_encode(
					\hash( 'sha384', $password, true )
				),
				\PASSWORD_DEFAULT
			)
		);
	}
	
	/**
	 *  Check hashed password
	 *  
	 *  @param string	$password	Password exactly as entered
	 *  @param string	$stored		Hashed password in database
	 */
	public static function verifyPassword( 
		string		$password, 
		string		$stored 
	) : bool {
		if ( empty( $stored ) ) {
			return false;
		}
		
		$stored = \base64_decode( $stored, true );
		if ( false === $stored ) {
			return false;
		}
		
		return 
		\password_verify(
			\base64_encode( 
				\hash( 'sha384', $password, true )
			),
			$stored
		);
	}
	
	/**
	 *  Check if user password needs rehashing
	 *  
	 *  @param string	$stored		Already hashed, stored password
	 *  @return bool
	 */
	public static function passNeedsRehash( 
		string		$stored 
	) : bool {
		$stored = \base64_decode( $stored, true );
		if ( false === $stored ) {
			return false;
		}
		
		return 
		\password_needs_rehash( $stored, \PASSWORD_DEFAULT );
	}
	
	/**
	 *  Create secret (unshared) key and public key for encryption
	 *  
	 *  @return array
	 */
	public static function keypair() : array {
		$pair = \sodium_crypto_box_keypair();
		return [
			'public' 	=> 
				\sodium_crypto_box_publickey( $pair ),
			'secret'	=>
				\sodium_crypto_box_secretkey( $pair )
		];
	}
	
	/**
	 *  Encrypt a message given a public key (unsigned)
	 *  
	 *  @param string	$pubkey		Recipient's public key
	 *  @param string	$message	Content to be encrypted
	 */
	public static function encrypt( 
		string	$pubkey, 
		string	$message 
	) : string {
		return 
		\base64_encode( 
			\sodium_crypto_box_seal( $message, $pubkey )
		);
	}
	
	/**
	 *  Decrypt an (unsigned) message given the private key
	 *  
	 *  @param string	$message	Content to be decrypted
	 */
	public static function decrypt( 
		string	$secret, 
		string	$message
	) : string {
		$message	= \base64_decode( $message, true );
		if ( false === $message ) {
			return '';
		}
		
		$out		= 
		\sodium_crypto_box_seal_open( $message, $pubkey );
		
		return ( false === $out ) ? '' : $out;
	}
	
	/**
	 *  Encrypt a message given a secret, public key, and the content
	 *  
	 *  @param string	$secret		Senders secret key
	 *  @param string	$pubkey		Recipient's public key
	 *  @param string	$message	Content to be encrypted
	 *  @return array
	 */
	public static function signedEncrypt( 
		string	$secret, 
		string	$pubkey,
		string	$message
	) : array {
		$nonce	= 
		\random_bytes( \SODIUM_CRYPTO_BOX_NONCEBYTES );
		
		$key	= 
		\sodium_crypto_box_keypair_from_secretkey_and_publickey( 
			$secret, 
			$pubkey 
		);
		
		return [ 
			'nonce'		=> $nonce, 
			'message'	=> 
			\base64_encode( 
				\sodium_crypto_box( 
					$message, $nonce, $key 
				) 
			)
		];
	}
	
	/**
	 *  Decrypt a message given the original keys and transmission nonce
	 *  
	 *  @param string	$nonce		Single-use random text in message
	 *  @param string	$secret		Recipient's secret key
	 *  @param string	$pubkey		Sender's public key
	 *  @param string	$message	Content to be decrypted
	 *  @return string
	 */
	public static function signedDecrypt(
		string	$nonce,
		string	$secret, 
		string	$pubkey,
		string	$message
	) : string {
		$message	= \base64_decode( $message, true );
		if ( false === $message ) {
			return '';
		}
		
		$key		= 
		\sodium_crypto_box_keypair_from_secretkey_and_publickey(
			$secret,
			$pubkey,
		);
		
		$out 		= 
		\sodium_crypto_box_open(
			$message,
			$nonce,
			$key
		);
		
		return ( false === $out ) ? '' : $out;
	}
}
