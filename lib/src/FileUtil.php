<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/FileUtil.php
 *  @brief	File loading and saving helper
 */
namespace PubCabin;

final class FileUtil {
	
	/**
	 *  Loaded file contents as strings
	 *  @var array
	 */
	private static $loaded	= [];
	
	/**
	 *  Loaded folder trees
	 *  @var array
	 */
	private static $folders	= [];
	
	/**
	 *  Loaded file contents as arrays of lines
	 *  @var array
	 */
	private static $texts	= [];
	
	/**
	 *  Line presets for configuration
	 *  @var array
	 */
	private static $presets	= [];
	
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
	 *  Logging safe string
	 */
	public static function logStr(
			$text, 
		int	$len = 255 
	) : string {
		return 
		\PubCabin\Util::truncate( 
			\PubCabin\Util::unifySpaces( 
				( string ) ( $text ?? '' ) 
			), 0, $len 
		);
	}
	
	/**
	 *  Check log file size and rollover, if needed
	 *  
	 *  @param string	$file	Log file name
	 */
	function logRollover( string $file ) {
		// Nothing to rollover
		if ( !\file_exists( $file ) ) {
			return;
		}
		
		$fs	= \filesize( $file );
		// Empty file
		if ( false === $fs ) {
			return;
		}
		
		if ( $fs > 5000000 ) {
			static::backupFile( $file, false, 'old', 0 );
		}
	}
	
	/**
	 *  Build a coherent path from given set of components
	 * 
	 *  @params array	$segs	List of directories and/or file
	 */
	public static function buildPath( array $segs ) {
		if ( empty( $segs ) ) {
			return '';
		}
		
		return 
		\strtr( 
			implode( '/', $segs ), 
			[ '//' => '/', '..' => '' ] 
		);
	}
	
	/**
	 *  Split a block of text into an array of lines
	 *  
	 *  @param string	$text	Raw text to split into lines
	 *  @param int		$lim	Max line limit, defaults to unlimited
	 *  @param bool		$tr	Also trim lines if true
	 *  @return array
	 */
	public static function lines( 
		string		$text, 
		int		$lim = -1, 
		bool		$tr = true 
	) : array {
		return $tr ?
		\preg_split( 
			'/\s*\R\s*/', 
			trim( $text ), 
			$lim, 
			\PREG_SPLIT_NO_EMPTY 
		) : 
		\preg_split( '/\R/', $text, $lim, \PREG_SPLIT_NO_EMPTY );
	}
	
	/**
	 *  Helper to turn items (one per line) into a unique value array
	 *  
	 *  @param string	$text	Lined settings (one per line)
	 *  @param int		$lim	Maximum number of items
	 *  @param string	$filter	Optional filter name to apply
	 *  @return array
	 */
	public static function lineSettings( 
		string		$text, 
		int		$lim, 
		string		$filter = '' 
	) : array {
		$ln = \array_unique( static::lines( $text ) );
		
		$rt = ( ( count( $ln ) > $lim ) && $lim > -1 ) ? 
			\array_slice( $ln, 0, $lim ) : $ln;
		
		return 
		( !empty( $filter ) && \is_callable( $filter ) ) ? 
			\array_map( $filter, $rt ) : $rt;
	}
	
	/**
	 *  Get presets as lined items (one item per line)
	 *  
	 *  @param string	$label		Preset unique identifier
	 *  @param string	$data		String block of items
	 *  @param sint		$lim		Maximum number of lines
	 */ 
	public static function linePresets(
		string		$label,
		string		$data,
		int		$lim		= 2000
	) {
		if ( isset( static::$presets[$label] ) ) {
			return static::$presets[$label];
		}
		
		// Maximum number of items
		static::$presets[$label]	= static::lineSettings( $data, $lim );
		return static::$presets[$label];
	}
	
	/**
	 *  Filter file extension
	 *  
	 *  @param string	$ext		Raw file extension or empty
	 *  @return string
	 */
	public static function filterExt( ?string $ext ) : string {
		return 
		empty( $ext ) ? '' : 
		\preg_replace( 
			'/[[:space:]]+/', 
			\PubCabin\Util::bland( 
				\PubCabin\Util::title( $ext ), true 
			), '' 
		);
	}
	
