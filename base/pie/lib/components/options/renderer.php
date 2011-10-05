<?php
/**
 * PIE API: options renderer class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE-components
 * @subpackage options
 * @since 1.0
 */

Pie_Easy_Loader::load( 'utils/docs' );

/**
 * Make rendering options easy
 *
 * @package PIE-components
 * @subpackage options
 */
abstract class Pie_Easy_Options_Renderer extends Pie_Easy_Renderer
{
	/**
	 * The field which contains all of the options which were rendered
	 */
	const FIELD_MANIFEST = '__opt_manifest__';

	/**
	 * Renders option container opening and flash message elements
	 *
	 * @param string $block_class Additonal class(es) for the options block container
	 */
	public function render_begin( $block_class = null )
	{
		// begin rendering ?>
		<div id="<?php $this->render_id() ?>" class="pie-easy-options-block <?php $this->render_classes() ?> <?php print $block_class ?>">
			<?php $this->render_manifest() ?>
			<div class="pie-easy-options-mesg">
				<!-- flash messages for this option will render here -->
			</div><?php
	}

	/**
	 * Renders option container closing tag
	 */
	public function render_end()
	{
		// end rendering ?>
		</div><?php
	}

	/**
	 * Render form input label
	 */
	public function render_label()
	{
		// begin rendering label tag ?>
		<label for="<?php $this->render_name() ?>" title="<?php print esc_attr( $this->component()->title ) ?>"><?php print esc_attr( $this->component()->title ) ?></label><?php
	}

	/**
	 * Render a simple form input tag
	 *
	 * @param string $type A valid form input element "type" attribute value (see HTML spec)
	 * @param string $value A value attribute to use instead of the option's stored value
	 * @param string $element_id The input element's id attribute
	 */
	public function render_input( $type, $value = null, $element_id = null )
	{
		// checked is null by default
		$checked = null;

		// multi is false by default
		$multi = false;

		// the stored option value
		$real_value = $this->component()->get();
		
		// overriding value?
		if ( is_null( $value ) ) {
			// use real value
			$input_value = $real_value;
		} else {
			// use passed  value
			$input_value = $value;
			// determine checkiness
			if ( $type == 'radio' ) {
				$checked = ( $value == $real_value );
			} elseif ( $type == 'checkbox' ) {
				$checked = in_array( $input_value, (array) $real_value );
				$multi = true;
			} elseif ( $type == 'hidden-multi' ) {
				$type = 'hidden';
				$multi = true;
			}
		}

		// render the input ?>
		<input type="<?php print esc_attr( $type ) ?>" name="<?php $this->render_name() ?><?php if ( $multi ): ?>[]<?php endif; ?>" id="<?php $this->render_field_id( $element_id ) ?>" class="<?php $this->render_field_class() ?>" value="<?php $this->render_field_value( $input_value ) ?>"<?php if ($checked): ?> checked="checked"<?php endif; ?> /> <?php
	}

	/**
	 * Render the option value
	 * 
	 * @param mixed $value Pass this to override the real value of the option
	 */
	final public function render_field_value( $value = null )
	{
		if ( is_null( $value ) ) {
			$value = $this->component()->get();
		}
		
		print esc_attr( $value );
	}

	/**
	 * Render the field id
	 *
	 * @param string $css_id Pass a CSS id to override the field id set by the configuration
	 */
	final public function render_field_id( $css_id = null )
	{
		if ( empty( $css_id ) ) {
			$css_id = $this->component()->field_id;
		}

		print esc_attr( $css_id );
	}

	/**
	 * Render the field class
	 */
	final public function render_field_class()
	{
		print esc_attr( $this->component()->field_class );
	}

	/**
	 * Render a string representation of the date the option was last updated
	 *
	 * @param string $format
	 */
	public function render_date_updated( $format = 'F j, Y, g:i a' )
	{
		$time_updated = $this->component()->get_meta( Pie_Easy_Options_Option::META_TIME_UPDATED );

		if ( $time_updated ) {
			print date( $format, $time_updated );
		} else {
			print __('Never', pie_easy_text_domain);
		}
	}

	/**
	 * Render save all options button
	 *
	 * @param string $class
	 */
	public function render_save_all( $class = null )
	{
		// begin rendering ?>
		<a class="<?php $this->merge_classes('pie-easy-options-save', 'pie-easy-options-save-all', $class) ?>" href="#">
			<?php _e( 'Save All', pie_easy_text_domain ); ?>
		</a><?php
	}
	
	/**
	 * Render save one option button
	 *
	 * @param string $class
	 */
	public function render_save_one( $class = null )
	{
		// begin rendering ?>
		<a class="<?php $this->merge_classes('pie-easy-options-save', 'pie-easy-options-save-one', $class) ?>" href="#<?php $this->render_name() ?>">
			<?php _e( 'Save', pie_easy_text_domain ); ?>
		</a><?php
	}

	/**
	 * Render a hidden input which appends the current option name to the manifest
	 */
	final public function render_manifest()
	{
		// begin rendering ?>
		<input type="hidden" name="<?php print self::FIELD_MANIFEST ?>[]" value="<?php $this->render_name() ?>" /><?php
	}
}

?>
