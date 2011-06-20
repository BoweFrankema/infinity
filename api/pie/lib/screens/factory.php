<?php
/**
 * PIE API: screens factory class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage screens
 * @since 1.0
 */

Pie_Easy_Loader::load( 'base/factory' );

/**
 * Make creating screen objects easy
 *
 * @package PIE
 * @subpackage screens
 */
class Pie_Easy_Screens_Factory extends Pie_Easy_Factory
{
	/**
	 * @todo sending section through is a temp hack
	 * @ignore
	 * @return Pie_Easy_Screens_Screen
	 */
	public function create( $ext, $theme, $name, $title = null, $desc = null, $section = null )
	{
		// load it from alternate location
		$class_name = $this->policy()->load_ext( $ext );

		// get one?
		if ( !$class_name ) {
			// nope, load core screen
			Pie_Easy_Loader::load_ext( array('screens', $ext) );
			// determine class name
			$class_name = Pie_Easy_Files::file_to_class( $ext, 'Pie_Easy_Exts_Screen' );
		}

		// update ext map
		if ( $this->ext( $class_name, $ext ) ) {
			// return it
			return new $class_name( $theme, $name, $title, $desc );
		}
	}
}

?>
