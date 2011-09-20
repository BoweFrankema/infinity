<?php
/**
 * PIE API: file system helper class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage utils
 * @since 1.0
 */

Pie_Easy_Loader::load( 'utils/file_cache' );

/**
 * Make the File System Easy
 *
 * @package PIE
 * @subpackage utils
 * @uses Pie_Easy_Files_Exception
 */
final class Pie_Easy_Files extends Pie_Easy_Base
{
	/**
	 * File cache instance
	 * 
	 * @var Pie_Easy_File_Cache
	 */
	private static $fcache;

	/**
	 * Split a path at forward '/' OR backward '\' slashes
	 *
	 * @param string $path
	 * @return array
	 */
	public static function path_split ( $path )
	{
		return preg_split( '/\/|\\\/', $path, null, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Pop and return the last file off the end of a path
	 *
	 * @see path_split
	 * @param string $path
	 * @return string
	 */
	public static function path_pop ( $path )
	{
		return array_pop( self::path_split( $path ) );
	}

	/**
	 * Build a filesytem path from a list of file names
	 *
	 * @internal Using this for building URL paths is a bad idea.
	 * @param array $file_names,... One array or an unlimited number of file names
	 * @param boolean $relative [Optional] Set to true if the path is relative (no leading slash)
	 * @return string
	 */
	public static function path_build ()
	{
		// get all args
		$args = func_get_args();

		// prefix is null by default
		$prefix = null;

		// relative is false by default
		$relative = false;

		// check for relative flag
		if ( end( $args ) === true ) {
			$relative = true;
			array_pop($args);
		}
		reset( $args );

		// if two or more args, we got some file names
		if ( is_array($args[0]) ) {
			$file_names = $args[0];
		} else {
			$file_names = $args;
		}

		// need prefix?
		if ( $relative === false ) {
			$prefix = DIRECTORY_SEPARATOR;
		}

		// final files array
		$file_names_clean = array();

		// clean them up
		foreach( $file_names as $file_name ) {
			// handle string
			if ( is_string( $file_name ) ) {
				// split into parts
				$sub_paths = self::path_split( $file_name );
			} else {
				// should already be an array
				$sub_paths = $file_name;
			}
			// add to final list
			foreach( $sub_paths as $sub_path ) {
				// handle relative traversal in absolute paths
				if ( !$relative ) {
					if ( $sub_path == '.' ) {
						// single dot, skip
						continue;
					} elseif ( $sub_path == '..' ) {
						// two dots, remove last dir "up"
						array_pop( $file_names_clean );
						continue;
					}
				}
				// push file name onto final array
				array_push( $file_names_clean, $sub_path );
			}
		}

		return $prefix . implode( DIRECTORY_SEPARATOR, $file_names_clean );
	}

	/**
	 * Normalize a filesystem path
	 *
	 * This will remove useless slashes and convert invalid directory separators
	 * with the correct separator for the local system
	 *
	 * @param string $path
	 * @return string
	 */
	public static function path_normalize( $path )
	{
		// is this a relative path?
		if ( $path{0} == '/' || $path{0} == '\\' ) {
			$relative = false;
		} else {
			$relative = true;
		}

		// return the path
		return self::path_build( self::path_split( $path ), $relative );
	}

	/**
	 * Returns fstat instance for a file from the cache
	 *
	 * @param string $filename
	 * @return Pie_Easy_File
	 */
	public static function cache( $filename )
	{
		return self::file_cache()->get($filename);
	}
	
	/**
	 * Return file cache instance
	 *
	 * @return Pie_Easy_File_Cache
	 */
	private static function file_cache()
	{
		if ( !self::$fcache instanceof Pie_Easy_File_Cache ) {
			self::$fcache = new Pie_Easy_File_Cache();
		}
		
		return self::$fcache;
	}

	/**
	 * List all files in a directory filtered by a regular expression
	 *
	 * @param string $dir Absolute path to directory
	 * @param string $regex Valid PCRE expression
	 * @param boolean $absolute Set to true to return abolute path to file
	 * @return array
	 */
	public function list_filtered( $dir, $regex, $absolute = false )
	{
		// does the directory exist?
		if ( is_dir( $dir ) ) {
			// try to open the dir
			$dh = opendir( $dir );
			// check that handle is valid
			if ( $dh ) {
				// list of files to return
				$return_files = array();
				// loop through and add only files that match regex to list
				while (($file = readdir($dh)) !== false) {
					// check regex
					if ( preg_match($regex, $file) ) {
						// build file path
						$file_path = ( $absolute ) ? $dir . DIRECTORY_SEPARATOR . $file : $file;
						// push onto return array
						$return_files[$file] = $file_path;
					}
				}
				// destroy handle
				closedir($dh);
				// sort the files (by key)
				ksort( $return_files );
				// done
				return $return_files;
			} else {
				throw new Pie_Easy_Files_Exception( 'Unable to open the directory: ' . $dir );
			}
		} else {
			throw new Pie_Easy_Files_Exception( 'The directory does not exist: ' . $dir );
		}
	}

	/**
	 * Return URL to a theme directory
	 *
	 * @param string $theme
	 * @return string
	 */
	static public function theme_dir_url( $theme )
	{
		return get_theme_root_uri( $theme ) . '/' . $theme;
	}

	/**
	 * Return URL to a theme file
	 *
	 * @param string $theme
	 * @param string|array $file_names,...
	 */
	static public function theme_file_url( $theme )
	{
		// get all args except the first
		$file_names = func_get_args();
		array_shift($file_names);

		// were file names passed as an array?
		if ( is_array( current( $file_names ) ) ) {
			$file_names = current( $file_names );
		}

		return self::theme_dir_url( $theme ) . '/' . implode( '/', $file_names );
	}

	/**
	 * Return path to a theme file relative to theme root
	 *
	 * @param string $file_path
	 */
	static public function theme_file_to_rel( $file_path )
	{
		// convert path to be realtive to themes root
		return str_replace( get_theme_root() . DIRECTORY_SEPARATOR, '', $file_path );
	}

	/**
	 * Return URL to a theme file given an absolute file system path
	 *
	 * @param string $file_path
	 */
	static public function theme_file_to_url( $file_path )
	{
		// convert path to be relative to themes root
		$relative_path = self::theme_file_to_rel( $file_path );
		// split it up
		$file_names = self::path_split( $relative_path );
		// theme is first arg, beautiful!
		$theme = array_shift( $file_names );
		// return as url
		return self::theme_file_url( $theme, $file_names );
	}

	/**
	 * Create a class name from a file name
	 * @param string $file_name
	 * @return type
	 */
	static public function file_to_class( $file_name, $prefix = null )
	{
		// split at common delimeters
		$parts = preg_split( '/[_.\/\\-]/', $file_name );

		// if last part is php, kill it
		if ( end( $parts ) == 'php' ) {
			array_pop( $parts );
		}

		// upper case the first char of every part
		foreach ( $parts as &$ext_part ) {
			$ext_part = ucfirst( $ext_part );
		}

		// add prefix if necessary
		if ( strlen( $prefix ) ) {
			array_unshift( $parts, $prefix );
		}

		// join them with underscores
		return implode( '_', $parts );
	}
}

/**
 * Pie Easy File Exception
 *
 * @package PIE
 * @subpackage utils
 */
final class Pie_Easy_Files_Exception extends Exception {}

?>
