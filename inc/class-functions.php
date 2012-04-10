<?php
/**
 * Add list of all defined functions
 *
 * @package     Debug Objects
 * @subpackage  List defined functions
 * @author      Frank B&uuml;ltge
 * @since       2.1.5
 */

if ( ! class_exists( 'Debug_Objects_Functions' ) ) {
	
	class Debug_Objects_Functions extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Functions', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_functions' )
			);
			
			return $tabs;
		}
		
		/**
		 * Return all declared classes
		 * 
		 * @since 2.1.5 03/27/2012
		 * @param bool $sort sort classes
		 * @param bool $echo return or echo
		 */
		public static function get_functions( $sort = TRUE, $echo = TRUE ) {
			
			$functions = get_defined_functions();
			if ( $sort )
				sort( $functions );
			
			if ( ! $echo ) {
				return $functions;
			} else {
				
				echo '<h4>Content</h4>';
				echo '<ul><li><a href="#wp_func">WP Functions</a></li><li><a href="#php_func">Module Functions</a></li></ul>';
				
				$i      = 0;
				$class  = '';
				echo '<h4 id="wp_func">WP Functions</h4>';
				$output = '';
				if ( $sort )
					sort( $functions[1] );
				foreach ( $functions[1] as $count => $func ) {
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
					$output .= '<tr' . $class . '><td>' . $count . '</td><td>' . $func . '</td></tr>';
					$i++;
				}
				echo '<table>' . $output . '</table>' . "\n";
				
				$i      = 0;
				$class  = '';
				echo '<h4 id="php_func">Module Functions</h4>';
				$output = '';
				if ( $sort )
					sort( $functions[0] );
				foreach ( $functions[0] as $count => $func ) {
					$class = ( ' class="alternate"' == $class ) ? '' : ' class="alternate"';
					$output .= '<tr' . $class . '><td>' . $count . '</td><td>' . $func . '</td></tr>';
					$i++;
				}
				echo '<table>' . $output . '</table>' . "\n";
				
				echo '<p class="alternate">' . __( 'Functions total:', parent :: get_plugin_data() ) . ' ' . $i . '</p>';
			}
			
		}
		
	} // end class
}// end if class exists