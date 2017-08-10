<?php
/**
 * Add list of all defined functions
 *
 * @package     Debug Objects
 * @subpackage  List defined functions
 * @author      Frank Bültge
 * @since       2.1.5
 * @version     2017-01-21
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

class Debug_Objects_Functions extends Debug_Objects {

	protected static $classobj;

	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @access  public
	 * @return Debug_Objects_Functions|null $classobj
	 */
	public static function init() {

		NULL === self::$classobj and self::$classobj = new self();

		return self::$classobj;
	}

	/**
	 * Debug_Objects_Functions constructor.
	 */
	public function __construct() {

		parent::__construct();

		if ( ! $this->get_capability() ) {
			return;
		}

		add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
	}

	public function get_conditional_tab( $tabs ) {

		$tabs[] = array(
			'tab'      => esc_attr__( 'Functions', 'debug_objects' ),
			'function' => array( $this, 'get_functions' )
		);

		return $tabs;
	}

	/**
	 * Return all declared classes
	 *
	 * @since 2.1.5 03/27/2012
	 *
	 * @param bool $sort sort classes
	 * @param bool $echo return or echo
	 *
	 * @return array
	 */
	public function get_functions( $sort = TRUE, $echo = TRUE ) {

		// Use this Hook to remove or add your custom functions
		$functions = (array) apply_filters( 'debug_objects_defined_functions', get_defined_functions() );

		if ( $sort ) {
			sort( $functions );
		}

		echo '<h4>Content</h4>';
		echo '<ul><li><a href="#wp_func">WP Functions</a></li><li><a href="#php_func">Module Functions</a></li></ul>';

		$i      = 0;
		$output = '';

		echo '<h4 id="wp_func">WP Functions</h4>';

		if ( $sort ) {
			sort( $functions[ 1 ] );
		}

		foreach ( (array) $functions[ 1 ] as $count => $func ) {
			$output .= '<tr><td>' . $count . '</td><td>' . $func . '</td></tr>';
			$i ++;
		}

		echo '<table class="tablesorter"><thead><tr><th>' . esc_attr__( 'No', 'debug_objects' )
		     . '</th><th>' . esc_attr__( 'Function', 'debug_objects' ) . '</th></tr></thead>'
		     . $output . '</table>' . "\n";

		$i      = 0;
		$output = '';

		echo '<h4 id="php_func">Module Functions</h4>';

		if ( $sort ) {
			sort( $functions[ 0 ] );
		}

		foreach ( (array) $functions[ 0 ] as $count => $func ) {
			$output .= '<tr><td>' . $count . '</td><td>' . $func . '</td></tr>';
			$i ++;
		}

		echo '<table class="tablesorter"><thead><tr><th>' . esc_attr__( 'No', 'debug_objects' )
		     . '</th><th>' . esc_attr__( 'Function', 'debug_objects' ) . '</th></tr></thead>'
		     . $output . '</table>' . "\n";

		echo '<p class="alternate">' . esc_attr__( 'Functions total:', 'debug_objects' ) . ' ' . $i . '</p>';

		return $functions;
	}

} // end class
