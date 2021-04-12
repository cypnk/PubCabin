<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Files/Upload.php
 *  @brief	User file uploading helper
 */
namespace PubCabin\Modules\Files;

class Upload {
	
	/**
	 *  Valid image types to create a thumbnail
	 *  @var array
	 */
	private static $thumbnail_types	= [
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/bmp',
		'image/webp'
	];
	
	/**
	 *  Filter upload name
	 *  
	 *  @param string	$name	Raw filename from upload
	 *  @return string
	 */
	public static function filterUpName( string $name ) : string {
		if ( empty( $name ) ) {
			return '_';
		}
		
		$name	= \preg_replace('/[^\pL_\-\d\.\s]', ' ' );
		return \preg_replace( '/\s+/', '-', trim( $name ) );
	}
	
	/**
	 *  Rename if a file by that name already exists in destination
	 *   
	 *  @param string	$up	Upload destination path
	 *  @return string
	 */
	public static function dupRename( string $up ) : string {
		$info	= \pathinfo( $up );
		$ext	= $info['extension'];
		$name	= $info['filename'];
		$dir	= $info['dirname'];
		$fpath	= $up;
		$i	= 0;
		
		while ( \file_exists( $fpath ) ) {
			$fpath = 
			\PubCabin\Util::slashPath( $dir, true ) . 
				$name . '_' . $i++ . '.' . $ext;
		}
		
		return $fpath;
	}
		
	/**
	 *  Given a compelete file path, prefix a term to the filename and 
	 *  return a unique file name path
	 *  
	 *  @param string	$path		Base file path
	 *  @param string	$prefix		Special file prefix added to path
	 */
	public static function prefixPath( string $path, string $prefix ) {
		$tn	= 
		\PubCabin\Util::slashPath( \dirname( $path ), true ) . 
			$prefix . \basename( $path );
		
		// Avoid duplicates
		return static::dupRename( $tn );
	}
	
	/**
	 *  Create image thumbnails from file path and given mime type
	 *  
	 *  @param string	$src	File source location
	 *  @param string	$mime	Image mime type
	 */
	public static function createThumbnail( 
		string	$src,
		string	$mime 
	) : string {
		static $hasgd;
		
		if ( !isset( $hasgd ) ) {
			// Check for GD
			if ( \PubCabin\Util::missing( 'imagecreatetruecolor' ) ) {
				$hasgd = false;
				errors( 
					'Upload thumbnail: Check GD function availability' 
				);
				return '';
			}
			$hasgd = true;
		}
		
		if ( !$hasgd ) {
			return '';
		}
		
		// Get size and set proportions
		list( $width, $height ) = \getimagesize( $src );
		$t_width	= 100;
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
			
			case 'image/webp':
				$source	= \imagecreatefromwebp( $src );
				break;
				
			default:
				$source	= \imagecreatefromjpeg( $src );
		}
		
		// Resize to new resources
		\imagecopyresized( $thumb, $source, 0, 0, 0, 0, 
			$t_width, $t_height, $width, $height );
		
		// Thunbnail destination
		$dest	= static::prefixPath( $src, 'tn_' );
		
		// Create thumbnail at destination
		switch( $mime ) {
			case 'image/png':
				$tn = \imagepng( $thumb, $dest, 100 );
				break;
			
			case 'image/gif':
				$tn = \imagegif( $thumb, $dest, 100 );
				break;
			
			case 'image/bmp':
				$tn = \imagebmp( $thumb, $dest, 100 );
				break;
			
			case 'image/webp':
				$tn = \imagewebp( $thumb, $dest, 100 );
				break;
			
			default:
				$tn = \imagejpeg( $thumb, $dest, 100 );
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
	 *  Format uploaded file info for storage or database metadata
	 *  
	 *  @param string	$src	File original location
	 *  @return array
	 */
	public static function processFile( string $src, ) : array {
		$mime	= \PubCabin\FileUtil::adjustMime( $src );
		
		return [
			'src'		=> $src,
			'mime'		=> $mime,
			'filename'	=> \basename( $src ),
			'filesize'	=> \filesize( $src ),
			'description'	=> '',
			
			// Process thumbnail if needed
			'thumbnail'	=>
				\in_array( $mime, static::$thumbnail_types ) ? 
				static::createThumbnail( $src, $mime ) : ''
		];
	}
	
	/**
	 *  Parse uploads into sensible format
	 *  
	 *  @return array
	 */
	public static function parseUploads() : array {
		$files = [];
		
		foreach( $_FILES as $name => $file ) {
			if ( \is_array($file['name']) ) {
				foreach ( $file['name'] as $n => $f ) {
					$files[$name][$n] = array();
					
					foreach( $file as $k => $v ) {
						$files[$name][$n][$k] = 
							$file[$k][$n];
					}
				}
			} else {
	        		$files[$name][] = $file;
			}
		}
        	return $files;
	}
		
	/**
	 *  Save uploaded files to a base directory path, generate thumbnails, 
	 *  if necessary and return info
	 *   
	 *  @param string	$path	Base uploading path
	 *  @return array
	 */
	public function saveUploads( string $path ) : array {
		// Format uploads
		
		// TODO add max file number and size limit
		$files	= static::parseUploads();
		$store	= 
		\PubCabin\Util::slashPath( 
			$this->getHostDirectory( 'files' ), true 
		) . 
		\PubCabin\Util::slashPath( $path, true );
		
		
		$saved	= [];
		
		foreach ( $files as $name ) {
			foreach ( $name as $file ) {
				// If errors were found, skip
				if ( $file['error'] != \UPLOAD_ERR_OK ) {
					errors( 'Error handling upload: ' . $name );
					continue;
				}
				$tn	= $file['tmp_name'];
				$n 	= static::filterUpName( $file['name'] );
				
				// Check for duplicates and rename 
				$up	= static::dupRename( $store . $n );
				if ( \move_uploaded_file( $tn, $up ) ) {
					$saved[] = $up;
				}
			}
		}
		
		// Once uploaded and moved, format info
		$processed	= [];
		foreach( $saved as $k => $v ) {
			$processed[] = static::processFile( $v );	
		}
		return $processed;
	}
	
	public function saveStream() : array {
		// TODO handle PUT based file uploading
		return [];
	}
}


