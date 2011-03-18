<?php
/**
 * PIE API schemes scheme styles and scripts enqueuer class file
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package pie
 * @subpackage schemes
 * @since 1.0
 */

/**
 * Make enqueuing scheme styles and scripts easy
 */
class Pie_Easy_Scheme_Enqueue
{
	const ITEM_DELIM = ',';
	const TRIGGER_PATH = 'path';
	const TRIGGER_ALWAYS = 'always';
	const TRIGGER_ACTS = 'actions';
	const TRIGGER_CONDS = 'conditions';
	const ACTION_HANDLER = 'template_redirect';

	/**
	 * @var Pie_Easy_Scheme
	 */
	private $scheme;

	/**
	 * @var Pie_Easy_Map
	 */
	private $styles;

	/**
	 * @var Pie_Easy_Map
	 */
	private $scripts;

	/**
	 * Constructor
	 *
	 * @param Pie_Easy_Scheme $scheme
	 */
	public function __construct( Pie_Easy_Scheme $scheme )
	{
		$this->scheme = $scheme;

		// init styles map
		$this->styles = new Pie_Easy_Map();

		// define styles
		if ( $this->define( $this->styles, Pie_Easy_Scheme::DIRECTIVE_STYLE_DEFS ) ) {
			// hook up styles always handler
			add_action( self::ACTION_HANDLER, array( $this, 'handle_style_always' ) );
			// init style triggers
			$this->triggers( $this->styles, Pie_Easy_Scheme::DIRECTIVE_STYLE_ACTS, self::TRIGGER_ACTS );
			$this->triggers( $this->styles, Pie_Easy_Scheme::DIRECTIVE_STYLE_CONDS, self::TRIGGER_CONDS );
		}

		// init scripts map
		$this->scripts = new Pie_Easy_Map();

		// define scripts
		if ( $this->define( $this->scripts, Pie_Easy_Scheme::DIRECTIVE_SCRIPT_DEFS ) ) {
			// hook up scripts always handler
			add_action( self::ACTION_HANDLER, array( $this, 'handle_script_always' ) );
			// init script triggers
			$this->triggers( $this->scripts, Pie_Easy_Scheme::DIRECTIVE_SCRIPT_ACTS, self::TRIGGER_ACTS );
			$this->triggers( $this->scripts, Pie_Easy_Scheme::DIRECTIVE_SCRIPT_CONDS, self::TRIGGER_CONDS );
		}
	}

	/**
	 * Create a unique handle for enqueing
	 *
	 * @param string $theme
	 * @param string $handle
	 * @return string
	 */
	private function make_handle( $theme, $handle )
	{
		return sprintf( '%s-%s', $theme, trim( $handle ) );
	}

	/**
	 * Try to define triggers which have been set in the scheme's config
	 *
	 * @return boolean
	 */
	private function define( $map, $directive )
	{
		// check if at least one theme defined some triggers
		if ( $this->scheme->has_directive( $directive ) ) {

			// get trigger directives for all themes
			$directive_map = $this->scheme->get_directive_map( $directive );

			// loop through and populate trigger map
			foreach ( $directive_map as $theme => $directive ) {

				// is directive value a map?
				if ( $directive->value instanceof Pie_Easy_Map ) {

					// yes, add each handle and URL path to map
					foreach( $directive->value as $handle => $path ) {

						// new map for this trigger
						$trigger = new Pie_Easy_Map();
						// add path value
						$trigger->add( self::TRIGGER_PATH, $this->scheme->theme_file_url( $theme, $path ) );
						// init empty always toggle
						$trigger->add( self::TRIGGER_ALWAYS, true );
						// init empty actions stack
						$trigger->add( self::TRIGGER_ACTS, new Pie_Easy_Stack() );
						// init empty conditions stack
						$trigger->add( self::TRIGGER_CONDS, new Pie_Easy_Stack() );
						
						// add trigger to main map
						$map->add( $this->make_handle( $theme, $handle), $trigger );
					}
				}
			}

			return true;
		}
		
		return false;
	}

