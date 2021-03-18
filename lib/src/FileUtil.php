<?php declare( strict_types = 1 );
/**
 *  @file	/lib/src/FileUtil.php
 *  @brief	File loading and saving helper
 */
final class FileUtil {
	
	/**
	 *  Loaded file contents as strings
	 *  @var array
	 */
	private static $loaded	= [];
	
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
		
		// Filter file extension
		$ext	= 
		\preg_replace( 
			'/[[:space:]]+/', 
			Util::bland( Util::title( $ext ), true ), '' 
		);
		
		// Extension mode
		$prefix = $fx == 1 ? \rtrim( $ext, '.' ) . '.' : '';
		$suffix	= $fx == 0 ? '.' . \ltrim( $ext, '.' ) : '';
		
		// Backup file name inferred from full file path
		$name	= 
		Util::slashPath( \dirname( $file ), true ) . $prefix . 
			\gmdate( 'Ymd\THis' ) . '.' . 
			\basename( $file ) . $suffix;
		
		return $copy ? 
			\copy( $file, $name ) : \rename( $file, $name );
	}

	/**
	 *  Load file contents and check for any server-side code		
	 */
	public function loadFile( 
		string		$name, 
		array		&$errors = [] 
	) : string {
		// Check if already loaded
		if ( isset( static::$loaded[$name] ) ) {
			return static::$loaded[$name];
		}
		
		// Relative path to storage
		$fname	= \STORE . $name;
		if ( !\file_exists( $fname ) ) {
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
		$file = \STORE . $name;
		
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
}

