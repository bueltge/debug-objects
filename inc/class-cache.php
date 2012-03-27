<?php
/**
 * Add small screen with informations about cache of WP
 *
 * @package     Debug Objects
 * @subpackage  Cache
 * @author      Frank B&uuml;ltge
 * @since       2.0.0
 */

if ( ! class_exists( 'Debug_Objects_Cache' ) ) {
	
	class Debug_Objects_Cache extends Debug_Objects {
		
		public static function init() {
			
			if ( ! current_user_can( '_debug_objects' ) )
				return;
			
			add_filter( 'debug_objects_tabs', array( __CLASS__, 'get_conditional_tab' ) );
		}
		
		public static function get_conditional_tab( $tabs ) {
			
			$tabs[] = array( 
				'tab' => __( 'Cache', parent :: get_plugin_data() ),
				'function' => array( __CLASS__, 'get_object_cache' )
			);
			
			return $tabs;
		}
		
		public static function get_object_cache( $echo = TRUE ) {
			global $wp_object_cache, $wp_object;
			
			$output  = '';
			$output .= parent :: get_as_ul_tree( $wp_object_cache, '<strong class="h4">WordPress Object Cache</strong>' );
			$output .= '<p>' . __( 'Objects total:', parent :: get_plugin_data() ) . ' ' . $wp_object . '</p>';
			
			if ( $echo )
				echo $output;
			else
				return $output;
		}
		
	} // end class
}// end if class exists
