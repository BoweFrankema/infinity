<?php
/**
 * ICE API: option extensions, "on" checkbox class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package ICE-extensions
 * @subpackage options
 * @since 1.0
 */

ICE_Loader::load_ext( 'options/checkbox' );

/**
 * On checkbox option
 *
 * @package ICE-extensions
 * @subpackage options
 */
class ICE_Ext_Option_Toggle_On
	extends ICE_Ext_Option_Checkbox
		implements ICE_Option_Auto_Field
{
	/**
	 */
	public function load_field_options()
	{
		return array(
			true => __( 'On', infinity_text_domain )
		);
	}
}

?>
