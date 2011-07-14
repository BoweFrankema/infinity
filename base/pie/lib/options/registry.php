<?php
/**
 * PIE API: options registry
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage options
 * @since 1.0
 */

Pie_Easy_Loader::load( 'base/registry', 'options/factory', 'utils/ajax' );

/**
 * Make keeping track of options easy
 *
 * @package PIE
 * @subpackage options
 */
abstract class Pie_Easy_Options_Registry extends Pie_Easy_Registry
{
	/**
	 * Enqueue required scripts
	 */
	public function init_scripts()
	{
		// call parent
		parent::init_scripts();

		// jQuery UI is always needed
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-button' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script( 'jquery-ui-tabs' );

		// call localize script *LAST*
		$this->localize_script();
	}

	/**
	 * Template method to allow localization of scripts
	 */
	protected function localize_script()
	{
		// override this to apply special localizations that apply to your implementation
	}

	/**
	 * Return sibling options as an array
	 *
	 * @param Pie_Easy_Options_Option $option
	 * @return array
	 */
	public function get_siblings( Pie_Easy_Options_Option $option )
	{
		// options to return
		$options = array();

		// render options that require this one
		foreach ( $this->get_all() as $sibling_option ) {
			if ( $option->name == $sibling_option->required_option ) {
				$options[] = $sibling_option;
			}
		}

		return $options;
	}

	/**
	 * Return registered options as an array
	 *
	 * @param Pie_Easy_Sections_Section $section Limit options to one section by passing a section object
	 * @return array
	 */
	public function get_for_section( Pie_Easy_Sections_Section $section )
	{
		// options to return
		$options = array();

		// loop through and compare names
		foreach ( parent::get_all() as $option ) {

			// do section names match?
			if ( $section->name != $option->section ) {
				continue;
			}

			// add to array
			$options[] = $option;
		}

		// return them
		return $options;
	}

	/**
	 * Return registered options that are valid in a menu
	 *
	 * It does not make sense to list an option in a menu which requires another option,
	 * so this helper method will return an array without them.
	 *
	 * @param Pie_Easy_Sections_Section $section Limit options to one section
	 * @return array
	 */
	public function get_menu_options( Pie_Easy_Sections_Section $section = null )
	{
		// get all options for section
		$options = $this->get_for_section( $section );

		foreach ( $options as $key => $option ) {
			// remove options that require another option
			if ( $option->required_option ) {
				unset( $options[$key] );
			}
			// remove options that aren't supported
			if ( !$option->supported() ) {
				unset( $options[$key] );
			}
		}

		return $options;
	}

	/**
	 * Look through POST vars for options from this registry and try to save them
	 *
	 * @return integer Number of options saved
	 */
	public function process_form()
	{
		if ( empty( $_POST ) ) {
			return false;
		} elseif ( isset( $_POST[Pie_Easy_Options_Renderer::FIELD_MANIFEST] ) ) {

			$manifest = $_POST[Pie_Easy_Options_Renderer::FIELD_MANIFEST];

			// "save only these" option names if param is set
			$save_options =
				!empty( $_POST['option_names'] ) ?
				explode( ',', $_POST['option_names'] ) : null;

			// keep track of how many were updated
			$save_count = 0;

			// loop through manifest options
			foreach ( $manifest as $option_name ) {

				// skip options that don't exist in save options if set
				if ( !empty( $save_options ) && !in_array( $option_name, $save_options ) ) {
					continue;
				}

				// is this option registered?
				if ( $this->has( $option_name ) ) {
					// get the option
					$option = $this->get($option_name);
					// look for option name as POST key
					if ( array_key_exists( $option->name, $_POST ) ) {
						// get new value
						$new_value = $_POST[$option->name];
						// strip slashes from new value?
						if ( is_scalar( $new_value ) ) {
							$new_value = stripslashes( $_POST[$option->name] );
						}
						// update it
						$option->update( $new_value );
					} else {
						// not in POST, delete it
						$option->delete();
					}
					// increment the count
					$save_count++;
				}
			}

			// update custom css
			$this->export_css_file()->update();

			// update dynamic feature css
			Pie_Easy_Policy::features()->registry()->export_css_file()->update();
			
			// done saving
			return $save_count;

		} else {
			throw new Exception( 'No manifest was rendered' );
		}
	}

	/**
	 * Process the form and generate an AJAX response
	 *
	 * @see process_form
	 */
	public function process_form_ajax()
	{
		// process the form
		$save_count = $this->process_form();

		// any options saved successfuly?
		if ( $save_count == 1 ) {
			Pie_Easy_Ajax::responseStd( true, sprintf( __('%d option successfully updated.', pie_easy_text_domain), $save_count ) );
		} elseif ( $save_count > 1 ) {
			Pie_Easy_Ajax::responseStd( true, sprintf( __('%d options successfully updated.', pie_easy_text_domain), $save_count ) );
		} else {
			Pie_Easy_Ajax::responseStd( false, __('An error has occurred. No options were updated.', pie_easy_text_domain) );
		}
	}

	/**
	 * Update custom css from value of all registered components that are a css option
	 *
	 * @return string
	 */
	public function export_css()
	{
		// css from parent
		$css = parent::export_css();

		// loop through and check field type
		foreach ( $this->get_all() as $component ) {
			if ( $component instanceof Pie_Easy_Exts_Option_Css ) {
				$css .= $component->get() . PHP_EOL;
			}
		}

		return $css;
	}

}

?>
