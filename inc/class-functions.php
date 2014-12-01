<?php
/**
 * Add list of all defined functions
 *
 * @package     Debug Objects
 * @subpackage  List defined functions
 * @author      Frank Bültge
 * @since       2.1.5
 */

if ( ! function_exists( 'add_filter' ) ) {
	echo "Hi there! I'm just a part of plugin, not much I can do when called directly.";
	exit;
}

if ( ! class_exists( 'Debug_Objects_Functions' ) ) {
	class Debug_Objects_Functions extends Debug_Objects {

		protected static $classobj = NULL;

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

		public function __construct() {

			if ( ! current_user_can( '_debug_objects' ) ) {
				return;
			}

			add_filter( 'debug_objects_tabs', array( $this, 'get_conditional_tab' ) );
		}

		public function get_conditional_tab( $tabs ) {

			$tabs[ ] = array(
				'tab'      => __( 'Functions', parent:: get_plugin_data() ),
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
		 * @return Array
		 */
		public function get_functions( $sort = TRUE, $echo = TRUE ) {

			// Use this Hook to remove or add your custom functions
			$functions = apply_filters( 'debug_objects_defined_functions', get_defined_functions() );

			if ( $sort ) {
				sort( $functions );
			}

			if ( ! $echo ) {
				return $functions;
			}

			echo '<h4>Content</h4>';
			echo '<ul><li><a href="#wp_func">WP Functions</a></li><li><a href="#php_func">Module Functions</a></li></ul>';

			$i      = 0;
			$class  = '';
			$output = '';

			echo '<h4 id="wp_func">WP Functions</h4>';

			if ( $sort ) {
				sort( $functions[ 1 ] );
			}

			foreach ( $functions[ 1 ] as $count => $func ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$output .= '<tr' . $class . '><td>' . $count . '</td><td>' . $func . '</td></tr>';
				$i ++;
			}

			echo '<table class="tablesorter"><thead><tr><th>' . __( 'No' ) . '</th><th>' . __(
					'Function'
				) . '</th></tr></thead>'
				. $output . '</table>' . "\n";

			$i      = 0;
			$class  = '';
			$output = '';

			echo '<h4 id="php_func">Module Functions</h4>';

			if ( $sort ) {
				sort( $functions[ 0 ] );
			}

			foreach ( $functions[ 0 ] as $count => $func ) {
				$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
				$output .= '<tr' . $class . '><td>' . $count . '</td><td>' . $func . '</td></tr>';
				$i ++;
			}

			echo '<table class="tablesorter"><thead><tr><th>' . __( 'No' ) . '</th><th>' . __(
					'Function'
				) . '</th></tr></thead>'
				. $output . '</table>' . "\n";

			echo '<p class="alternate">' . __( 'Functions total:', parent:: get_plugin_data() ) . ' ' . $i . '</p>';
		}

	} // end class
}// end if class exists