	/**
	 *  Create a datestamped backup of the given file before moving or copying it
	 *  
	 *  @param string	$file	File name path
	 *  @param bool		$copy	Copy if true, rename if false
	 *  @param string	$ext	Backup file extension (defaults to bkp)
	 *  @param int		$fx	Prepend or append extension
	 *  				1 = Prefix, 0 = Suffix, other = Add nothing
	 *  
	 *  @return bool		True if no action needed or action successful
	 */
	public static function backupFile(
		string		$file,
		bool		$copy, 
		string		$ext	= 'bkp',
		int		$fx	= 0
	) : bool {
		if ( !\file_exists( $file ) ) {
			return true;
		}
		
		$ext	= static::filterExt( $ext );
		
		// Extension mode
		$prefix = $fx == 1 ? \rtrim( $ext, '.' ) . '.' : '';
		$suffix	= $fx == 0 ? '.' . \ltrim( $ext, '.' ) : '';
		
		// Backup file name inferred from full file path
		$name	= 
		\PubCabin\Util::slashPath( \dirname( $file ), true ) . $prefix . 
			\gmdate( 'Ymd\THis' ) . '.' . 
			\basename( $file ) . $suffix;
		
		return $copy ? 
			\copy( $file, $name ) : \rename( $file, $name );
	}

	/**
	 *  Load file contents and check for any server-side code
	 *  
	 *  @param string	$name	File path name
	 *  @param string	$root	Optional root if not from data dir
	 *  @param array	$errors	Any errors during loading
	 */
	public static function loadFile( 
		string		$name, 
		string		$root		= '',
		array		&$errors	= [] 
	) : string {
		// Check if already loaded
		if ( isset( static::$loaded[$root . $name] ) ) {
			return static::$loaded[$root . $name];
		}
		
		$root = empty( $root ) ? 
			\PubCabin\Util::slashPath( \PUBCABIN_DATA, true ) :
			\PubCabin\Util::slashPath( $root, true );
		
		// Relative path to storage
		$fname	= $root . $name;
		if ( empty( static::filterDir( $fname, $root ) ) ) {
			$errors[] = 'Folder check failed: ' . $fname;
			return '';
		}
		
		if ( !\file_exists( $fname ) ) {
			$errors[] = 'File not found: ' . $fname;
			return '';
		}
		
		$ext		= 
		\pathinfo( $fname, \PATHINFO_EXTENSION ) ?? '';
		
		switch( \strtolower( $ext ) ) {
			case 'json':
			case 'config':
				// Clean comments and junk while loading
				$data	= \php_strip_whitespace( $fname );
				break;
				
			default:
				$data = \file_get_contents( $fname );
		}
		
		// Nothing loaded?
		if ( false === $data ) {
			$errors[] = 'No contents in ' . $fname;
			return '';
		}
		
		if ( false !== \strpos( $data, '<?php' ) ) {
			$errors[] = 'Code detected in ' . $fname;
			return '';
		}
		
		static::$loaded[$name] = $data;
		return $data;
	}
	
	/**
	 *  File saving helper with auto backup
	 *  
	 *  @param string	$name		Destination file name
	 *  @param string	$data		File contents
	 *  @param int		$fx		Prefix 'bkp.', suffix '.bkp', or nothing
	 *  @param bool		$append		Append to file instead of replacing it
	 */
	public static function saveFile( 
		string	$name, 
		string	$data, 
		int	$fx		= 0,
		bool	$append		= false
	) : bool {
		$file = \PUBCABIN_DATA . $name;
		
		// Backup failed? Don't overwrite
		if ( !static::backupFile( $file, true, 'bkp', $fx ) ) {
			return false;
		}
		
		if ( $append ) {
			return 
			( false === \file_put_contents( 
				$file, $data, \FILE_APPEND | \LOCK_EX 
			) ) ? false : true;
		}
		
		return 
		( false === \file_put_contents( $file, $data, \LOCK_EX ) ) ? 
			false : true;
	}
	