	/**
	 * Set triggers for specified directives.
	 *
	 * This is for scheme directives that define a trigger with a
	 * value being a delimeted string of style or script handles
	 *
	 * @param Pie_Easy_Map $map
	 * @param string $directive_name
	 * @param string $trigger_type
	 * @return boolean
	 */
	private function triggers( Pie_Easy_Map $map, $directive_name, $trigger_type )
	{
		// check if at least one theme defined this trigger
		if ( $this->scheme->has_directive( $directive_name ) ) {

			// get trigger directives for all themes
			$directive_map = $this->scheme->get_directive_map( $directive_name );

			// loop through and update triggers map
			foreach ( $directive_map as $theme => $directive ) {

				// is directive value a map?
				if ( $directive->value instanceof Pie_Easy_Map ) {

					// yes, add action to each trigger's trigger stack
					foreach( $directive->value as $action => $handles ) {

						// split handles at delimeter
						$handles = explode( self::ITEM_DELIM, $handles );

						// loop through each handle
						foreach ( $handles as $handle ) {

							// trim handle
							$handle = $this->make_handle( $theme, $handle );

							// does trigger handle exist?
							if ( $map->item_at( $handle ) ) {

								// remove trigger's always toggle
								$map->item_at($handle)->add(self::TRIGGER_ALWAYS, false);
								
								// push onto trigger's trigger stack
								$map->item_at($handle)->item_at($trigger_type)->push($action);

								// is this an actions trigger type?
								if ( $trigger_type == self::TRIGGER_ACTS ) {
									// yes, hook it to action handler
									add_action( $action, array( $this, 'handle_' . $directive_name ) );
								}

							}
						}
					}
				}
			}

			// is this a conditions trigger type?
			if ( $trigger_type == self::TRIGGER_CONDS ) {
				// yes, hook up conditions handler
				add_action( self::ACTION_HANDLER, array( $this, 'handle_' . $directive_name ) );
			}

			return true;
		}

		return false;
	}

	/**
	 * Handle enqueing styles that should always be loaded
	 */
	public function handle_style_always()
	{
		// loop through styles and check if always is toggled on
		foreach( $this->styles as $handle => $config_map ) {
			// always load?
			if ( $config_map->item_at(self::TRIGGER_ALWAYS) == true ) {
				// yes, enqueue it!
				wp_enqueue_style( $handle, $config_map->item_at(self::TRIGGER_PATH) );
			}
		}
	}

	/**
	 * Handle enqueing styles on configured actions
	 */
	public function handle_style_actions()
	{
		// action is current filter
		$action = current_filter();

		// loop through styles and check if action is set
		foreach( $this->styles as $handle => $config_map ) {
			// action in this style's action stack?
			if ( $config_map->item_at(self::TRIGGER_ACTS)->contains($action) ) {
				// yes, enqueue it!
				wp_enqueue_style( $handle, $config_map->item_at(self::TRIGGER_PATH) );
			}
		}
	}

	/**
	 * Handle enqueing styles when specific conditions are met
	 */
	public function handle_style_conditions()
	{
		// loop through styles and check if conditions are set
		foreach( $this->styles as $handle => $config_map ) {
			// and conditions in stack?
			if ( count( $config_map->item_at(self::TRIGGER_CONDS) ) ) {
				// check if ANY of the conditions eval to true
				foreach( $config_map->item_at(self::TRIGGER_CONDS) as $callback ) {
					// try to exec the callback
					if ( function_exists( $callback ) && call_user_func($callback) == true ) {
						// callback exists and evaled to true, enqueue it
						wp_enqueue_style( $handle, $config_map->item_at(self::TRIGGER_PATH) );
						// done with this inner (conditions) loop
						break;
					}
				}
			}
		}
	}

	/**
	 * Handle enqueing scripts that should always be loaded
	 */
	public function handle_script_always()
	{
		// loop through scripts and check if always is toggled on
		foreach( $this->scripts as $handle => $config_map ) {
			// always load?
			if ( $config_map->item_at(self::TRIGGER_ALWAYS) == true ) {
				// yes, enqueue it!
				wp_enqueue_script( $handle, $config_map->item_at(self::TRIGGER_PATH) );
			}
		}
	}

	/**
	 * Handle enqueing scripts on configured actions
	 */
	public function handle_script_actions()
	{
		// action is current filter
		$action = current_filter();

		// loop through scripts and check if action is set
		foreach( $this->scripts as $handle => $config_map ) {
			// action in this script's action stack?
			if ( $config_map->item_at(self::TRIGGER_ACTS)->contains($action) ) {
				// yes, enqueue it!
				wp_enqueue_script( $handle, $config_map->item_at(self::TRIGGER_PATH) );
			}
		}
	}

	/**
	 * Handle enqueing scripts when specific conditions are met
	 */
	public function handle_script_conditions()
	{
		// loop through scripts and check if conditions are set
		foreach( $this->scripts as $handle => $config_map ) {
			// any conditions in stack?
			if ( count( $config_map->item_at(self::TRIGGER_CONDS) ) ) {
				// check if ANY of the conditions eval to true
				foreach( $config_map->item_at(self::TRIGGER_CONDS) as $callback ) {
					// try to exec the callback
					if ( function_exists( $callback ) && call_user_func($callback) == true ) {
						// callback exists and evaled to true, enqueue it
						wp_enqueue_script( $handle, $config_map->item_at(self::TRIGGER_PATH) );
						// done with this inner (conditions) loop
						break;
					}
				}
			}
		}
	}
}
?>