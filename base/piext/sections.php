<?php
/**
 * Infinity Theme: sections classes file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package Infinity
 * @subpackage sections
 * @since 1.0
 */

Pie_Easy_Loader::load( 'components/sections' );

/**
 * Infinity Theme: sections policy
 *
 * @package Infinity
 * @subpackage sections
 */
class Infinity_Sections_Policy extends Pie_Easy_Sections_Policy
{
	/**
	 * @return Pie_Easy_Sections_Policy
	 */
	static public function instance()
	{
		self::$calling_class = __CLASS__;
		return parent::instance();
	}
	
	/**
	 * Return the name of the implementing API
	 *
	 * @return string
	 */
	final public function get_api_slug()
	{
		return 'infinity_theme';
	}

	/**
	 * @ignore
	 * @return boolean
	 */
	final public function enable_styling()
	{
		return ( is_admin() );
	}

	/**
	 * @ignore
	 * @return boolean
	 */
	final public function enable_scripting()
	{
		return ( is_admin() );
	}

	/**
	 * @return Infinity_Sections_Registry
	 */
	final public function new_registry()
	{
		return new Infinity_Sections_Registry();
	}

	/**
	 * @return Infinity_Exts_Section_Factory
	 */
	final public function new_factory()
	{
		return new Infinity_Exts_Section_Factory();
	}

	/**
	 * @return Infinity_Sections_Renderer
	 */
	final public function new_renderer()
	{
		return new Infinity_Sections_Renderer();
	}

}

/**
 * Infinity Theme: sections registry
 *
 * @package Infinity
 * @subpackage sections
 */
class Infinity_Sections_Registry extends Pie_Easy_Sections_Registry
{
	// nothing custom yet
}

/**
 * Infinity Theme: section factory
 *
 * @package Infinity
 * @subpackage exts
 */
class Infinity_Exts_Section_Factory extends Pie_Easy_Sections_Factory
{
	// nothing custom yet
}

/**
 * Infinity Theme: sections renderer
 *
 * @package Infinity
 * @subpackage sections
 */
class Infinity_Sections_Renderer extends Pie_Easy_Sections_Renderer
{
	/**
	 * Render the section layout around the section's content
	 *
	 * @param string $content The content that should be wrapped in the section layout
	 */
	protected function render_section( $content )
	{ ?>
		<div class="<?php $this->render_classes() ?>">
			<?php print $content ?>
		</div><?php
	}
}

//
// Helpers
//

/**
 * Initialize sections environment
 */
function infinity_sections_init( $theme = null )
{
	// component policies
	$sections_policy = Infinity_Sections_Policy::instance();

	// enable component
	Pie_Easy_Scheme::instance($theme)->enable_component( $sections_policy );

	do_action( 'infinity_sections_init' );
}

/**
 * Initialize sections screen requirements
 */
function infinity_sections_init_screen()
{
	// init ajax OR screen reqs (not both)
	if ( defined( 'DOING_AJAX') ) {
		Infinity_Sections_Policy::instance()->registry()->init_ajax();
		do_action( 'infinity_sections_init_ajax' );
	} else {
		Infinity_Sections_Policy::instance()->registry()->init_screen();
		do_action( 'infinity_sections_init_screen' );
	}
}

?>
