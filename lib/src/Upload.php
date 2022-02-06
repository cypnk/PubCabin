<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/Upload.php
 *  @brief	File uploading and processing helper
 */
class Upload {
	
	// Enable generating thumbnails for image types
	public const THUMBNAIL_GEN	= 1;
	
	// Image types to generate thumbnail
	public const THUMBNAIL_TYPES	= 'image/jpeg, image/png, image/gif, image/bmp';
	
	// Default thumbnail size
	public const THUMBNAIL_WIDTH	= 100;
	
	// Prefix added to thumbnail filenames
	public const THUMBNAIL_PREFIX	= 'tn_';
	
	// Configuration settings
	protected $config;
	
	public function __construct( Config $config ) {
		$this->config = $config;
	}
	
	/**
	 *  Create image thumbnails from file path and given mime type
	 *  
	 *  @param string 	$src	Original image path
	 *  @param string	$mime	Image mime type
	 */
	public function createThumbnail( 
		string	$src,
		string	$mime 
	) : string {
		
		// Get size and set proportions
		$imgsize	= \getimagesize( $src );
		if ( false === $imgsize ) {
			return '';
		}
		
		if ( empty( $imgsize[0] ) || empty( $imgsize[1] ) ) {
			return '';
		}
		$width		= $imgsize[0];
		$height		= $imgsize[1];
		
		$t_width	= 
		$this->config->setting( 'thumbnail_width', 'int' ) ?? self::THUMBNAIL_WIDTH;
		
		// Width too small to generate thumbnail
		if ( $t_width > $width ) {
			return '';
		}
		
		$t_height	= ( $t_width / $width ) * $height;
		
		// New thumbnail
		$thumb		= \imagecreatetruecolor( $t_width, $t_height );
		
		// Create new image
		switch( $mime ) {
			case 'image/png':
				// Set transparent background
				\imagesavealpha( $thumb, true );
				$source	= \imagecreatefrompng( $src );
				break;
				
			case 'image/gif':
				$source	= \imagecreatefromgif( $src );
				break;
			
			case 'image/bmp':
				$source	= \imagecreatefrombmp( $src );
				break;
				
			default:
				$source	= \imagecreatefromjpeg( $src );
		}
		
		// Resize to new resources
		\imagecopyresized( $thumb, $source, 0, 0, 0, 0, 
			$t_width, $t_height, $width, $height );
		
		// Thunbnail destination
		$tnp	= 
		$this->config->setting( 'thumbnail_prefix' ) ?? self::THUMBNAIL_PREFIX;
		
		$dest	= 
		FileUtil::dupRename( Util::prefixPath( $src, Util::labelName( $tnp ) ) );
		
		// Create thumbnail at destination
		switch( $mime ) {
			case 'image/png':
				$tn = imagepng( $thumb, $dest, 100 );
				break;
			
			case 'image/gif':
				$tn = imagegif( $thumb, $dest, 100 );
				break;
			
			case 'image/bmp':
				$tn = imagebmp( $thumb, $dest, 100 );
				break;
			
			default:
				$tn = imagejpeg( $thumb, $dest, 100 );
		}
		
		// Did anything go wrong?
		if ( false === $tn ) {
			return '';
		}
		
		// Cleanup
		\imagedestroy( $thumb );
		
		return $dest;
	}
	
	/**
	 *  Filter upload file name into a safe format
	 *  
	 *  @param string	$name		Original raw filename
	 *  @return string
	 */
	public static function filterUpName( ?string $name ) : string {
		if ( empty( $name ) ) {
			return '_';
		}
		
		$name	= \preg_replace('/[^\pL_\-\d\.\s]', ' ' );
		return \preg_replace( '/\s+/', '-', \trim( $name ) );
	}
	
	/**
	 *  Format uploaded file info
	 *  
	 *  @param string	$src	Complete file path
	 *  @param array	$img	Allowed thumbnail image types
	 *  @param bool		$tn	Create a thumbnail for allowed types, if true
	 */
	protected function processUpload( string $src, array $img, bool $tn = false ) {
		$mime	= FileUtil::detectMime( $src );
		
		return [
			'src'		=> $src,
			'mime'		=> $mime,
			'filename'	=> \basename( $src ),
			'filesize'	=> \filesize( $src ),
			'description'	=> '',
			
			// Process thumbnail if needed
			'thumbnail'	=> $tn ? ( 
				\in_array( $mime, $img ) ? 
					$this->createThumbnail( $src, $mime ) : '' 
			) : ''
		];
	}
	
	/** 
	 *  Return uploaded $_FILES array into a more sane format
	 * 
	 *  @return array
	 */
	public function parseUploads() : array {
		$files = [];
		
		foreach ( $_FILES as $name => $file ) {
			if ( \is_array($file['name']) ) {
				foreach ( $file['name'] as $n => $f ) {
					$files[$name][$n] = [];
					
					foreach ( $file as $k => $v ) {
						$files[$name][$n][$k] = 
							$file[$k][$n];
					}
				}
				continue;
			}
			
        		$files[$name][] = $file;
		}
		return $files;
	}
	
	/**
	 * Move uploaded files to the same directory as the post
	 */
	public function saveUploads( 
		string	$path, 
		string	$root 
	) {
		$files	= $this->parseUploads();
		$store	= 
		Util::slashPath( $root, true ) . 
		Util::slashPath( $path, true );
		
		$saved	= [];
		foreach ( $files as $name ) {
			foreach( $name as $file ) {
				// If errors were found, skip
				if ( $file['error'] != \UPLOAD_ERR_OK ) {
					continue;
				}
				
				$tn	= $file['tmp_name'];
				$n	= 
				static::filterUpName( $file['name'] );
				
				// Check for duplicates and rename 
				$up	= FileUtil::dupRename( $store . $n );
				if ( \move_uploaded_file( $tn, $up ) ) {
					$saved[] = $up;
				}
			}
		}
		
		$tn		= 
		$this->config->setting( 'thumbnail_gen', 'bool' ) ?? self::THUMBNAIL_GEN;
		
		$img		= 
		Util::trimmedList( 
			$this->config->setting( 'thumbnail_types' ) ?? 
			self::THUMBNAIL_TYPES 
		);
		
		// Once uploaded and moved, format info
		$processed	= [];
		foreach( $saved as $k => $v ) {
			$processed[] = $this->processUpload( $v, $img, $tn );	
		}
		return $processed;
	}
}
