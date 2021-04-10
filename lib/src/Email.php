<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Email.php
 *  @brief	SMTP Mail message
 */
namespace PubCabin;

class Email extends Message {
	
	const BOUNDARY_PREFIX	= '--Multipart_';
	
	const ID_FORMAT		= '<{id}-{hash}@{host}>';
	
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
	 *  Email message header setter
	 *  
	 *  @param string	$sep		Email boundary separator
	 *  @param string	$to		Sender address(es)
	 *  @param string	$from		Recipient address(es)
	 *  @param string	$subject	Formatted email subject
	 */
	protected function setHeaders( 
		string		$sep, 
		string		$to, 
		string		$from, 
		string		$subject 
	) {
		// App name ( X-Mailer )
		$mailer			= 
		\quoted_printable_encode( $this->config->setting( 
			'app_name', 'string' 
		) );
		
		// Message ID
		$id			= 
		\strtr( self::ID_FORMAT, [
			'{id}'		=> Util::genSeqId(), 
			'{hash}'	=> 
				\hash( 'sha1', $from . $to . $subject ),
			'{host}'	=> 
				$this->config->setting( 'basename', 'string' ) ?? 
				'pubcabin.local';
		] );
		
		// Headers
		$this->headers		= [ 
			'Date: ' . Util::dateRfc(),
			'From: ' . $from,
			'To: ' . $to, 
			'Message-ID: ' . $id,
			'MIME-Version: 1.0',
			'Content-Type: multipart/mixed; boundary="' . $sep . '"',
			'X-Mailer: =?UTF-8?Q?' .  $mailer . '?='
		];
	}
	
	/**
	 *  Email sending message helper
	 *  
	 *  @param array	$rec		List of recipients (must match whitelist)
	 *  @param string	$subject	Message heading
	 *  @param string	$msg		Mail body
	 *  @param array	$files		Source locations for attatched files
	 *  @param string	$mode		Email format mode
	 *  @return bool
	 */
	public function send(
		array		$rec, 
		string		$subject, 
		string		$message, 
		array		$attach		= [],
		string		$mode		= 'text'
	) : bool {
		
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
		
		$to	= \implode( ',', $names );
		
		// Content separator
		$sep			= 
		self::BOUNDARY_PREFIX . Util::genAlphaNum();
		
		// Prepend/append separator, breaks, and content headers
		switch( $mode ) {
			case 'html':
				// Encode for HTML
				$msg =  $br. $br . $sep . $br . 
				'Content-Type: text/html; charset="UTF-8"' . $br . 
				'Content-Transfer-Encoding: base64' . $br . 
				\base64_encode( $msg ) . $br;
				break;
			
			default:
				// Strip tags from plain text
				$msg =  $br. $br . $sep . $br . 
				'Content-Type: text/plain; charset="UTF-8"' . $br . 
				'Content-Transfer-Encoding: quoted-printable' . $br . 
				\quoted_printable_encode( \trim( \strip_tags( $msg ) ) ) . $br;
		}
		
		// Add any attachments
		foreach ( $attach as $f ) {
			$msg .= $this->attachFile( $f, $sep );
		}
		
		// End body
		$msg .= "{$sep}--";
		
		// Subject
		$subj			= 
		'=?UTF-8?Q?' . 
		Util::unifySpaces( \quoted_printable_encode( 
			\trim( \strip_tags( $subject ) )
		) ) . '?=';
		
		// Set email headers
		$this->setHeaders( $sep, $to, $mfr, $subj );
		
		$ok			= 
		mail( 
			$to, $subj, $msg, 
			\array_map( '\\PubCabin\\Util::unifySpaces', $this->headers ) 
		);
	
		if ( $ok ) {
			return true;
		}
		
		errors( \error_get_last()['message'] ?? 'Email: Error sending message' );
		return false;
	}
}


