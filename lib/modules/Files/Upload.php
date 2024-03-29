<?php declare( strict_types = 1 );
/**
 *  @file	/lib/modules/Files/Upload.php
 *  @brief	User file uploading helper
 */
namespace PubCabin\Modules\Files;

class Upload {
	
	/**
	 *  Recieve chunk size for put files
	 */
	const PUT_CHUNK		= 8192;
	
	/**
	 *  File module holder
	 */
	protected $module;
	
	public function __construct( 
		\PubCabin\Modules\Files\Module	$_module
	) {
		$this->module = $_module;
	}
	
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
		$err	= [];
		$hooks	= $this->module->getModule( 'Hooks' );
		
		foreach ( $files as $name ) {
			foreach ( $name as $file ) {
				// If errors were found, skip
				if ( $file['error'] != \UPLOAD_ERR_OK ) {
					$err[] = 'Error handling upload: ' . $name;
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
		
		if ( !empty( $err ) ) {
			$hooks->event( [ 'uploaderror', [
				'method'	=> 'post',
				'store'		=> $store,
				'path'		=> $path, 
				'messages'	=> $err
			] ];
		}
		
		// Once uploaded and moved, format info
		$processed	= [];
		foreach( $saved as $k => $v ) {
			$processed[] = static::processFile( $v );	
		}
		return $processed;
	}
	
	/**
	 *  Handle PUT method file upload
	 *  
	 *  @param string	$path	Uploading destination
	 *  @return array
	 */
	public function saveStream( string $path ) : array {
		$src	= '';
		$store	= 
		\PubCabin\Util::slashPath( 
			$this->getHostDirectory( 'files' ), true 
		);
		
		$hooks	= $this->module->getModule( 'Hooks' );
		
		try {
			// Temp storage
			$tmp	= \tmpnam( $store, 'upload' );
			if ( false === $tmp ) {
				$hooks->event( [ 'uploaderror', [
					'method'	=> 'put',
					'store'		=> $store,
					'path'		=> $path,
					'messages'	=> 
					[ 'Unable to create temp file in ' . $store ]
				] ];
				return [];
			}
			
			$wr	= \fopen( $tmp, 'w' );
			if ( false === $wr ) {
				unlink( $tmp );
				$hooks->event( [ 'uploaderror', [
					'method'	=> 'put',
					'store'		=> $store,
					'path'		=> $path, 
					'messages'	=> 
					[ 'Unable to open temp file ' . $tmp ]
				] ];
				
				return [];
			}
			
			$stream	= \fopen( 'php://input', 'r' );
			if ( false === $stream ) {
				\fclose( $wr );
				unlink( $tmp );
				unset( $stream );
				
				$hooks->event( [ 'uploaderror', [
					'method'	=> 'put',
					'store'		=> $store,
					'path'		=> $path, 
					'messages'	=> 
					[ 'Cannot open upload stream php://input' ]
				] ];
				return [];
			}
			
			\stream_set_chunk_size( $stream, self::PUT_CHUNK );
			$ss = \stream_copy_to_stream( $stream, $wr );
			
			// Cleanup
			\fclose( $stream );
			\fclose( $wr );
			
			$fs = \filesize( $tmp );
			
			// Compare file size to total written bytes
			if ( 
				false === $ss || 
				false === $fs || 
				$ss != $fs 
			) {
				$hooks->event( [ 'uploaderror', [
					'method'	=> 'put',
					'store'		=> $store,
					'path'		=> $path, 
					'messages'	=> 
					[ 'Corrupted or empty data in ' . $tmp ]
				] ];
				unlink( $tmp );
				return [];
			}
			
			// Exract file path from destination
			$name	= static::filterUpName( \basename( $path ) );
			$src	= static::dupRename( $store . $name );
			
			if ( !\rename( $tmp, $src ) ) {
				$hooks->event( [ 'uploaderror', [
					'method'	=> 'put',
					'store'		=> $store,
					'path'		=> $path, 
					'messages'	=> 
					[ 'Cannot move temp file ' . $tmp ]
				] ];
				
				unlink( $tmp );
				return [];
			}
			
		} catch( \Exception $e ) {
			$hooks->event( [ 'uploaderror', [
				'method'	=> 'put',
				'store'		=> $store,
				'path'		=> $path, 
				[ 'messages'	=> $e->getMessage() ]
			] ];
			return [];
		}
		
		return [ static::processFile( $src ) ];
	}
}


