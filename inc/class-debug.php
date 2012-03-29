<?php
/**
 * Add small quick and dirty debug helper
 *
 * @package     Debug Objects
 * @subpackage  Helper for Debug
 * @author      Frank B&uuml;ltge
 * @since       2.1.5
 */

if ( ! class_exists( 'Debug_Objects_Debug' ) ) {
	
	class Debug_Objects_Debug extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Debug', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'debug' )
			);
			
			return $tabs;
		}
		
		/**
		 * Helper to debug a variable
		 * 
		 * @since 2.1.5 03/27/2012
		 * @param mixed $var the var to debug
		 * @param bool $die whether to die after outputting
		 * @param string $function the function to call, usually either print_r or var_dump, but can be anything
		 */
		public static function debug( $var = NULL, $function = 'var_dump', $echo = FALSE, $die = TRUE ) {
			
			if ( ! isset( $var ) )
				$var = $GLOBALS['wp_version'];
			
			$output  = '<pre>';
			$output .= call_user_func( $function, $var );
			$output .= '</pre>';
			
			if ( $echo )
				echo $output;
			
			if ( $die )
				wp_die( __( 'Debug Objects wp_die on Debug method.', parent::get_plugin_data() ) );
			
			return apply_filters( 'debug_objects_debug_var', $var );
		}
		
	} // end class
}// end if class exists