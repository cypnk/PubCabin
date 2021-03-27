<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Module/Files/Captcha.php
 *  @brief	Bot test image generator (requires GD)
 */
namespace PubCabin\Modules\Forms;

class Captcha {
	
	// Captcha font file name in the module asset folder
	const CAPTCHA_FONT	= 'VeraMono.ttf';
	
	// Captcha font size
	const CAPTCHA_FSIZE	= 30;
	
	// Captcha image height
	const CAPTCHA_HEIGHT	= 35;
	
	// Captcha string length
	const CAPTCHA_LENGTH	= 5;
	
	// Captcha image mime type (currently, jpg, png, or bmp)
	const CAPTCHA_MIME	= 'image/png';
	
	// Captcha image file name (extension should match mime)
	const CAPTCHA_NAME	= 'captcha.png';
	
	// Default hashing algorithm
	const CAPTCHA_HASH	= 'tiger160,4';
	
	// RGB Background color
	const CAPTCHA_BG	= '255, 255, 255';
	
	// Text color ranges. Maybe pastels
	const CAPTCHA_COLORS	= '0, 150, 10, 150, 10, 150';
	
	// Line colors in a comfortable range
	const CAPTCHA_LINES	= '150, 200, 150, 200, 150, 200';
	
	/**
	 *  Generate captcha image
	 *  
	 *  @param string		$txt	Random captcha string
	 *  @param \PubCabin\Config	$config	Main configuration
	 */
	public function genCaptcha( 
		string			$txt, 
		\PubCabin\Config	$config 
	) {
		
		// Prepare response
		$rsp	= new \PubCabin\Response( $config );
		$assets	= $this->moduleBase( 'assets' );
		
		// Font file (not served to visitor directly)
		$ffile	= $config->setting( 'captcha_font' ) ?? 
				self::CAPTCHA_FONT;
		
		$font	= 
		\PubCabin\Util::slashPath( $assets, true ) . $ffile; 
		
		// Image meta info
		$mime	= $config->setting( 'captcha_mime' ) ?? 
				self::CAPTCHA_MIME;
		$name	= $config->setting( 'captcha_name' ) ?? 
				self::CAPTCHA_NAME;
		
		// Check for GD
		if ( \PubCabin\Util::missing( 'imagecreatetruecolor' ) ) {
			errors( 'CAPTCHA: Check GD function availability' );
			$rsp->sendGenFilePrep( $mime, $name, 404, false );
			$rsp->sendFileFinish( $name, true );
		}
		
		// Check if font file is accessible
		if ( !\is_readable( $font ) || !\is_file( $font ) ) {
			errors( 'CAPTCHA: Font file not found' );
			$rsp->sendGenFilePrep( $mime, $name, 404, false );
			$rsp->sendFileFinish( $name, true );
		}
		
		// Height of image
		$sizey	= 
		\PubCabin\Util::intRange( 
			$config->setting( 'captcha_height', 'int' ) ?? 
				self::CAPTCHA_HEIGHT,
			10, 100
		);
		
		// Character length
		$cl	= \PubCabin\Util::strsize( $txt );
		
		// Font size
		$fs	= 
		\PubCabin\Util::intRange( 
			$config->setting( 'captcha_fsize', 'int' ) ?? 
				self::CAPTCHA_FSIZE,
			10, 72
		);
		
		// Expand the image with the number of characters
		$sizex	= floor( ( $cl * $fs / 1.5 ) + 10 );
		
		// Some initial padding
		$w	= floor( ( $sizex / $cl ) - $fs / 2 );
		
		$img	= \imagecreatetruecolor( $sizex, $sizey );
		
		// RGB Background
		$color	= 
		\PubCabin\Util::trimmedList(
			$config->setting( 'captcha_bg', 'string' ) ?? 
			self::CAPTCHA_BG
		);
		$color	= 
		\PubCabin\Util::spanIntRange( $colors, 3, 0, 255 );
		
		$bg	= 
		\imagecolorallocate( $img, $color[0], $color[1], $color[2] );
		
		\imagefilledrectangle( $img, 0, 0, $sizex, $sizey, $bg );
		
		// Line thickness
		\imagesetthickness( $img, 3 );
		
		// Line color ranges
		$color	= 
		\PubCabin\Util::trimmedList(
			$config->setting( 'captcha_lines', 'string' ) ?? 
			self::CAPTCHA_COLORS
		);
		$color	= 
		\PubCabin\Util::spanIntRange( $colors, 6, 0, 255 );
		
		// Random lines
		for ( $i = 0; $i < ( $sizex * $sizey ) / 250; $i++ ) {
			// Set line color
			$lc = 
			\imagecolorallocate( 
				$img, 
				\rand( $color[0], $color[1] ), 
				\rand( $color[2], $color[3] ), 
				\rand( $color[4], $color[5] ) 
			);
			
			\imageline( 
				$img, 
				\mt_rand( 0, $sizex ), 
				\mt_rand( 0, $sizey ), 
				\mt_rand( 0, $sizex ), 
				\mt_rand( 0, $sizey ), 
				$lc
			);
		}
		
		// Reset thickness
		\imagesetthickness( $img, 1 );
		
		// Text colors range
		$color	= 
		\PubCabin\Util::trimmedList(
			$config->setting( 'captcha_colors', 'string' ) ?? 
			self::CAPTCHA_COLORS
		);
		$color	= 
		\PubCabin\Util::spanIntRange( $colors, 6, 0, 255 );
		
		
		// Insert the text (with random colors and placement)
		for ( $i = $cl; $i >= 0; $i--) {
			
			$l	= 
			\PubCabin\Util::truncate( $txt, $i, 1 );
			
			$tc	= 
			\imagecolorallocate( 
				$img, 
				\rand( $color[0], $color[1] ), 
				\rand( $color[2], $color[3] ), 
				\rand( $color[4], $color[5] ) 
			);
			
			\imagettftext( 
				$img, 
				$fs, 
				\rand( -10, 10 ), 
				$w + ( $i * \rand( 18, 19 ) ), 
				\rand( 30, 40 ), 
				$tc, 
				$font, 
				$l 
			);
		}
		
		// Prepare headers
		$rsp->sendGenFilePrep( $mime, $name, 200, false );
		
		// Send generated image and end execution
		switch( $mime ) {
			case 'image/png':
				\imagepng( $img );
				break;
				
			case 'image/jpg':
			case 'image/jpeg':
				\imagejpeg( $img );
				break;
				
			case 'image/bmp':
				\imagebmp( $img );
				break;
		}
		
		\imagedestroy( $img );
		$rsp->sendFileFinish( $name, true );
	}
	
}