	/** 
	 *  Return uploaded $_FILES array into a more sane format
	 * 
	 *  @return array
	 */
	public static function parseUploads() : array {
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
	 *  Rename file to prevent overwriting existing ones by 
	 *  appending _i where 'i' is incremented by 1 until no 
	 *  more files with the same name are found
	 *   
	 *  @param string	$up		Unmodified filename
	 *  @return string
	 */
	public static function dupRename( string $up ) {
		$info	= \pathinfo( $up );
		$ext	= static::filterExt( $info['extension'] ?? '' );
		$name	= $info['filename'] ?? '';
		$dir	= $info['dirname'];
		$file	= $up;
		$i	= 0;
		
		while ( \file_exists( $file ) ) {
			$file = \PubCabin\Util::slashPath( $dir, true ) . 
				$name . '_' . $i++ . 
				\rtrim( '.' . $ext, '.' );
		}
		
		return $file;
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
		$mime	= static::adjustMime( $src );
		
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
	 *  Move uploaded files to the same directory as the post
	 *  
	 *  @param string	$path	Full upload destination directory
	 *  @param string	$root	Root destination prefix
	 *  @param array	$err	Any upload processing errors
	 */
	public static function saveUploads( 
		string	$path, 
		string	$root,
		array	&$err
	) : array {
		$files	= static::parseUploads();
		$store	= 
		\PubCabin\Util::slashPath( $root, true ) . 
		\PubCabin\Util::slashPath( $path, true );
		
		$saved	= [];
		$err	= [];
		
		foreach ( $files as $name ) {
			foreach( $name as $file ) {
				// If errors were found, skip
				if ( $file['error'] != \UPLOAD_ERR_OK ) {
					$err[] = 'Error handling upload: ' . $name;
					continue;
				}
				
				$tn	= $file['tmp_name'];
				$n	= 
				static::filterUpName( $file['name'] );
				
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
	
	/**
	 *  Handle PUT method file upload
	 *  
	 *  @param string	$path	Uploading destination
	 *  @param string	$store	Root storage directory
	 *  @param array	$err	Any PUT processing errors
	 *  @return array
	 */
	public function saveStream( 
		string	$path, 
		string	$store,
		array	&$err
	) : array {
		$src	= '';
		$err	= [];
		
		try {
			// Temp storage
			$tmp	= \tmpnam( $store, 'upload' );
			if ( false === $tmp ) {
				$err[] = 'Unable to create temp file in ' . $store;
				
				return [];
			}
			
			$wr	= \fopen( $tmp, 'w' );
			if ( false === $wr ) {
				unlink( $tmp );
				$err[] = 'Unable to open temp file ' . $tmp;
				
				return [];
			}
			
			$stream	= \fopen( 'php://input', 'r' );
			if ( false === $stream ) {
				\fclose( $wr );
				unlink( $tmp );
				unset( $stream );
				$err[] = 'Cannot open upload stream php://input';
				
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
				unlink( $tmp );
				$err[] = 'Corrupted or empty data in ' . $tmp;
				
				return [];
			}
			
			// Exract file path from destination
			$name	= static::filterUpName( \basename( $path ) );
			$src	= static::dupRename( $store . $name );
			
			if ( !\rename( $tmp, $src ) ) {
				unlink( $tmp );
				
				$err[] = 'Cannot move temp file ' . $tmp;
				return [];
			}
			
		} catch( \Exception $e ) {
			$err[] = $e->getMessage();
			
			return [];
		}
		
		return [ static::processFile( $src ) ];
	}
	
	/**
	 *  Get text content as an array of lines
	 *  
	 *  @param mixed	$raw	Post content or file path
	 *  @param bool		$fl	Content is in a file
	 *  @param bool		$skip	Skip empty lines when loading
	 */
	public static function loadText(
				$raw, 
		bool		$fl	= true, 
		bool		$skip	= false 
	) {
		$key		= $raw . ( string ) $fl;
		
		if ( isset( static::$texts[$key] ) ) {
			return static::$texts[$key];
		}
		
		// Get content from files
		if ( $fl ) {
			if ( \file_exists( $raw ) ) {
				$data	= $skip ? 
				\file( $raw, 
					\FILE_IGNORE_NEW_LINES | 
					\FILE_SKIP_EMPTY_LINES 
				) : 
				\file( $raw, \FILE_IGNORE_NEW_LINES );
				
				if ( false === $data ) {
					return [];
				}
			} else {
				return [];
			}
		
		// Or break content into lines
		} else {
			$data	= explode( "\n", $raw );
		}
		
		if ( empty( $data ) ) {
			return [];
		}
		
		// Remove empty lines from beginning of post 
		// (titles etc...)
		while( "" === trim( \current( $data ) ) ) {
			\array_shift( $data );
		}
		
		if ( empty( $data ) ) {
			return [];
		}
		
		// Empty lines from end of post 
		// (tags etc...)
		while( "" === trim( \end( $data ) ) ) {
			\array_pop( $data );
		}
		
		\reset( $data );
		static::$texts[$key]	= $data;
		return $data;
	}
	
	/**
	 *  File mime-type detection helper
	 *  
	 *  @param string	$path	Fixed file path
	 *  @return string
	 */
	public static function detectMime( string $path ) : string {
		$mime = \mime_content_type( $path );
		if ( false === $mime ) {
			return 'application/octet-stream';
		}
		
		// Override text types with special extensions
		// Required on some OSes like OpenBSD
		if ( 0 === \strcasecmp( $mime, 'text/plain' ) ) {
			$ext = \pathinfo( $path, \PATHINFO_EXTENSION ) ?? '';
			
			switch( \strtolower( $ext ) ) {
				case 'css':
					return 'text/css';
					
				case 'js':
					return 'text/javascript';
					
				case 'svg':
					return 'image/svg+xml';
					
				case 'vtt':
					return 'text/vtt';
			}
		}
		
		return \strtolower( $mime );
	}
	
	/**
	 *  Verify if given directory path is a subfolder of root
	 *  
	 *  @param string	$path	Folder path to check
	 *  @param string	$root	Full parent folder path
	 *  @return string Empty if directory traversal or other issue found
	 */
	public static function filterDir( $path, string $root ) {
		if ( \strpos( $path, '..' ) ) {
			return '';
		}
		
		$lp	= \strlen( $root );
		if ( \strlen( $path ) < $lp ) { 
			return ''; 
		}
		$pos	= \strpos( $path, $root );
		if ( false === $pos ) {
			return '';
		}
		$path	= \substr( $path, $pos + $lp );
		return \trim( $path ?? '' );
	}
	
	/**
	 *  Get all files in relative folder path
	 *  
	 *  @param string	$root Search path
	 *  @return array
	 */
	public static function getTree( string $root ) : array {
		// Clean root
		$root	= static::buildPath( explode( '/', $root ) );
		
		if ( isset( static::$folders[$root] ) ) {
			return static::$folders[$root];
		}
		
		if ( !\is_readable( $root ) || !\is_dir( $root ) ) {
			return [];
		}
		
		try {
			$dir		= 
			new \RecursiveDirectoryIterator( 
				$root, 
				\FilesystemIterator::FOLLOW_SYMLINKS | 
				\FilesystemIterator::KEY_AS_FILENAME
			);
			$it		= 
			new \RecursiveIteratorIterator( 
				$root, 
				\RecursiveIteratorIterator::LEAVES_ONLY,
				\RecursiveIteratorIterator::CATCH_GET_CHILD 
			);
			
			$it->rewind();
			
			// Temp array for sorting
			$tmp	= \iterator_to_array( $it, true );
			\rsort( $tmp, \SORT_NATURAL );
			
			static::$folders[$root]	= $tmp;
			return $tmp;
			
		} catch( \Exception $e ) {
			\messages( 'error',
				'Error retrieving files from ' . $pd . ' ' . 
				$e->getMessage() ?? 
					'Directory search exception'
			);
		}
		
		return [];
	}
}

