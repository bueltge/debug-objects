<?php
/**
 * Add list of all declared classes and sub-classes
 *
 * @package     Debug Objects
 * @subpackage  Helper for Debug
 * @author      Frank Bültge
 * @since       2.1.5
 * @version     2017-01-20
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Classes' ) ) {
	return NULL;
}

class Debug_Objects_Classes extends Debug_Objects {

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return  Debug_Objects_Classes $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Classes constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );

		// Get settings
		$options = Debug_Objects_Settings::return_options();

		if ( ! isset( $options[ 'filter' ] ) ) {
			return;
		}

		// Filter classes from this plugin
		if ( 1 === (int) $options[ 'filter' ] ) {
			add_filter( 'debug_objects_declared_classes', array( $this, 'filter_debug_objects_classes' ) );
		}
	}

	/**
	 * Add content for tabs
	 *
	 * @param  array $tabs
	 *
	 * @return array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Classes', 'debug_objects' ),
			'function' => array( $this, 'get_classes' )
		);

		return $tabs;
	}

	/**
	 * Filter classes to remove the classes from this plugin
	 *
	 * @param  array
	 *
	 * @return array
	 */
	public function filter_debug_objects_classes( array $classes ) {

		foreach ( $classes as $count => $class ) {
			if ( 0 === strpos( $class, 'Debug_Objects' ) ) {
				unset( $classes[ $count ] );
			}
		}

		return $classes;
	}

	/**
	 * Return all declared classes
	 *
	 * @since 2.1.5 03/27/2012
	 *
	 * @param bool $sort sort classes
	 * @param bool $echo return or echo
	 *
	 * @return string
	 */
	public function get_classes( $sort = TRUE, $echo = TRUE ) {

		// Use this Hook to remove or add your custom classes
		$classes = (array) apply_filters( 'debug_objects_declared_classes', get_declared_classes() );
		if ( $sort ) {
			sort( $classes );
		}

		if ( $echo ) {
			$output    = '';
			foreach ( $classes as $count => $class ) {

				$count ++;
				$output .= '<tr><td>' . $count . '</td><td>' . $class . '</td>';
				$subclasses = get_parent_class( $class );

				if ( ! empty( $subclasses ) ) {
					$output .= '<td><code>extend</code> ' . $subclasses . '</td>';
				} else {
					$output .= '<td></td>';
				}

				$output .= '</tr>';
			}
			echo '<h4>' . esc_attr__( 'Total Classes: ', 'debug_objects' ) . count( $classes ) . '</h4>';
			echo '<table class="tablesorter"><thead><tr><th>' . __( 'No' ) . '</th><th>' . __(
					'Class'
				) . '</th><th>' . esc_attr__( 'Parent class', 'debug_objetcs' ) . '</th></tr></thead>'
				. $output . '</table>';
		}

		return $classes;
	}

} // end class
