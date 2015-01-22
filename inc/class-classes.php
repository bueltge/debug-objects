<?php
/**
 * Add list of all declared classes and sub-classes
 *
 * @package     Debug Objects
 * @subpackage  Helper for Debug
 * @author      Frank Bültge
 * @since       2.1.5
 * @version     03/07/2014
 */
if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( class_exists( 'Debug_Objects_Classes' ) ) {
	return NULL;
}

class Debug_Objects_Classes {

	protected static $classobj = NULL;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return  $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	public function __construct() {

		if ( ! current_user_can( '_debug_objects' ) ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );

		// Get settings
		$options = Debug_Objects_Settings::return_options();

		// Filter classes from this plugin
		if ( isset( $options[ 'filter' ] ) && '1' === $options[ 'filter' ] ) {
			add_filter( 'debug_objects_declared_classes', array( $this, 'remove_debug_objects_classes' ) );
		}
	}

	/**
	 * Add content for tabs
	 *
	 * @param  Array $tabs
	 *
	 * @return Array $tabs
	 */
	public function get_conditional_tab( $tabs ) {

		$tabs[ ] = array(
			'tab'      => __( 'Classes' ),
			'function' => array( $this, 'get_classes' )
		);

		return $tabs;
	}

	/**
	 * Filter classes to remove the classes from this plugin
	 *
	 * @param  Array
	 *
	 * @return Array
	 */
	public function remove_debug_objects_classes( $classes ) {

		foreach ( $classes as $count => $class ) {
			if ( 'Debug_Objects' === substr( $class, 0, 13 ) ) {
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
		$classes = apply_filters( 'debug_objects_declared_classes', get_declared_classes() );
		if ( $sort ) {
			sort( $classes );
		}

		if ( ! $echo ) {
			return $classes;
		} else {

			$style     = '';
			$substyle  = '';
			$output    = '';
			$suboutput = '';
			foreach ( $classes as $count => $class ) {

				$count ++;
				$subclasses = '';
				$style      = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
				$output .= '<tr' . $style . '><td>' . $count . '</td><td>' . $class . '</td>';
				$subclasses = get_parent_class( $class );

				if ( ! empty( $subclasses ) ) {
					$output .= '<td><code>extend</code> ' . $subclasses . '</td>';
				} else {
					$output .= '<td> </td>';
				}

				$output .= '</tr>';
			}
			echo '<h4>Total Classes: ' . count( $classes ) . '</h4>';
			echo '<table class="tablesorter"><thead><tr><th>' . __( 'No' ) . '</th><th>' . __(
					'Class'
				) . '</th><th>' . __( 'Parent class' ) . '</th></tr></thead>'
				. $output . '</table>';
		}

	}

} // end class
