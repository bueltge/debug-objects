<?php
/**
 * Add list of all declared classes and sub-classes
 *
 * @package     Debug Objects
 * @subpackage  Helper for Debug
 * @author      Frank B&uuml;ltge
 * @since       2.1.5
 */

if ( ! class_exists( 'Debug_Objects_Classes' ) ) {
	
	class Debug_Objects_Classes extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Classes', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_classes' )
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
		public static function get_classes( $sort = TRUE, $echo = TRUE ) {
			
			$classes = get_declared_classes();
			if ( $sort )
				sort( $classes );
			
			if ( ! $echo ) {
				return $classes;
			} else {
				$i        = 0;
				$style    = '';
				$substyle = '';
				$output   = '';
				$suboutput = '';
				foreach ( $classes as $count => $class ) {
					$subclasses = '';
					$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
					$output .= '<tr' . $style . '><td>' . $count . '</td><td>' . $class . '</td>';
					$subclasses = get_parent_class( $class );
					//$output .= var_export( $subclasses, true );
					if ( ! empty( $subclasses ) ) {
						$output .= '<td><code>extend</code> ' . $subclasses . '</td>';
					} else {
						$output .= '<td> </td>';
					}
					$output .= '</tr>';
					$i++;
				}
				echo '<table>' . $output . '</table>';
				
				echo '<p class="alternate">' . __( 'Class total:', parent :: get_plugin_data() ) . ' ' . $i . '</p>';
			}
			
		}
		
	} // end class
}// end if class exists