<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Email.php
 *  @brief	SMTP Mail message
 */
namespace PubCabin;

class Email extends Message {
	
	const BOUNDARY_PREFIX	= '--Multipart_';
	
	/**
	 *  File attachment helper
	 *  
	 *  @param string		$name	Full file source path
	 *  @param string		$sep	Multipart boundary separator
	 *  @return string
	 */
	public function attachFile( string $name, string $sep ) : string {
		
		// Check accessibility
		if ( !\is_readable( $name ) || !\is_file( $name ) ) {
			return '';
		}
		
		// Clean(er) filename to avoid encoding issues
		$fname	= 
		\strtr( 
			Util::title( \basename( $name ) ), 
			[ "\"" => '', '\'' => ''] 
		);
		
		$mime	= FileUtil::adjustMime( \mime_content_type( $name ) );
		
		return
		"{$sep}\r\n" . 
		"Content-Type: {$mime}; name=\"{$fname}\"\r\n" . 
		"Content-Transfer-Encoding: base64\r\n" . 
		"Content-Disposition: attachment; filename=\"{$fname}\"\r\n\r\n" . 
		\chunk_split( \base64_encode( 
			\file_get_contents( $name ) 
		) ) . "\r\n\r\n";
	}
	
	/**
	 *  Email sending message helper
	 *  
	 *  @param array	$rec		List of recipients (must match whitelist)
	 *  @param string	$subject	Message heading
	 *  @param string	$msg		Mail body
	 *  @param array	$files		Source locations for attatched files
	 *  @param bool		$html		Format email as HTML if true
	 *  @return bool
	 */
	public function send(
		array		$rec, 
		string		$subject, 
		string		$message, 
		array		$attach		= [],
		bool		$html		= false
	) : bool {
		static $hheaders = [
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset="UTF-8"',
			'Content-Transfer-Encoding: base64'
		];
	
		static $theaders = [
			'MIME-Version: 1.0',
			'Content-Type: text/plain; charset="UTF-8"',
			'Content-Transfer-Encoding: 8bit'
		];
		
		static $br	= "\r\n";
		
		$msg	= trim( $msg );
		if ( empty( $msg ) ) {
			errors( 'Email: Message cannot be empty.' );
			return false;
		}
		
		$mfr	= Util::cleanEmail( $this->config->setting( 'mail_from' ) );
		if ( empty( $mfr ) ) {
			errors(
				'Email: Sender address is invalid. Check mail_from config setting.' 
			);
			return false;
		}
		
		// Check sender whitelist
		$mrl	= $this->config->setting( 'mail_whitelist' );
		$mwhite	= 
		\is_array( $mrl ) ? 
			\array_map( '\\PubCabin\\Util::cleanEmail', $mrl ) : 
			FileUtil::lineSettings( $mrl, -1, '\\PubCabin\\Util::cleanEmail' );
		
		if ( empty( $mwhite ) ) {
			errors( 'Email: No valid recipients found. Check whitelist.' );
			return false;
		}
		
		// Consistent addresses
		$mwhite	= \array_unique( \array_map( '\\PubCabin\\Util::lowercase', $mwhite ) );
		$rcpt	= \array_unique( \array_map( '\\PubCabin\\Util::lowercase', $rec ) );
		// Check recipient whitelist
		$names	= [];
		foreach( $rcpt as $r ) {
			if ( \in_array( $r, $mwhite, true ) ) {
				$names[] = $r;
			}
		}
		if ( empty( $names ) ) {
			errors( 'Email: No matching recipients in whiltelist.' );
			return false;
		}
		
		
		// Email without attachments
		if ( empty( $attach ) ) {
			
			// HTML or plain text headers
			$this->headers 		= $html ? $hheaders : $theaders;
		
		// Email with attachments and content separators
		} else {
			$sep			= 
			self::BOUNDARY_PREFIX . Util::genAlphaNum();
			
			$this->headers		= [ 
				'MIME-Version: 1.0',
				'Content-Transfer-Encoding: base64', 
				'Content-Type: multipart/mixed; boundary="' . $sep . '"'
			];
			
			// Prepend/append separator, breaks and content headers
			if ( $html ) {
				
				// Encode for HTML
				$msg =  $sep . $br . 
				'Content-Type: text/html; charset="UTF-8"' . $br . 
				'Content-Transfer-Encoding: base64' . $br . 
				\base64_encode( $msg );
				
			} else {
				
				// Strip tags from plain text
				$msg =  $sep . $br . 
				'Content-Type: text/plain; charset="UTF-8"' . $br . 
				'Content-Transfer-Encoding: 8bit' . $br . 
				\strip_tags( $msg )
			}
			
			// Padding
			$msg	.= $br . $br;
			
			// Add attachments
			foreach ( $attach as $f ) {
				$msg .= $this->attachFile( $f, $sep );
			}
			
			// End body
			$msg .= "{$sep}--";
		}
		
		$this->headers[]	= 'From: ' . $mfr;
		$subj			= 
		Util::entities( Util::unifySpaces( $subject ) );
		
		$ok			= 
		mail( 
			\implode( ',', $names ), $subj, $msg, 
			\array_map( '\\PubCabin\\Util::unifySpaces', $this->headers ) 
		);
	
		if ( $ok ) {
			return true;
		}
		
		errors( \error_get_last()['message'] ?? 'Email: Error sending message' );
		return false;
	}
}


